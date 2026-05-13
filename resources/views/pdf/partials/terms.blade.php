<div class="terms">
    <p class="bold">Terms and Conditions</p>
    @foreach (preg_split('/\r\n|\r|\n/', trim($termsAndConditions ?? '')) as $term)
        @if (trim($term) !== '')
            <p>{{ $term }}</p>
        @endif
    @endforeach

    @if (! empty($bankDetails))
        <div class="bank-details">
            <p class="bold">Bank Details</p>
            <p>{{ $bankDetails }}</p>
        </div>
    @endif
</div>
