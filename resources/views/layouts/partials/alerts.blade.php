@if (session('success'))
    <div class="alert alert-success border-0 shadow-sm no-print" role="alert">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger border-0 shadow-sm no-print" role="alert">
        {{ session('error') }}
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning border-0 shadow-sm no-print" role="alert">
        {{ session('warning') }}
    </div>
@endif
