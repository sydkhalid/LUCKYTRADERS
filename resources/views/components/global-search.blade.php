<div
    class="relative w-full max-w-xl"
    data-global-search
    data-search-url="{{ route('global-search.search') }}"
>
    <input
        type="search"
        autocomplete="off"
        data-global-search-input
        placeholder="Search invoices, products, parties..."
        class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 pl-10 text-sm font-medium text-slate-900 shadow-sm focus:border-cyan-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-600/15"
    >
    <span class="pointer-events-none absolute left-3.5 top-1/2 h-2.5 w-2.5 -translate-y-1/2 rounded-full border-2 border-slate-400"></span>
    <span class="pointer-events-none absolute left-[25px] top-[27px] h-2 w-0.5 -rotate-45 rounded-full bg-slate-400"></span>

    <div
        data-global-search-panel
        class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-96 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-300/30"
    ></div>
</div>
