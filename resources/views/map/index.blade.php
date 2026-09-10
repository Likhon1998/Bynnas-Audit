<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Map · {{ config('app.name', 'Bynnas Audit') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/bynnas-logo.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        html, body { height: 100%; overflow: hidden; }
        [x-cloak] { display: none !important; }
        .audit-map { background: #0b1d12; }
        .audit-map .leaflet-control-zoom { border: 0; box-shadow: 0 8px 24px rgba(15,23,42,.25); }
        .audit-map .leaflet-control-zoom a {
            border: 1px solid rgba(15,23,42,.12);
            width: 32px; height: 32px; line-height: 32px;
            background: #fff; color: #0f172a;
        }
        .audit-map .leaflet-control-zoom a:hover { background: #f8fafc; }
        .audit-map .leaflet-control-attribution {
            background: rgba(255,255,255,.82);
            color: #475569;
            border: 0;
        }
        .audit-map .leaflet-control-attribution a { color: #0f172a; }
        .audit-map .leaflet-top.leaflet-left { top: 48px; left: 4px; }
        .map-layer-btn { background: rgba(15,23,42,.72); color: #e2e8f0; }
        .map-layer-btn.is-on { background: #0f766e; color: #fff; }
        .audit-map .leaflet-interactive:focus {
            outline: 2px solid #f8fafc;
            outline-offset: 2px;
        }
        .territory-tooltip { background: rgba(15,23,42,.92); color: #f8fafc; border: 1px solid rgba(255,255,255,.12); }
        .leaflet-pane.bd-glow-pane { z-index: 410; }
        .leaflet-pane.bd-fill-pane { z-index: 420; }
        .leaflet-pane.bd-border-pane { z-index: 430; }
        .leaflet-pane.territory-pane { z-index: 450; }
        .map-select { background-color: rgba(255,255,255,.08); color: #fff; border-color: rgba(255,255,255,.12); }
        .map-select option { color: #0f172a; }
        .audit-map.is-dark-basemap .leaflet-tile-pane {
            filter: invert(1) hue-rotate(180deg) brightness(0.92) contrast(0.95) saturate(0.55);
        }
    </style>
</head>
<body
    class="h-screen overflow-hidden font-sans text-[13px] text-slate-100 antialiased"
    x-data="auditMapPage({
        liveUrl: @js(route('map.live')),
        dashboardUrl: @js(route('dashboard')),
        geoBase: '/geo'
    })"
    x-init="boot()"
>
    <div class="flex h-screen">
        <aside class="flex h-full shrink-0 flex-col border-r border-white/10 bg-slate-900/80 text-white backdrop-blur-md transition-[width] duration-200" :class="sidebarOpen ? 'w-[300px]' : 'w-0 overflow-hidden'">
            <div class="flex min-h-0 w-[300px] flex-1 flex-col">
                <div class="flex items-center gap-2 border-b border-white/10 px-3 py-3">
                    <img src="{{ asset('images/bynnas-logo.png') }}?v=3" alt="" class="h-8 w-8 rounded-lg object-contain">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-semibold">Bynnas <span class="text-sky-300">Map</span></p>
                        <p class="text-[10px] text-slate-400">Voronoi territories · risk fill</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[10px] font-semibold text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full" :class="live ? 'animate-pulse bg-emerald-400' : 'bg-slate-500'"></span>
                        <span x-text="live ? 'Live' : 'Off'"></span>
                    </span>
                </div>

                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-3 py-3">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Search</label>
                        <input type="search" x-model="query" @input.debounce.200ms="search()" placeholder="Find a place or shakha" class="h-9 w-full rounded-lg border-white/10 bg-white/10 text-[12px] text-white placeholder:text-slate-400 focus:border-sky-400 focus:ring-sky-400">
                        <div x-show="results.length" x-cloak class="mt-1.5 overflow-hidden rounded-lg border border-white/10 bg-slate-900/80 backdrop-blur-md">
                            <template x-for="item in results" :key="item.key">
                                <button type="button" class="flex w-full items-start gap-2 px-2.5 py-1.5 text-left hover:bg-white/10" @click="goTo(item)">
                                    <span class="mt-0.5 rounded px-1.5 py-0.5 text-[9px] font-semibold uppercase text-sky-200" :style="'background:' + item.color + '33'" x-text="item.typeLabel"></span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-[12px]" x-text="item.label"></span>
                                        <span class="block truncate text-[10px] text-slate-400" x-text="item.meta"></span>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Separate section</label>
                        <div class="grid grid-cols-2 gap-1.5">
                            <template x-for="mode in sectionOptions" :key="mode.key">
                                <button type="button" class="rounded-lg border px-2 py-2 text-[11px] font-semibold" :class="section === mode.key ? 'border-sky-300/60 bg-sky-400/20 text-white' : 'border-white/10 bg-white/5 text-slate-300'" @click="setSection(mode.key)" x-text="mode.label"></button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Division</label>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" class="rounded-lg border px-2 py-2 text-[11px] font-semibold" :class="!selectedDivision ? 'border-sky-300/60 bg-sky-400/20 text-white' : 'border-white/10 bg-white/5 text-slate-300'" @click="selectDivision('')">All</button>
                            <template x-for="name in mapDivisionNames" :key="name">
                                <button type="button" class="rounded-lg border px-2 py-2 text-[11px] font-semibold" :class="selectedDivision === name ? 'border-sky-300/60 bg-sky-400/20 text-white' : 'border-white/10 bg-white/5 text-slate-300'" @click="selectDivision(name)" x-text="name"></button>
                            </template>
                        </div>
                    </div>

                    <div x-show="section === 'all' || section === 'district' || section === 'pouroshova'">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">District</label>
                        <select x-model="selectedDistrict" @change="onDistrictChange()" class="map-select h-9 w-full rounded-lg py-0 text-[12px]">
                            <option value="">All districts</option>
                            <template x-for="name in districtNames" :key="name">
                                <option :value="name" x-text="name"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="section === 'all' || section === 'pouroshova'">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Pouroshova</label>
                        <select x-model="selectedPouroshova" @change="applyFilters()" class="map-select h-9 w-full rounded-lg py-0 text-[12px]">
                            <option value="">All pouroshova</option>
                            <template x-for="name in pouroshovaNames" :key="name">
                                <option :value="name" x-text="name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Map layer</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button type="button" class="rounded-lg border px-2 py-1.5 text-[10px] font-semibold" :class="basemap === 'osm' ? 'border-sky-300/60 bg-sky-400/20 text-white' : 'border-white/10 text-slate-300'" @click="setBasemap('osm')">OpenStreet</button>
                            <button type="button" class="rounded-lg border px-2 py-1.5 text-[10px] font-semibold" :class="basemap === 'hybrid' ? 'border-sky-300/60 bg-sky-400/20 text-white' : 'border-white/10 text-slate-300'" @click="setBasemap('hybrid')">Hybrid</button>
                            <button type="button" class="rounded-lg border px-2 py-1.5 text-[10px] font-semibold" :class="basemap === 'satellite' ? 'border-sky-300/60 bg-sky-400/20 text-white' : 'border-white/10 text-slate-300'" @click="setBasemap('satellite')">Satellite</button>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Risk legend</label>
                        <div class="space-y-1">
                            <button type="button" class="flex w-full items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-2 py-1.5 text-left" @click="toggleRisk('low')">
                                <span class="h-2.5 w-2.5 rounded-sm bg-emerald-500 ring-1 ring-emerald-300" :class="risks.low ? '' : 'opacity-30'"></span>
                                <span class="text-[11px] text-slate-200">Low</span>
                            </button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-2 py-1.5 text-left" @click="toggleRisk('medium')">
                                <span class="h-2.5 w-2.5 rounded-sm bg-amber-500 ring-1 ring-amber-300" :class="risks.medium ? '' : 'opacity-30'"></span>
                                <span class="text-[11px] text-slate-200">Medium</span>
                            </button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-2 py-1.5 text-left" @click="toggleRisk('high')">
                                <span class="h-2.5 w-2.5 rounded-sm bg-rose-500 ring-1 ring-rose-300" :class="risks.high && risks.significant ? '' : 'opacity-30'"></span>
                                <span class="text-[11px] text-slate-200">High / significant</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Area</label>
                        <select x-model="selectedArea" @change="zoomToArea(selectedArea)" class="map-select h-9 w-full rounded-lg py-0 text-[12px]">
                            <option value="">All areas</option>
                            <template x-for="area in areaLegend" :key="area.name">
                                <option :value="area.name" x-text="area.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Areas</label>
                            <button type="button" class="text-[10px] font-semibold text-sky-300" @click="showShakhas = !showShakhas; renderTerritories()" x-text="showShakhas ? 'Hide territories' : 'Show territories'"></button>
                        </div>
                        <div class="max-h-48 space-y-1 overflow-y-auto pr-0.5">
                            <template x-for="area in areaLegend" :key="area.name">
                                <button type="button" class="flex w-full items-center gap-2 rounded-lg border px-2 py-1.5 text-left" :class="!selectedArea || selectedArea === area.name ? 'border-white/15 bg-white/5' : 'border-white/5 opacity-35'" @click="zoomToArea(selectedArea === area.name ? '' : area.name)">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white/40" :style="'background:' + area.color"></span>
                                    <span class="min-w-0 flex-1 truncate text-[11px] text-slate-200" x-text="area.name"></span>
                                    <span class="text-[11px] text-slate-400" x-text="area.count"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <p class="text-[10px] leading-relaxed text-slate-400" x-text="statusText"></p>
                </div>

                <div class="border-t border-white/10 px-3 py-2.5">
                    <a :href="dashboardUrl" class="flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-[12px] font-semibold text-white hover:bg-white/10">Back to dashboard</a>
                </div>
            </div>
        </aside>

        <div class="relative min-w-0 flex-1">
            <div id="audit-map" class="audit-map absolute inset-0"></div>
            <div x-show="busy" x-cloak class="absolute inset-0 z-[490] flex items-center justify-center bg-slate-950/45 backdrop-blur-sm">
                <p class="rounded-xl border border-white/10 bg-slate-900/80 px-4 py-2 text-[12px] text-white" x-text="statusText"></p>
            </div>
            <button type="button" class="absolute left-3 top-3 z-[500] flex h-9 w-9 items-center justify-center rounded-lg border border-white/70 bg-white text-slate-800 shadow" @click="sidebarOpen = !sidebarOpen" :title="sidebarOpen ? 'Hide map menu' : 'Show map menu'" :aria-expanded="sidebarOpen" aria-label="Toggle map menu">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <div class="absolute bottom-3 left-3 z-[500] overflow-hidden rounded-lg border border-white/40 bg-slate-900/80 shadow-lg backdrop-blur-md">
                <div class="flex">
                    <button type="button" class="map-layer-btn px-3 py-2 text-[11px] font-semibold" :class="basemap === 'osm' ? 'is-on' : ''" @click="setBasemap('osm')">OpenStreet</button>
                    <button type="button" class="map-layer-btn px-3 py-2 text-[11px] font-semibold" :class="basemap === 'hybrid' ? 'is-on' : ''" @click="setBasemap('hybrid')">Hybrid</button>
                    <button type="button" class="map-layer-btn px-3 py-2 text-[11px] font-semibold" :class="basemap === 'satellite' ? 'is-on' : ''" @click="setBasemap('satellite')">Satellite</button>
                </div>
            </div>
            <div class="absolute bottom-3 right-3 z-[500] rounded-xl border border-white/10 bg-slate-900/80 px-3 py-2 text-white shadow-lg backdrop-blur-md">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400" x-text="sectionLabel"></p>
                <p class="text-[18px] font-semibold text-white" x-text="visibleCount"></p>
                <p class="text-[10px] text-slate-400">risk territories</p>
            </div>
        </div>
    </div>

    <x-shakha-drawer />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/@turf/turf@6.5.0/turf.min.js"></script>
    <script>
    function auditMapPage(config) {
        const divisionColors = {
            dhaka: '#2563eb',
            chattogram: '#0d9488',
            rajshahi: '#c026d3',
            khulna: '#ea580c',
            barishal: '#0891b2',
            sylhet: '#65a30d',
            rangpur: '#4f46e5',
            mymensingh: '#db2777',
        };
        const riskColors = {
            significant: '#e11d48',
            high: '#ea580c',
            medium: '#d97706',
            low: '#059669',
            unassessed: '#64748b',
        };

        return {
            liveUrl: config.liveUrl,
            dashboardUrl: config.dashboardUrl,
            geoBase: config.geoBase,
            geoIndex: { all: 'all.json', divisions: {} },
            geoCache: {},
            sidebarOpen: true,
            query: '',
            results: [],
            live: true,
            busy: true,
            statusText: 'Loading map…',
            fingerprint: '',
            markers: [],
            places: [],
            geoFeatures: [],
            stats: { risk: {} },
            section: 'all',
            selectedDivision: '',
            selectedDistrict: '',
            selectedPouroshova: '',
            selectedArea: '',
            showShakhas: false,
            basemap: 'satellite',
            risks: { significant: true, high: true, medium: true, low: true, unassessed: true },
            sectionOptions: [
                { key: 'all', label: 'All' },
                { key: 'division', label: 'Division' },
                { key: 'district', label: 'District' },
                { key: 'pouroshova', label: 'Pouroshova' },
            ],
            map: null,
            tileLayers: {},
            fillLayer: null,
            highlightLayer: null,
            countryLayers: [],
            countryGeo: null,
            divisionGeo: null,
            districtGeo: null,
            upazilaGeo: null,
            allGeo: null,
            detailGeo: null,
            territoryLayer: null,
            fillRenderer: null,
            territoryRenderer: null,
            get divisionNames() {
                return [...new Set(this.places.filter((p) => p.type === 'bivag').map((p) => p.division))].filter(Boolean).sort();
            },
            get mapDivisionNames() {
                const fromIndex = Object.keys(this.geoIndex.divisions || {});
                return (fromIndex.length ? fromIndex : this.divisionNames).slice().sort();
            },
            get districtNames() {
                return [...new Set(this.places.filter((p) => p.type === 'jela' && (!this.selectedDivision || p.division === this.selectedDivision)).map((p) => p.district || p.label))].filter(Boolean).sort();
            },
            get pouroshovaNames() {
                return [...new Set(this.places.filter((p) => p.type === 'pouroshova' && (!this.selectedDivision || p.division === this.selectedDivision) && (!this.selectedDistrict || p.district === this.selectedDistrict)).map((p) => p.label))].filter(Boolean).sort();
            },
            get areaLegend() {
                const counts = {};
                this.markers.forEach((marker) => {
                    const name = marker.area || 'Unassigned';
                    if (!counts[name]) counts[name] = { name, color: marker.area_color || '#64748b', count: 0 };
                    counts[name].count += 1;
                });
                return Object.values(counts).sort((a, b) => a.name.localeCompare(b.name));
            },
            get sectionLabel() {
                return this.sectionOptions.find((item) => item.key === this.section)?.label || 'All';
            },
            get visibleCount() {
                return this.filteredMarkers().length;
            },
            async boot() {
                this.map = L.map('audit-map', {
                    zoomControl: true,
                    minZoom: 6,
                    maxZoom: 16,
                    preferCanvas: true,
                    fadeAnimation: false,
                    markerZoomAnimation: false,
                }).setView([23.7, 90.35], 7);
                this.map.zoomControl.setPosition('topleft');
                this.map.createPane('bdGlow'); this.map.getPane('bdGlow').classList.add('bd-glow-pane');
                this.map.createPane('bdFill'); this.map.getPane('bdFill').classList.add('bd-fill-pane');
                this.map.createPane('bdBorder'); this.map.getPane('bdBorder').classList.add('bd-border-pane');
                this.map.createPane('territory');
                this.map.getPane('territory').classList.add('territory-pane');
                this.fillRenderer = L.canvas({ pane: 'bdFill', padding: 0.5 });
                this.territoryRenderer = L.canvas({ pane: 'territory', padding: 0.5 });
                this.tileLayers.osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap', updateWhenIdle: true, keepBuffer: 2 });
                this.tileLayers.satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 18, attribution: 'Esri', updateWhenIdle: true, keepBuffer: 2 });
                this.tileLayers.labels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', { maxZoom: 18, updateWhenIdle: true });
                this.territoryLayer = L.layerGroup();
                this.setBasemap('satellite');
                this.map.addLayer(this.territoryLayer);
                await Promise.all([this.loadIndex(), this.refresh()]);
                this.busy = false;
                this.loadAllOutline();
                setInterval(() => this.refresh(), 12000);
            },
            setBasemap(name) {
                this.basemap = name;
                Object.values(this.tileLayers).forEach((layer) => this.map.removeLayer(layer));
                this.map.getContainer().classList.toggle('is-dark-basemap', false);
                if (name === 'osm') this.tileLayers.osm.addTo(this.map);
                else {
                    this.tileLayers.satellite.addTo(this.map);
                    if (name === 'hybrid') this.tileLayers.labels.addTo(this.map);
                }
            },
            setSection(key) {
                this.section = key;
                if (key === 'division') {
                    this.selectedDistrict = '';
                    this.selectedPouroshova = '';
                }
                if (key === 'district') this.selectedPouroshova = '';
                this.applyFilters();
            },
            async selectDivision(name) {
                this.selectedDivision = name || '';
                this.selectedDistrict = '';
                this.selectedPouroshova = '';
                if (!this.selectedDivision) this.detailGeo = null;
                if (this.selectedDivision && this.section === 'all') this.section = 'division';
                if (this.selectedDivision) await this.ensureDetail(this.selectedDivision);
                this.rebuildFill();
                this.renderTerritories();
                this.fitToSelection();
                this.statusText = this.selectedDivision ? this.selectedDivision : 'Choose a division to open its map.';
            },
            onDistrictChange() {
                if (this.selectedDistrict && !this.selectedDivision) {
                    const place = this.places.find((row) => row.type === 'jela' && row.label === this.selectedDistrict);
                    if (place?.division) this.selectedDivision = place.division;
                }
                this.applyFilters();
            },
            async fetchGeo(path) {
                if (!path) return null;
                if (this.geoCache[path]) return this.geoCache[path];
                const response = await fetch(this.geoBase + '/' + path);
                if (!response.ok) throw new Error('geo ' + path);
                const json = await response.json();
                this.geoCache[path] = json;
                return json;
            },
            async ensureDetail(name) {
                const path = this.geoIndex.divisions?.[name];
                if (!name || !path) {
                    this.detailGeo = null;
                    return;
                }
                this.statusText = 'Opening ' + name + '…';
                this.detailGeo = await this.fetchGeo(path);
                this.upazilaGeo = this.detailGeo;
                this.districtGeo = this.detailGeo;
            },
            async loadIndex() {
                try {
                    this.statusText = 'Loading division list…';
                    const index = await this.fetchGeo('index.json');
                    this.geoIndex = index || this.geoIndex;
                    this.statusText = 'Choose a division to open its map.';
                } catch (e) {
                    this.statusText = 'Division list unavailable.';
                }
            },
            async loadAllOutline() {
                try {
                    this.allGeo = await this.fetchGeo(this.geoIndex.all || 'all.json');
                    this.countryGeo = this.allGeo;
                    this.divisionGeo = this.allGeo;
                    this.drawCountryBorder();
                    if (!this.selectedDivision) this.rebuildFill();
                    if (!this.selectedDivision) this.statusText = 'Choose a division to open its map.';
                } catch (e) {
                    this.statusText = 'Boundaries unavailable. Open a division from the list.';
                }
            },
            convexSafe(collection) {
                try {
                    if (!window.turf?.convex) return null;
                    const hull = turf.convex(collection);
                    if (!hull) return null;
                    hull.properties = { country: 'Bangladesh' };
                    return { type: 'FeatureCollection', features: [hull] };
                } catch (e) {
                    return null;
                }
            },
            drawCountryBorder() {
                this.countryLayers.forEach((layer) => this.map.removeLayer(layer));
                this.countryLayers = [];
            },
            fillSource() {
                if (this.selectedDivision && this.detailGeo) return this.detailGeo;
                return this.allGeo;
            },
            rebuildFill() {
                if (this.fillLayer) this.map.removeLayer(this.fillLayer);
                if (this.highlightLayer) this.map.removeLayer(this.highlightLayer);
                const source = this.fillSource();
                if (!source) return;
                this.fillLayer = L.geoJSON(source, {
                    pane: 'bdFill',
                    renderer: this.fillRenderer,
                    style: (feature) => this.fillStyle(feature),
                    filter: (feature) => this.featureVisible(feature.properties || {}),
                    onEachFeature: (feature, layer) => {
                        const props = feature.properties || {};
                        layer.bindTooltip(this.featureLabel(props), { sticky: true, className: 'territory-tooltip' });
                        layer.on('click', () => this.pickFeature(props));
                    },
                }).addTo(this.map);
            },
            featureLabel(props) {
                return [props.name, props.district_name, props.division_name].filter(Boolean).join(' · ') || 'Bangladesh';
            },
            isPouroshova(props) {
                const names = this.pouroshovaNames.map((name) => this.norm(name));
                const hay = this.norm([props.name, props.district_name].join(' '));
                if (names.some((name) => name && hay.includes(name))) return true;
                return /\b(sadar|city|pourashava|paurashava|pouroshova|corporation)\b/i.test(String(props.name || '') + ' ' + String(props.source_name || ''));
            },
            colorForDivision(name) {
                return divisionColors[this.norm(name).replace(/\s+/g, '')] || '#64748b';
            },
            colorForDistrict(name) {
                const text = this.norm(name);
                let hash = 0;
                for (let i = 0; i < text.length; i++) hash = text.charCodeAt(i) + ((hash << 5) - hash);
                return 'hsl(' + Math.abs(hash % 360) + ', 62%, 48%)';
            },
            colorFill(fillColor, opacity, lineColor) {
                return {
                    stroke: true,
                    color: lineColor || fillColor,
                    weight: lineColor ? 0.7 : 1,
                    opacity: lineColor ? 0.55 : opacity,
                    fill: true,
                    fillColor,
                    fillOpacity: opacity,
                    lineJoin: 'round',
                };
            },
            fillStyle(feature) {
                const props = feature.properties || {};
                if (!this.featureVisible(props)) {
                    return { stroke: false, fill: false, fillOpacity: 0, opacity: 0, weight: 0 };
                }
                if (this.section === 'district') {
                    return this.colorFill(this.colorForDistrict(props.district_name), 0.5, this.colorForDistrict(props.district_name));
                }
                if (this.section === 'pouroshova') {
                    return this.colorFill('#f9a8d4', 0.45, '#f9a8d4');
                }
                if (this.selectedDivision) {
                    return {
                        stroke: false,
                        fill: true,
                        fillColor: '#d6dce3',
                        fillOpacity: 0.52,
                        weight: 0,
                    };
                }
                return this.colorFill('#d1d5db', 0.48, '#9ca3af');
            },
            isFocused(props) {
                if (this.section === 'division') {
                    return !this.selectedDivision || this.norm(props.division_name) === this.norm(this.selectedDivision);
                }
                if (this.section === 'district') {
                    return !this.selectedDistrict || this.norm(props.district_name) === this.norm(this.selectedDistrict);
                }
                if (this.section === 'pouroshova') {
                    return !this.selectedPouroshova || this.norm(props.name) === this.norm(this.selectedPouroshova) || this.norm(props.district_name) === this.norm(this.selectedPouroshova);
                }
                return true;
            },
            featureVisible(props) {
                if (this.selectedDivision && props.division_name && this.norm(props.division_name) !== this.norm(this.selectedDivision)) return false;
                if (this.selectedDistrict && props.district_name && this.norm(props.district_name) !== this.norm(this.selectedDistrict)) return false;
                if (this.selectedArea && (props.district_name || props.name)) {
                    const area = this.norm(this.selectedArea);
                    const hay = this.norm([props.district_name, props.name].join(' '));
                    if (!hay.includes(area) && !area.includes(this.norm(props.district_name || props.name || ''))) return false;
                }
                if (this.section === 'pouroshova' && !this.isPouroshova(props)) return false;
                if (this.selectedPouroshova && this.norm(props.name) !== this.norm(this.selectedPouroshova) && this.norm(props.district_name) !== this.norm(this.selectedPouroshova)) return false;
                return true;
            },
            pickFeature(props) {
                if (!this.selectedDivision && (props.division_name || props.layer === 'division')) {
                    this.selectDivision(props.division_name || props.name || '');
                    return;
                }
                if (this.section === 'division') {
                    this.selectedDivision = props.division_name || this.selectedDivision;
                    this.selectedDistrict = '';
                    this.selectedPouroshova = '';
                }
                if (this.section === 'district') {
                    this.selectedDivision = props.division_name || this.selectedDivision;
                    this.selectedDistrict = props.district_name || '';
                    this.selectedPouroshova = '';
                }
                if (this.section === 'pouroshova') {
                    this.selectedDivision = props.division_name || this.selectedDivision;
                    this.selectedDistrict = props.district_name || this.selectedDistrict;
                    this.selectedPouroshova = props.name || '';
                }
                this.applyFilters(true);
            },
            async applyFilters(fit = true) {
                try {
                    if (this.selectedDivision && (this.section === 'district' || this.section === 'pouroshova' || this.selectedDistrict || this.selectedPouroshova)) {
                        await this.ensureDetail(this.selectedDivision);
                    }
                    this.rebuildFill();
                    this.renderTerritories();
                    if (fit) this.fitToSelection();
                    this.statusText = this.selectedDivision
                        ? this.selectedDivision + ' · shakha territories'
                        : 'Choose a division to open its map.';
                } finally {
                    this.busy = false;
                }
            },
            restyle() {
                this.rebuildFill();
            },
            zoomToArea(name) {
                this.selectedArea = name || '';
                this.showShakhas = false;
                this.renderTerritories();
                this.rebuildFill();
                if (!this.selectedArea) {
                    this.fitToSelection();
                    this.statusText = 'Showing all of Bangladesh';
                    return;
                }
                const points = this.markers.filter((marker) => (marker.area || 'Unassigned') === this.selectedArea);
                let group = null;
                points.forEach((marker) => {
                    const latlng = L.latLng(marker.lat, marker.lng);
                    group = group ? group.extend(latlng) : L.latLngBounds(latlng, latlng);
                });
                if (this.fillLayer) {
                    this.fillLayer.eachLayer((layer) => {
                        if (layer.getBounds) group = group ? group.extend(layer.getBounds()) : layer.getBounds();
                    });
                }
                if (!group || !group.isValid()) {
                    this.statusText = this.selectedArea + ' has no map points yet';
                    return;
                }
                const zoom = points.length <= 2 ? 13 : 12;
                this.map.fitBounds(group.pad(0.45), { maxZoom: zoom, animate: true, duration: 0.8 });
                this.statusText = 'Zoomed to ' + this.selectedArea;
            },
            fitToSelection() {
                const layer = this.fillLayer;
                if (!layer) {
                    this.map.setView([23.7, 90.35], 7);
                    return;
                }
                const bounds = [];
                layer.eachLayer((item) => {
                    if (item.getBounds) bounds.push(item.getBounds());
                });
                if (!bounds.length) {
                    this.map.setView([23.7, 90.35], 7);
                    return;
                }
                const merged = bounds.reduce((all, next) => all.extend(next), bounds[0]);
                this.map.fitBounds(merged.pad(this.selectedDivision || this.selectedDistrict || this.selectedArea ? 0.18 : 0.06));
            },
            toggleRisk(key) {
                if (key === 'high') {
                    const next = !(this.risks.high && this.risks.significant);
                    this.risks.high = next;
                    this.risks.significant = next;
                } else {
                    this.risks[key] = !this.risks[key];
                }
                this.renderTerritories();
            },
            filteredMarkers() {
                return this.markers.filter((marker) => {
                    if (!this.showShakhas) return false;
                    if (!this.risks[marker.risk]) return false;
                    if (this.selectedArea && (marker.area || 'Unassigned') !== this.selectedArea) return false;
                    if (this.selectedDivision && this.norm(marker.division) !== this.norm(this.selectedDivision)) return false;
                    if (this.selectedDistrict && this.norm(marker.district) !== this.norm(this.selectedDistrict) && this.norm(marker.area) !== this.norm(this.selectedDistrict)) return false;
                    if (this.section === 'pouroshova' && !marker.pourashava && !marker.upazila) return false;
                    if (this.selectedPouroshova) {
                        const hay = this.norm([marker.pourashava, marker.upazila, marker.name, marker.district].join(' '));
                        if (!hay.includes(this.norm(this.selectedPouroshova))) return false;
                    }
                    return Number.isFinite(marker.lat) && Number.isFinite(marker.lng);
                });
            },
            activeClipPolygon() {
                const focused = [];
                if (this.fillLayer) {
                    this.fillLayer.eachLayer((layer) => {
                        if (layer.feature && this.isFocused(layer.feature.properties || {})) focused.push(layer.feature);
                    });
                }
                const hasLocalClip = this.selectedDistrict || this.selectedPouroshova || this.selectedArea
                    || (this.section === 'division' && this.selectedDivision)
                    || (this.section === 'district' && this.selectedDistrict)
                    || (this.section === 'pouroshova' && this.selectedPouroshova);
                if (hasLocalClip && focused.length) return this.unionFeatures(focused);
                if (this.countryGeo?.features?.length) return this.unionFeatures(this.countryGeo.features);
                if (focused.length) return this.unionFeatures(focused);
                return window.turf ? turf.bboxPolygon([88.01, 20.59, 92.68, 26.64]) : null;
            },
            jitterPoints(points) {
                const seen = new Set();
                points.features.forEach((feature, index) => {
                    let [lng, lat] = feature.geometry.coordinates;
                    let key = lng.toFixed(5) + ',' + lat.toFixed(5);
                    if (seen.has(key)) {
                        const angle = (index * 2.399) % (Math.PI * 2);
                        lng += Math.cos(angle) * 0.01;
                        lat += Math.sin(angle) * 0.01;
                        feature.geometry.coordinates = [lng, lat];
                        key = lng.toFixed(5) + ',' + lat.toFixed(5);
                    }
                    seen.add(key);
                });
                return points;
            },
            unionFeatures(features) {
                const valid = (features || []).filter((feature) => feature?.geometry);
                if (!valid.length || !window.turf) return null;
                if (valid.length === 1) return valid[0];
                try {
                    const combined = turf.combine(turf.featureCollection(valid));
                    return combined.features?.[0] || valid[0];
                } catch (e) {
                    return valid[0];
                }
            },
            clipToBoundary(cell, clip) {
                if (!cell || !clip || !window.turf?.intersect) return null;
                let parts = (clip.geometry?.type === 'MultiPolygon' || clip.geometry?.type === 'GeometryCollection')
                    ? turf.flatten(clip).features
                    : [clip];
                if (parts.length > 6) {
                    parts = parts
                        .map((feature) => ({ feature, area: turf.area(feature) }))
                        .sort((a, b) => b.area - a.area)
                        .slice(0, 6)
                        .map((row) => row.feature);
                }
                const bits = [];
                parts.forEach((poly) => {
                    try {
                        const cut = turf.intersect(cell, poly);
                        if (cut) bits.push(cut);
                    } catch (e) {}
                });
                if (!bits.length) return null;
                if (bits.length === 1) return bits[0];
                try {
                    return turf.combine(turf.featureCollection(bits)).features[0];
                } catch (e) {
                    return bits[0];
                }
            },
            territoryStyle(risk) {
                const band = (risk === 'high' || risk === 'significant') ? 'high'
                    : (risk === 'medium') ? 'medium'
                    : (risk === 'low') ? 'low'
                    : 'unassessed';
                const palette = {
                    low: { fillColor: '#10b981', color: '#6ee7b7' },
                    medium: { fillColor: '#f59e0b', color: '#fcd34d' },
                    high: { fillColor: '#f43f5e', color: '#fda4af' },
                    unassessed: { fillColor: '#64748b', color: '#cbd5e1' },
                };
                const colors = palette[band];
                return {
                    stroke: false,
                    fill: true,
                    fillColor: colors.fillColor,
                    fillOpacity: 0.42,
                    weight: 0,
                };
            },
            openShakhaDrawer(shakha) {
                window.dispatchEvent(new CustomEvent('open-shakha-drawer', { detail: shakha }));
            },
            decorateTerritory(layer, shakha) {
                const el = layer.getElement();
                if (!el) return;
                const riskText = shakha.risk_label || shakha.risk || 'risk not assessed';
                el.setAttribute('tabindex', '0');
                el.setAttribute('role', 'region');
                el.setAttribute('aria-label', (shakha.name || 'Shakha') + ', ' + riskText);
                el.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        this.openShakhaDrawer(shakha);
                    }
                });
            },
            addTerritoryLayer(geometry, shakha) {
                if (!this._territoryFeatures) this._territoryFeatures = [];
                const feature = geometry.type === 'Feature' ? geometry : { type: 'Feature', geometry, properties: {} };
                feature.properties = Object.assign({}, feature.properties || {}, { risk: shakha.risk, name: shakha.name, shakha });
                this._territoryFeatures.push(feature);
            },
            flushTerritories() {
                const features = this._territoryFeatures || [];
                this._territoryFeatures = [];
                if (!features.length) return;
                const layer = L.geoJSON({ type: 'FeatureCollection', features }, {
                    pane: 'territory',
                    renderer: this.territoryRenderer,
                    style: (feature) => this.territoryStyle(feature.properties?.risk),
                    onEachFeature: (feature, lyr) => {
                        const shakha = feature.properties?.shakha;
                        if (!shakha) return;
                        lyr.on('click', (event) => {
                            L.DomEvent.stopPropagation(event);
                            this.openShakhaDrawer(shakha);
                        });
                        lyr.bindTooltip(shakha.name + ' · ' + (shakha.risk_label || 'Not assessed'), {
                            sticky: true,
                            className: 'territory-tooltip',
                        });
                    },
                });
                this.territoryLayer.addLayer(layer);
            },
            renderTerritories() {
                if (!this.territoryLayer) return;
                this.territoryLayer.clearLayers();
                this._territoryFeatures = [];
                if (!this.selectedDivision) return;
                const shakhas = this.filteredMarkers();
                if (!shakhas.length || !window.turf) return;
                try {
                    const clip = this.activeClipPolygon();
                    if (!clip) return;
                    const points = this.jitterPoints(turf.featureCollection(shakhas.map((item) => turf.point([item.lng, item.lat], { id: item.id }))));
                    if (points.features.length === 1) {
                        const clipped = this.clipToBoundary(clip, clip) || clip;
                        this.addTerritoryLayer(clipped, shakhas[0]);
                        this.flushTerritories();
                        return;
                    }
                    let bbox = turf.bbox(clip);
                    bbox = [bbox[0] - 0.08, bbox[1] - 0.08, bbox[2] + 0.08, bbox[3] + 0.08];
                    const cells = turf.voronoi(points, { bbox });
                    if (!cells) return;
                    (cells.features || []).forEach((cell) => {
                        if (!cell?.geometry) return;
                        const clipped = this.clipToBoundary(cell, clip);
                        if (!clipped) return;
                        let shakha = shakhas.find((item) => item.id === cell.properties?.id);
                        if (!shakha) {
                            const nearest = turf.nearestPoint(turf.centroid(clipped), points);
                            shakha = shakhas.find((item) => item.id === nearest.properties.id);
                        }
                        if (shakha) this.addTerritoryLayer(clipped, shakha);
                    });
                    this.flushTerritories();
                } catch (e) {
                    this.statusText = 'Territories could not be drawn. Try a smaller district view.';
                }
            },
            renderMarkers() {
                this.renderTerritories();
            },
            async refresh() {
                try {
                    const response = await fetch(this.liveUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    if (!response.ok) throw new Error('live');
                    const data = await response.json();
                    if (data.fingerprint === this.fingerprint && this.markers.length) return;
                    this.fingerprint = data.fingerprint;
                    this.markers = data.markers || [];
                    this.places = data.places || [];
                    this.stats = data.stats || { risk: {} };
                    this.live = true;
                    this.statusText = this.selectedDivision ? this.selectedDivision + ' · shakha territories' : 'Bangladesh · choose a division';
                    this.renderTerritories();
                    this.restyle();
                    if (this.query) this.search();
                } catch (e) {
                    this.live = false;
                    this.statusText = 'Live update paused. Retrying…';
                }
            },
            search() {
                const q = this.norm(this.query);
                if (!q) { this.results = []; return; }
                const items = [];
                this.places.forEach((place) => {
                    if (!this.norm([place.label, place.division, place.district].join(' ')).includes(q)) return;
                    items.push({
                        key: place.type + '-' + place.label,
                        type: place.type,
                        typeLabel: place.type === 'bivag' ? 'Division' : (place.type === 'jela' ? 'District' : 'Pouroshova'),
                        color: place.type === 'bivag' ? this.colorForDivision(place.division) : (place.type === 'jela' ? this.colorForDistrict(place.label) : '#db2777'),
                        label: place.label,
                        meta: [place.district, place.division].filter(Boolean).join(' · '),
                        lat: place.lat,
                        lng: place.lng,
                        value: place.label,
                    });
                });
                this.markers.forEach((marker) => {
                    if (!this.norm([marker.name, marker.area, marker.division, marker.district, marker.pourashava].join(' ')).includes(q)) return;
                    items.push({
                        key: 'shakha-' + marker.id,
                        type: 'shakha',
                        typeLabel: 'Shakha',
                        color: riskColors[marker.risk],
                        label: marker.name,
                        meta: [marker.area, marker.division].filter(Boolean).join(' · '),
                        lat: marker.lat,
                        lng: marker.lng,
                        value: marker.name,
                        shakha: marker,
                    });
                });
                this.results = items.slice(0, 10);
            },
            async goTo(item) {
                this.query = item.label;
                this.results = [];
                if (item.type === 'bivag') {
                    await this.selectDivision(item.value);
                    return;
                }
                if (item.type === 'jela') {
                    this.section = 'district';
                    this.selectedDivision = item.meta.split(' · ').pop() || this.selectedDivision;
                    this.selectedDistrict = item.value;
                    this.selectedPouroshova = '';
                }
                if (item.type === 'pouroshova') {
                    this.section = 'pouroshova';
                    this.selectedPouroshova = item.value;
                }
                if (item.type === 'shakha' && item.shakha?.division) {
                    this.selectedDivision = item.shakha.division;
                }
                this.map.setView([item.lat, item.lng], item.type === 'bivag' ? 8 : 11);
                await this.applyFilters(false);
                if (item.type === 'shakha' && item.shakha) this.openShakhaDrawer(item.shakha);
            },
            norm(value) {
                return String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
            },
            esc(value) {
                return String(value || '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
            },
        };
    }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.14.9/dist/cdn.min.js"></script>
    @livewireScripts
</body>
</html>
