<?php
    $globalSearchItems = [
        ['label' => 'Dashboard', 'description' => 'Overview and operational summary', 'url' => route('dashboard'), 'keywords' => 'home overview summary'],
    ];
    if (auth()->user()?->can('map.view')) {
        $globalSearchItems[] = ['label' => 'Map', 'description' => 'Bangladesh map of bivag, jela, upazila and shakhas', 'url' => route('map.index'), 'keywords' => 'map bangladesh division district upazila jela bivag shakha'];
    }
    if (auth()->user()?->canAny(['organogram.view', 'organogram.manage'])) {
        $globalSearchItems[] = ['label' => 'Organogram', 'description' => 'Organization structure and employees', 'url' => route('organogram'), 'keywords' => 'organization employee hierarchy'];
    }
    if (auth()->user()?->can('annual_audit.manage')) {
        $globalSearchItems[] = ['label' => 'Annual Audit Plan', 'description' => 'Yearly audit and monitoring schedules', 'url' => route('annual-audit.index'), 'keywords' => 'year plan schedule monitoring'];
    }
    if (auth()->user()?->canAny(['monthly_visits.manage', 'monthly_visits.execute'])) {
        $globalSearchItems[] = ['label' => 'Monthly Visits', 'description' => 'Visit assignments and execution', 'url' => route('monthly-visits.index'), 'keywords' => 'monthly visit assignment'];
    }
    if (auth()->user()?->can('projects.manage')) {
        $globalSearchItems[] = ['label' => 'Projects', 'description' => 'Project audit and monitoring', 'url' => route('projects.index'), 'keywords' => 'project audit monitoring'];
    }
    if (auth()->user()?->can('kpis.manage')) {
        $globalSearchItems[] = ['label' => 'Key Performance Indicator (KPI)', 'description' => 'Annual Shakha performance data', 'url' => route('kpis.index'), 'keywords' => 'kpi performance indicator annual'];
    }
    if (auth()->user()?->canAny(['audits.create', 'audits.manage'])) {
        $globalSearchItems[] = ['label' => 'Audit Reports', 'description' => 'Create and manage audit reports', 'url' => route('audits.index'), 'keywords' => 'report audit gmail send'];
        $globalSearchItems[] = ['label' => 'Checklists', 'description' => 'Audit checklist formats', 'url' => route('checklists.index'), 'keywords' => 'check list format'];
    }
    if (auth()->user()?->can('findings.view_all')) {
        $globalSearchItems[] = ['label' => 'Findings Matrix', 'description' => 'Indicators, findings and summary', 'url' => route('audit-findings.index'), 'keywords' => 'finding matrix indicator summary'];
    }
    if (auth()->user()?->canAny(['shakhas.manage', 'shakhas.view_all'])) {
        $globalSearchItems[] = ['label' => 'All Shakha', 'description' => 'Browse and manage Shakhas', 'url' => route('shakhas.index'), 'keywords' => 'branch shakha risk'];
        $globalSearchItems[] = ['label' => 'Shakha Employees', 'description' => 'Branch employee roster', 'url' => route('shakha-employees.index'), 'keywords' => 'staff employee picture email'];
    }
    if (auth()->user()?->can('areas.manage')) {
        $globalSearchItems[] = ['label' => 'All Areas', 'description' => 'Browse and manage Areas', 'url' => route('areas.index'), 'keywords' => 'area division'];
    }
    if (auth()->user()?->can('users.manage') || auth()->user()?->isSuperAdmin()) {
        $globalSearchItems[] = ['label' => 'Users & Access', 'description' => 'Manage user accounts', 'url' => route('users.index'), 'keywords' => 'user account access permission'];
        $globalSearchItems[] = ['label' => 'Roles', 'description' => 'Manage roles and permissions', 'url' => route('roles.index'), 'keywords' => 'role permission access'];
    }
    $globalSearchItems[] = ['label' => 'Profile', 'description' => 'Account and mail settings', 'url' => route('profile.edit'), 'keywords' => 'settings account password email'];
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($title ?? config('app.name', 'Bynnas Audit')); ?></title>
        <link rel="icon" type="image/png" href="<?php echo e(asset('images/bynnas-logo.png')); ?>?v=3">
        <script>
            try {
                const savedTheme = localStorage.getItem('bynnasTheme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', savedTheme === 'dark' || (!savedTheme && prefersDark));
            } catch (e) {}
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
        <body
            class="font-sans text-[13px] font-normal leading-relaxed antialiased text-slate-700"
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                searchOpen: false,
                darkMode: document.documentElement.classList.contains('dark'),
                searchQuery: '',
                searchActive: 0,
                searchItems: <?php echo \Illuminate\Support\Js::from($globalSearchItems)->toHtml() ?>,
                get filteredSearchItems() {
                    const words = this.searchQuery.toLowerCase().trim().split(/\s+/).filter(Boolean);
                    if (!words.length) return this.searchItems;
                    return this.searchItems.filter((item) => {
                        const haystack = `${item.label} ${item.description} ${item.keywords}`.toLowerCase();
                        return words.every((word) => haystack.includes(word));
                    });
                },
                init() {
                    try {
                        this.sidebarCollapsed = localStorage.getItem('bynnasSidebarCollapsedV2') === '1';
                    } catch (e) {
                        this.sidebarCollapsed = false;
                    }
                },
                toggleSidebarCollapsed() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    try {
                        localStorage.setItem('bynnasSidebarCollapsedV2', this.sidebarCollapsed ? '1' : '0');
                    } catch (e) {}
                },
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    document.documentElement.classList.toggle('dark', this.darkMode);
                    try {
                        localStorage.setItem('bynnasTheme', this.darkMode ? 'dark' : 'light');
                    } catch (e) {}
                },
                openSearch() {
                    this.searchOpen = true;
                    this.searchQuery = '';
                    this.searchActive = 0;
                    this.$nextTick(() => this.$refs.globalSearchInput?.focus());
                },
                closeSearch() {
                    this.searchOpen = false;
                    this.searchQuery = '';
                    this.searchActive = 0;
                },
                moveSearch(step) {
                    const count = this.filteredSearchItems.length;
                    if (!count) return;
                    this.searchActive = (this.searchActive + step + count) % count;
                    this.$nextTick(() => this.$el.querySelector(`[data-search-index="${this.searchActive}"]`)?.scrollIntoView({ block: 'nearest' }));
                },
                selectSearchResult() {
                    const item = this.filteredSearchItems[this.searchActive];
                    if (item) window.location.href = item.url;
                },
            }"
            @keydown.window.prevent.ctrl.k="openSearch()"
            @keydown.window.prevent.meta.k="openSearch()"
            @keydown.window.escape="closeSearch(); sidebarOpen = false"
        >
        <?php if (isset($component)) { $__componentOriginalc5ad7eb21ddba49addb80e6944297ba0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-loader','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-loader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0)): ?>
