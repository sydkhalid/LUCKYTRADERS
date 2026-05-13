<div
    class="lt-header-search"
    data-global-search
    data-search-url="{{ route('global-search.search') }}"
>
    <div class="lt-search-inline" data-global-search-box aria-hidden="true">
        <div class="relative">
            <input
                type="search"
                autocomplete="off"
                tabindex="-1"
                data-global-search-input
                placeholder="Search invoices, products, parties..."
                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 pl-10 text-sm font-semibold text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-cyan-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-cyan-600/10"
            >
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.44 9.79l2.63 2.64a.75.75 0 1 0 1.06-1.06l-2.64-2.63A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
            </svg>
        </div>

        <div
            data-global-search-panel
            class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-96 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-300/30"
        ></div>
    </div>

    <button
        type="button"
        class="lt-header-control"
        data-global-search-toggle
        aria-label="Open search"
        aria-expanded="false"
    >
        <svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true">
            <path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
    </button>
</div>
