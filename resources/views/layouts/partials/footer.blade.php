<footer class="lt-footer content-footer footer bg-footer-theme px-3 py-3 px-lg-4 no-print">
    <div class="container-xxl d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <div>
            © {{ now()->year }} {{ $erpCompany['name'] ?? 'LUCKY TRADERS' }}
        </div>
        <div class="fw-semibold">
            {{ $erpBusinessType ?? 'Steel Trading ERP' }}
        </div>
    </div>
</footer>
