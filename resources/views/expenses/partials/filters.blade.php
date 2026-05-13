<form method="GET" action="{{ $action }}" class="mb-5 rounded bg-white p-5 shadow">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">From Date</label>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">To Date</label>
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div class="flex items-end">
            <button class="w-full rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
        </div>

        <div class="flex items-end">
            <a href="{{ $action }}" class="w-full rounded border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </div>
</form>
