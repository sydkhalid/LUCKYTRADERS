<div
    class="relative w-full max-w-xl"
    data-global-search
    data-search-url="{{ route('global-search.search') }}"
>
    <input
        type="search"
        autocomplete="off"
        data-global-search-input
        placeholder="Search ERP"
        class="w-full rounded border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-500"
    >

    <div
        data-global-search-panel
        class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-96 overflow-y-auto rounded border border-slate-200 bg-white shadow-lg"
    ></div>
</div>
