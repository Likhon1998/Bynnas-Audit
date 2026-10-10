/**
 * Client-side "Customize Table" editor for audit report custom tables.
 * Mirrors App\Support\CustomTableSchema so every edit is instant; Livewire only
 * receives a quiet background draft sync and one final save on close.
 */

const MAX_COLS = 20;
const MAX_ROWS = 100;
const HISTORY_LIMIT = 40;
const SYNC_DELAY_MS = 1200;

const clone = (value) => JSON.parse(JSON.stringify(value));
const newId = () => `c_${Math.random().toString(16).slice(2, 10)}`;
const clamp = (n, lo, hi) => Math.max(lo, Math.min(hi, n));

const columnNode = (label, children = []) => ({ id: newId(), label, children });

const leafColumns = (columns) => {
    const out = [];
    for (const col of columns || []) {
        const kids = col.children || [];
        if (kids.length === 0) {
            out.push(col);
        } else {
            out.push(...leafColumns(kids));
        }
    }
    return out;
};

const treeDepth = (columns) => {
    let max = 1;
    for (const col of columns || []) {
        if ((col.children || []).length) {
            max = Math.max(max, 1 + treeDepth(col.children));
        }
    }
    return max;
};

const blankRow = (leafCount) => ({
    cells: Array(Math.max(1, leafCount)).fill(''),
    is_total: false,
    lead_colspan: 1,
});

const normalizeMerges = (merges, rowCount, leafCount) => {
    const out = [];
    for (const m of merges || []) {
        if (!m || typeof m !== 'object') continue;
        const r = Number(m.r ?? -1);
        const c = Number(m.c ?? -1);
        if (r < 0 || c < 0 || r >= rowCount || c >= leafCount) continue;
        const rowspan = clamp(Number(m.rowspan) || 1, 1, rowCount - r);
        const colspan = clamp(Number(m.colspan) || 1, 1, leafCount - c);
        if (rowspan === 1 && colspan === 1) continue;
        out.push({ r, c, rowspan, colspan });
    }
    return out;
};

const normalizeColumns = (columns) => (Array.isArray(columns) ? columns : [])
    .filter((col) => col && typeof col === 'object')
    .map((col) => {
        const node = {
            id: String(col.id || newId()),
            label: String(col.label ?? 'কলাম'),
            children: normalizeColumns(col.children),
        };
        if (col.width !== undefined && col.width !== null && col.width !== '' && !Number.isNaN(Number(col.width))) {
            node.width = clamp(Number(col.width), 4, 80);
        }
        return node;
    });

const normalizeTable = (input) => {
    const table = input && typeof input === 'object' ? input : {};
    let columns = normalizeColumns(table.columns);
    if (columns.length === 0) {
        columns = [columnNode('কলাম ১'), columnNode('কলাম ২'), columnNode('কলাম ৩')];
    }
    const leafCount = leafColumns(columns).length;
    let rows = (Array.isArray(table.rows) ? table.rows : [])
        .filter((row) => row && typeof row === 'object')
        .map((row) => {
            const cells = (Array.isArray(row.cells) ? row.cells : []).map((c) => String(c ?? ''));
            while (cells.length < leafCount) cells.push('');
            cells.length = leafCount;
            return {
                cells,
                is_total: Boolean(row.is_total),
                lead_colspan: clamp(Number(row.lead_colspan) || 1, 1, leafCount),
            };
        });
    if (rows.length === 0) {
        rows = [blankRow(leafCount), blankRow(leafCount)];
    }
    return {
        type: 'custom_table',
        title: String(table.title ?? 'টেবিল:'),
        columns,
        rows,
        merges: normalizeMerges(table.merges, rows.length, leafCount),
    };
};

/** Locate a column node by id → { node, siblings, index, depth }. */
const findColumn = (columns, id, depth = 0) => {
    for (let i = 0; i < columns.length; i++) {
        const col = columns[i];
        if (col.id === id) {
            return { node: col, siblings: columns, index: i, depth };
        }
        const found = findColumn(col.children || [], id, depth + 1);
        if (found) return found;
    }
    return null;
};

const overlaps = (a, b) => !(
    a.r + a.rowspan <= b.r || b.r + b.rowspan <= a.r
    || a.c + a.colspan <= b.c || b.c + b.colspan <= a.c
);

const toBn = (n) => String(n).replace(/\d/g, (d) => '০১২৩৪৫৬৭৮৯'[d]);

