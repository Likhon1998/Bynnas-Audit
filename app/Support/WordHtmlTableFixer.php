<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * Microsoft Word ignores many CSS table rules when opening HTML as .doc.
 * Inject Word-native borders and inline-flow table attributes so grids match the PDF export.
 */
class WordHtmlTableFixer
{
    public function apply(string $html): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $tables = [];
        foreach ($dom->getElementsByTagName('table') as $table) {
            if ($table instanceof DOMElement) {
                $tables[] = $table;
            }
        }

        foreach ($tables as $table) {
            $this->prepareTable($table);
        }

        $output = $dom->saveHTML();
        if ($output === false) {
            return $html;
        }

        return preg_replace('/^<\?xml encoding="UTF-8"\?>/', '', $output) ?? $output;
    }

    protected function prepareTable(DOMElement $table): void
    {
        $class = $table->getAttribute('class');

        $this->appendStyle($table, 'mso-table-overlap:never;');

        if (str_contains($class, 'header-table')) {
            $table->setAttribute('border', '0');
            $table->setAttribute('cellspacing', '0');
            $table->setAttribute('cellpadding', '0');
            $this->appendStyle($table, 'border:none;border-collapse:collapse;width:100%;');

            foreach ($this->cellsInTable($table) as $cell) {
                $this->appendStyle($cell, 'border:none;padding:0;vertical-align:top;');
            }

            return;
        }

        if (str_contains($class, 'cover-rating')) {
            $table->setAttribute('border', '0');
            $table->setAttribute('cellspacing', '0');
            $table->setAttribute('cellpadding', '0');
            $this->appendStyle($table, 'border-collapse:collapse;width:42mm;margin-left:auto;');

            foreach ($this->cellsInTable($table) as $cell) {
                $cellClass = $cell->getAttribute('class');
                if (str_contains($cellClass, 'cr-label')) {
                    $this->appendStyle($cell, implode('', [
                        'border:1px solid #1d4ed8;',
                        'mso-border-alt:solid #1d4ed8 0.75pt;',
                        'background:#1d4ed8;color:#ffffff;',
                        'font-weight:bold;text-align:center;padding:4pt;',
                    ]));
                } elseif (str_contains($cellClass, 'cr-value')) {
                    $this->appendStyle($cell, implode('', [
                        'border:2px solid #f97316;',
                        'mso-border-alt:solid #f97316 1pt;',
                        'color:#ffffff;font-weight:bold;text-align:center;padding:4pt;',
                    ]));
                } else {
                    $this->appendStyle($cell, 'border:none;padding:0;');
                }
            }

            return;
        }

        if (str_contains($class, 'rating-box')) {
            $table->setAttribute('border', '1');
            $table->setAttribute('cellspacing', '0');
            $table->setAttribute('cellpadding', '0');
            $table->setAttribute('align', 'left');
            if ($table->getAttribute('width') === '') {
                $table->setAttribute('width', '100%');
            }
            $this->appendStyle($table, 'border-collapse:collapse;width:100%;mso-table-lspace:0pt;mso-table-rspace:0pt;mso-table-layout-alt:auto;');

            foreach ($this->cellsInTable($table) as $cell) {
                $cellClass = $cell->getAttribute('class');
                $cell->setAttribute('valign', 'middle');
                $style = 'border:1px solid #111111;mso-border-alt:solid #111111 0.5pt;text-align:center;font-weight:bold;padding:2pt;';
                if (str_contains($cellClass, 'rb-head')) {
                    $style .= 'background:#4472C4;color:#ffffff;';
                } elseif (str_contains($cellClass, 'rb-cell')) {
                    $style .= 'background:#F8CBAD;color:#111111;';
                }
                $this->appendStyle($cell, $style);
            }

            return;
        }

        if (str_contains($class, 'sign-table')) {
            $table->setAttribute('border', '1');
            $table->setAttribute('cellspacing', '0');
            $table->setAttribute('cellpadding', '0');
            $this->appendStyle($table, implode('', [
                'border-collapse:collapse;',
                'width:100%;',
                'margin-top:6mm;',
                'mso-table-lspace:0pt;',
                'mso-table-rspace:0pt;',
            ]));

            foreach ($this->cellsInTable($table) as $cell) {
                $cell->setAttribute('valign', 'top');
                $this->appendStyle($cell, implode('', [
                    'border:1px solid #222222;',
                    'mso-border-alt:solid #222222 0.5pt;',
                    'vertical-align:top;',
                    'padding:6pt;',
                    'width:33.33%;',
                ]));
                $this->normalizeParagraphsInCell($cell);
            }

            return;
        }

        $table->setAttribute('border', '1');
        $table->setAttribute('cellspacing', '0');
        $table->setAttribute('cellpadding', '0');
        $this->appendStyle($table, implode('', [
            'border-collapse:collapse;',
            'width:100%;',
            'mso-table-lspace:0pt;',
            'mso-table-rspace:0pt;',
        ]));

        foreach ($this->cellsInTable($table) as $cell) {
            $this->prepareGridCell($cell, $class);
        }

        $this->fixRatingCells($table);
    }

    protected function fixRatingCells(DOMElement $table): void
    {
        foreach ($this->cellsInTable($table) as $cell) {
            if (! str_contains($cell->getAttribute('class'), 'rating-cell')) {
                continue;
            }

            $cell->setAttribute('valign', 'middle');
            $this->appendStyle($cell, 'padding:0;vertical-align:middle;mso-padding-alt:0;');

            foreach ($cell->getElementsByTagName('table') as $nested) {
                if (! $nested instanceof DOMElement) {
                    continue;
                }

                $nested->setAttribute('align', 'left');
                $nested->setAttribute('width', '100%');
                $this->appendStyle($nested, 'margin:0;mso-table-overlap:never;mso-table-layout-alt:auto;');
            }
        }
    }

    protected function prepareGridCell(DOMElement $cell, string $tableClass): void
    {
        $tag = strtolower($cell->nodeName);
        $cellClass = $cell->getAttribute('class');
        $existing = strtolower($cell->getAttribute('style'));

        $border = 'border:1px solid #222222;mso-border-alt:solid #222222 0.5pt;';
        $padding = str_contains($cellClass, 'rating-cell')
            ? 'padding:0;'
            : 'padding:3pt 4pt;';

        $style = $border.$padding.'vertical-align:middle;';

        if ($tag === 'th') {
            $cell->setAttribute('valign', 'middle');
            if (! str_contains($existing, 'background:')) {
                $style .= 'background:#d9d9d9;mso-shading:windowtext;mso-pattern:gray-25 auto;';
            }
            if (! str_contains($existing, 'color:')) {
                $style .= 'color:#111111;';
            }
            $style .= 'font-weight:bold;text-align:center;';
        }

        if (str_contains($cellClass, 'section')) {
            $style .= 'background:#efefef;font-weight:bold;text-align:left;';
        }

        if (str_contains($cellClass, 'align-top')) {
            $cell->setAttribute('valign', 'top');
            $style .= 'vertical-align:top;text-align:left;';
        }

        if (str_contains($cellClass, 'left-align')) {
            $style .= 'text-align:left;';
        }

        if (str_contains($cellClass, 'center')) {
            $style .= 'text-align:center;';
        }

        if (str_contains($cellClass, 'right-align')) {
            $style .= 'text-align:right;';
        }

        if (str_contains($tableClass, 'obs-table') && $tag === 'th' && ! str_contains($existing, 'background:')) {
            $style .= 'background:#2E5090;color:#ffffff;';
        }

        if (str_contains($cellClass, 'rating-cell')) {
            $cell->setAttribute('valign', 'middle');
            $style .= 'vertical-align:middle;';
        }

        $this->appendStyle($cell, $style);
    }

    protected function normalizeParagraphsInCell(DOMElement $cell): void
    {
        foreach ($cell->getElementsByTagName('p') as $paragraph) {
            if (! $paragraph instanceof DOMElement || $paragraph->parentNode !== $cell) {
                continue;
            }

            $this->appendStyle($paragraph, 'margin:0 0 2pt;mso-margin-top-alt:0;mso-margin-bottom-alt:2pt;');
        }
    }

    /**
     * @return list<DOMElement>
     */
    protected function cellsInTable(DOMElement $table): array
    {
        $cells = [];
        foreach (['th', 'td'] as $tag) {
            foreach ($table->getElementsByTagName($tag) as $cell) {
                if ($cell instanceof DOMElement && $this->owningTable($cell) === $table) {
                    $cells[] = $cell;
                }
            }
        }

        return $cells;
    }

    protected function owningTable(DOMElement $cell): ?DOMElement
    {
        $node = $cell->parentNode;
        while ($node) {
            if ($node instanceof DOMElement && $node->nodeName === 'tr') {
                $section = $node->parentNode;
                if ($section instanceof DOMElement) {
                    if ($section->nodeName === 'table') {
                        return $section;
                    }
                    $owner = $section->parentNode;
                    if ($owner instanceof DOMElement && $owner->nodeName === 'table') {
                        return $owner;
                    }
                }
            }
            $node = $node->parentNode;
        }

        return null;
    }

    protected function appendStyle(DOMElement $element, string $style): void
    {
        $style = trim($style);
        if ($style === '') {
            return;
        }

        $existing = trim($element->getAttribute('style'), " \t\n\r\0\x0B;");
        $element->setAttribute('style', $existing === '' ? $style : $existing.';'.$style);
    }
}
