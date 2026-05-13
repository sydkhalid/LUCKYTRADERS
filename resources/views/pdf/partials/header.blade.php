<table class="header">
    <tr>
        <td style="width: 58%; vertical-align: top;">
            @if (! empty($company['logo_path']))
                <img src="{{ $company['logo_path'] }}" class="logo" alt="Logo">
            @endif
            <h1 class="company-name">{{ $company['name'] }}</h1>
            <p class="company-address">{{ $company['address'] }}</p>
            @if (! empty($company['phone']) || ! empty($company['email']))
                <p class="company-address">
                    @if (! empty($company['phone']))
                        Phone: {{ $company['phone'] }}
                    @endif
                    @if (! empty($company['phone']) && ! empty($company['email']))
                        |
                    @endif
                    @if (! empty($company['email']))
                        Email: {{ $company['email'] }}
                    @endif
                </p>
            @endif
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