export function customTableEditor(config = {}) {
    return {
        blockIndex: Number(config.blockIndex ?? 0),
        original: normalizeTable(clone(config.table || {})),
        templates: config.templates || {},
        t: normalizeTable(clone(config.table || {})),
        selR: null,
        selC: null,
        mergeRows: 2,
        mergeCols: 1,
        sizeCols: 1,
        sizeRows: 1,
        history: [],
        closing: false,
        dirty: false,
        syncTimer: null,

        init() {
            document.body.dataset.ctEditor = '1';
            this.syncSizeInputs();
        },

        destroy() {
            clearTimeout(this.syncTimer);
            delete document.body.dataset.ctEditor;
        },

        // ---------- derived view data ----------
        get leaves() {
            return leafColumns(this.t.columns);
        },

        get widths() {
            const leaves = this.leaves;
            const n = leaves.length;
            if (n === 0) return [];
            let sumSet = 0;
            let unset = 0;
            leaves.forEach((leaf) => {
                if (typeof leaf.width === 'number') sumSet += leaf.width;
                else unset++;
            });
            if (unset === 0 && sumSet > 0) {
                return leaves.map((leaf) => Math.round((leaf.width / sumSet) * 10000) / 100);
            }
            const fallback = unset > 0 ? Math.max(0, 100 - sumSet) / unset : 0;
            return leaves.map((leaf) => Math.round((typeof leaf.width === 'number' ? leaf.width : fallback) * 100) / 100);
        },

        get header() {
            const depth = treeDepth(this.t.columns);
            const matrix = Array.from({ length: depth }, () => []);
            const fill = (columns, level) => {
                for (const col of columns) {
                    const kids = col.children || [];
                    matrix[level].push({
                        id: col.id,
                        text: col.label,
                        colspan: kids.length ? leafColumns(kids).length : 1,
                        rowspan: kids.length ? 1 : Math.max(1, depth - level),
                    });
                    if (kids.length) fill(kids, level + 1);
                }
            };
            fill(this.t.columns, 0);
            return matrix;
        },

        /** Row → visible cells (merge-covered cells skipped). Reads structure only, never cell text. */
        get paint() {
            const rows = this.t.rows;
            const leafCount = this.leaves.length;
            const rowCount = rows.length;
            const covered = rows.map(() => Array(leafCount).fill(false));
            const starts = {};
            for (const m of this.t.merges) {
                starts[`${m.r}:${m.c}`] = m;
                for (let rr = m.r; rr < m.r + m.rowspan && rr < rowCount; rr++) {
                    for (let cc = m.c; cc < m.c + m.colspan && cc < leafCount; cc++) {
                        if (rr !== m.r || cc !== m.c) covered[rr][cc] = true;
                    }
                }
            }
            rows.forEach((row, ri) => {
                if (!row.is_total) return;
                const lead = clamp(Number(row.lead_colspan) || 1, 1, leafCount);
                if (lead <= 1 || starts[`${ri}:0`]) return;
                starts[`${ri}:0`] = { rowspan: 1, colspan: lead };
                for (let cc = 1; cc < lead; cc++) covered[ri][cc] = true;
            });
            return rows.map((row, r) => {
                const cells = [];
                for (let c = 0; c < leafCount; c++) {
                    if (covered[r][c]) continue;
                    const s = starts[`${r}:${c}`];
                    const rs = s ? s.rowspan : 1;
                    const cs = s ? s.colspan : 1;
                    cells.push({
                        c,
                        rs,
                        cs,
                        align: (c === 2 && cs === 1 && rs === 1) ? 'text-left' : 'text-center',
                        valign: (c <= 2 && rs > 1) ? 'align-middle' : 'align-top',
                    });
                }
                return { r, total: Boolean(row.is_total), cells };
            });
        },

        get columnList() {
            const out = [];
            const walk = (columns, depth) => {
                for (const col of columns) {
                    out.push({ node: col, depth, isLeaf: (col.children || []).length === 0 });
                    if ((col.children || []).length) walk(col.children, depth + 1);
                }
            };
            walk(this.t.columns, 0);
            return out;
        },

        get lastRow() {
            return this.t.rows[this.t.rows.length - 1] || null;
        },

        bn(n) {
            return toBn(n);
        },

        leafName() {
            if (this.selC === null) return '';
            return this.leaves[this.selC]?.label || '';
        },

        isSelected(r, c) {
            return this.selR === r && this.selC === c;
        },

        // ---------- sync ----------
        touched() {
            this.dirty = true;
            clearTimeout(this.syncTimer);
            this.syncTimer = setTimeout(() => this.syncNow(), SYNC_DELAY_MS);
        },

        syncNow() {
            if (!this.dirty || this.closing) return;
            this.dirty = false;
            this.$wire.syncCustomTableDraft(this.blockIndex, clone(this.t));
        },

        close() {
            if (this.closing) return;
            this.closing = true;
            clearTimeout(this.syncTimer);
            delete document.body.dataset.ctEditor;
            this.$wire.saveCustomTableEditor(this.blockIndex, clone(this.t), clone(this.original));
        },

        // ---------- history ----------
        remember() {
            this.history.push(clone(this.t));
            if (this.history.length > HISTORY_LIMIT) this.history.shift();
        },

        undo() {
            const prev = this.history.pop();
            if (!prev) return;
            this.t = prev;
            this.clampSelection();
            this.syncSizeInputs();
            this.touched();
        },

        syncSizeInputs() {
            this.sizeCols = this.t.columns.length;
            this.sizeRows = this.t.rows.length;
        },

        clampSelection() {
            if (this.selR !== null && this.selR >= this.t.rows.length) this.selR = null;
            if (this.selC !== null && this.selC >= this.leaves.length) this.selC = null;
            if (this.selR === null || this.selC === null) {
                this.selR = null;
                this.selC = null;
            }
        },

        /**
         * Apply a column-structure change while keeping each cell attached to its leaf column
         * (adding a column in the middle no longer shifts data into the wrong place).
         */
        restructure(mutate, inherit = {}) {
            const beforeIds = this.leaves.map((leaf) => leaf.id);
            const merges = this.t.merges.map((m) => ({ ...m, startId: beforeIds[m.c] }));
            mutate();
            const afterIds = leafColumns(this.t.columns).map((leaf) => leaf.id);
            const indexOf = {};
            beforeIds.forEach((id, i) => { indexOf[id] = i; });

            this.t.rows.forEach((row) => {
                const old = row.cells;
                row.cells = afterIds.map((id) => {
                    const src = indexOf[id] ?? indexOf[inherit[id]];
                    return src === undefined ? '' : (old[src] ?? '');
                });
                row.lead_colspan = clamp(Number(row.lead_colspan) || 1, 1, afterIds.length);
            });

            const remapped = merges.map((m) => {
                let c = afterIds.indexOf(m.startId);
                if (c < 0) c = afterIds.findIndex((id) => inherit[id] === m.startId);
                return { r: m.r, c, rowspan: m.rowspan, colspan: m.colspan };
            });
            this.t.merges = normalizeMerges(remapped, this.t.rows.length, afterIds.length);
            this.clampSelection();
            this.syncSizeInputs();
            this.touched();
        },

        // ---------- columns ----------
        addTopColumn() {
            if (this.t.columns.length >= MAX_COLS) return;
            this.remember();
            this.restructure(() => {
                this.t.columns.push(columnNode(`কলাম ${toBn(this.t.columns.length + 1)}`));
            });
        },

        addSubColumn(id) {
            const found = findColumn(this.t.columns, id);
            if (!found) return;
            this.remember();
            const child = columnNode('সাব-কলাম');
            const inherit = {};
            if ((found.node.children || []).length === 0) {
                inherit[child.id] = found.node.id;
                delete found.node.width;
            }
            this.restructure(() => {
                found.node.children = [...(found.node.children || []), child];
            }, inherit);
        },

        removeColumn(id) {
            const found = findColumn(this.t.columns, id);
            if (!found) return;
            if (found.depth === 0 && this.t.columns.length <= 1) return;
            this.remember();
            this.restructure(() => {
                found.siblings.splice(found.index, 1);
            });
        },

        setLabel() {
            this.touched();
        },

        setWidth(node, value) {
            const w = Number(value);
            this.remember();
            if (value === '' || Number.isNaN(w) || w <= 0) {
                delete node.width;
            } else {
                node.width = clamp(w, 4, 80);
            }
            this.touched();
        },

        // ---------- rows ----------
        addRow() {
            if (this.t.rows.length >= MAX_ROWS) return;
            this.remember();
            this.t.rows.push(blankRow(this.leaves.length));
            this.syncSizeInputs();
            this.touched();
        },

        removeLastRow() {
            if (this.t.rows.length <= 1) return;
            this.remember();
            this.t.rows.pop();
            this.t.merges = normalizeMerges(this.t.merges, this.t.rows.length, this.leaves.length);
            this.clampSelection();
            this.syncSizeInputs();
            this.touched();
        },

        toggleTotalRow() {
            const row = this.lastRow;
            if (!row) return;
            this.remember();
            row.is_total = !row.is_total;
            row.lead_colspan = row.is_total ? clamp(Number(row.lead_colspan) > 1 ? Number(row.lead_colspan) : 3, 1, this.leaves.length) : 1;
            this.touched();
        },

        applySize() {
            const cols = clamp(Number(this.sizeCols) || 1, 1, MAX_COLS);
            const rows = clamp(Number(this.sizeRows) || 1, 1, MAX_ROWS);
            if (cols === this.t.columns.length && rows === this.t.rows.length) return;
            this.remember();
            this.restructure(() => {
                while (this.t.columns.length < cols) {
                    this.t.columns.push(columnNode(`কলাম ${toBn(this.t.columns.length + 1)}`));
                }
                while (this.t.columns.length > cols) this.t.columns.pop();
            });
            const leafCount = this.leaves.length;
            while (this.t.rows.length < rows) this.t.rows.push(blankRow(leafCount));
            while (this.t.rows.length > rows) this.t.rows.pop();
            this.t.merges = normalizeMerges(this.t.merges, this.t.rows.length, leafCount);
            this.selR = null;
            this.selC = null;
            this.syncSizeInputs();
            this.touched();
        },

        loadTemplate(name) {
            const tpl = this.templates[name];
            if (!tpl) return;
            this.remember();
            this.t = normalizeTable(clone(tpl));
            this.selR = null;
            this.selC = null;
            this.syncSizeInputs();
            this.touched();
        },

        // ---------- cells ----------
        cellInput() {
            this.touched();
        },

        // ---------- merges ----------
        mergeAt(r, c) {
            return this.t.merges.find((m) => r >= m.r && r < m.r + m.rowspan && c >= m.c && c < m.c + m.colspan) || null;
        },

        selectCell(r, c) {
            const m = this.mergeAt(r, c);
            this.selR = m ? m.r : r;
            this.selC = m ? m.c : c;
            if (m) {
                this.mergeRows = m.rowspan;
                this.mergeCols = m.colspan;
            } else if (this.mergeRows < 2 && this.mergeCols < 2) {
                this.mergeRows = 2;
                this.mergeCols = 1;
            }
        },

        setMerge(r, c, rowspan, colspan) {
            const rowCount = this.t.rows.length;
            const leafCount = this.leaves.length;
            if (r < 0 || c < 0 || r >= rowCount || c >= leafCount) return;
            const next = {
                r,
                c,
                rowspan: clamp(rowspan, 1, rowCount - r),
                colspan: clamp(colspan, 1, leafCount - c),
            };
            const merges = this.t.merges.filter((m) => !overlaps(m, next));
            if (next.rowspan > 1 || next.colspan > 1) merges.push(next);
            this.t.merges = merges;
            this.mergeRows = next.rowspan;
            this.mergeCols = next.colspan;
            this.touched();
        },

        applyMerge() {
            if (this.selR === null || this.selC === null) return;
            this.remember();
            this.setMerge(this.selR, this.selC, Number(this.mergeRows) || 1, Number(this.mergeCols) || 1);
        },

        clearMerge() {
            if (this.selR === null || this.selC === null) return;
            this.remember();
            const r = this.selR;
            const c = this.selC;
            this.t.merges = this.t.merges.filter((m) => !(r >= m.r && r < m.r + m.rowspan && c >= m.c && c < m.c + m.colspan));
            this.mergeRows = 1;
            this.mergeCols = 1;
            this.touched();
        },

        nudge(deltaRows, deltaCols) {
            if (this.selR === null || this.selC === null) return;
            const existing = this.mergeAt(this.selR, this.selC);
            const base = existing || { r: this.selR, c: this.selC, rowspan: 1, colspan: 1 };
            const rs = Math.max(1, base.rowspan + deltaRows);
            const cs = Math.max(1, base.colspan + deltaCols);
            if (!existing && rs === 1 && cs === 1) return;
            this.remember();
            this.selR = base.r;
            this.selC = base.c;
            this.setMerge(base.r, base.c, rs, cs);
        },
    };
}

const register = (Alpine) => {
    Alpine.data('customTableEditor', customTableEditor);
};

if (window.Alpine) {
    register(window.Alpine);
}
document.addEventListener('alpine:init', () => register(window.Alpine));
