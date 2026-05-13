<table class="header">
    <tr>
        <td style="width: 58%; vertical-align: top;">
            <h1 class="company-name">{{ $company['name'] }}</h1>
            <p class="company-address">{{ $company['address'] }}</p>
            @if (! empty($company['gst_number']))
                <p class="company-address">GSTIN: {{ $company['gst_number'] }}</p>
            @endif
        </td>
        <td class="title-box" style="width: 42%; vertical-align: top;">
            <h2 class="title">{{ $title }}</h2>
            @isset($documentNo)
                <p class="muted">No: <span class="bold">{{ $documentNo }}</span></p>
            @endisset
            @isset($documentDate)
                <p class="muted">Date: <span class="bold">{{ $documentDate }}</span></p>
            @endisset
            <span class="badge">A4 Printable PDF</span>
        </td>
    </tr>
</table>