<?php $attributes = $__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0; ?>
<?php unset($__attributesOriginalc5ad7eb21ddba49addb80e6944297ba0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc5ad7eb21ddba49addb80e6944297ba0)): ?>
<?php $component = $__componentOriginalc5ad7eb21ddba49addb80e6944297ba0; ?>
<?php unset($__componentOriginalc5ad7eb21ddba49addb80e6944297ba0); ?>
<?php endif; ?>
        <div class="flex h-screen overflow-hidden bg-canvas">
            <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="flex min-w-0 flex-1 flex-col">
                <?php echo $__env->make('layouts.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <main class="flex min-h-0 flex-1 flex-col overflow-y-auto">
                    <?php echo e($slot); ?>

                </main>
            </div>
        </div>

        <div
            x-show="searchOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-start justify-center bg-slate-900/40 px-4 pt-24"
            @click.self="closeSearch()"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl" @click.stop>
                <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                    <input
                        x-ref="globalSearchInput"
                        x-model="searchQuery"
                        @input="searchActive = 0"
                        @keydown.down.prevent="moveSearch(1)"
                        @keydown.up.prevent="moveSearch(-1)"
                        @keydown.enter.prevent="selectSearchResult()"
                        type="search"
                        placeholder="Search pages, reports, employees, settings..."
                        class="w-full border-0 p-0 text-[13px] text-slate-700 placeholder:text-slate-400 focus:ring-0"
                        autocomplete="off"
                    >
                    <button type="button" @click="closeSearch()" class="rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-medium text-slate-400">Esc</button>
                </div>
                <div class="max-h-[55vh] overflow-y-auto p-2">
                    <template x-for="(item, index) in filteredSearchItems" :key="item.url">
                        <a
                            :href="item.url"
                            :data-search-index="index"
                            @mouseenter="searchActive = index"
                            :class="searchActive === index ? 'bg-blue-50 text-blue-900 ring-1 ring-inset ring-blue-100' : 'text-slate-600 hover:bg-slate-50'"
                            class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 transition last:mb-0"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[12px] font-semibold" x-text="item.label"></span>
                                <span class="block truncate text-[10px] text-slate-400" x-text="item.description"></span>
                            </span>
                            <span x-show="searchActive === index" class="text-[9px] font-medium text-blue-400">Enter ↵</span>
                        </a>
                    </template>
                    <div x-show="filteredSearchItems.length === 0" class="px-4 py-10 text-center">
                        <p class="text-[13px] font-semibold text-slate-600">No matching page found</p>
                        <p class="mt-1 text-[11px] text-slate-400">Try another keyword.</p>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $__env->yieldPushContent('scripts'); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/layouts/app.blade.php ENDPATH**/ ?>