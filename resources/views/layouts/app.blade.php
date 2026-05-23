<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $erpTheme['mode'] ?? 'light' }}" data-header-style="{{ $erpTheme['header_style'] ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP') | {{ $erpCompany['name'] ?? 'LUCKY TRADERS' }}</title>

    @php
        $erpFlash = [
            'success' => session('success'),
            'error' => session('error'),
            'warning' => session('warning'),
        ];
    @endphp

    <script>
        window.erpFlash = {{ \Illuminate\Support\Js::from($erpFlash) }};
        window.erpSettings = {{ \Illuminate\Support\Js::from([
            'company' => $erpCompany ?? [],
            'currency' => $erpCurrency ?? ['code' => 'INR', 'symbol' => '₹'],
            'defaultTax' => (float) ($erpSystemSettings->default_tax ?? 18),
            'dateFormat' => $erpSystemSettings->date_format ?? 'd M Y',
            'invoiceFooter' => $erpInvoiceSettings->terms_and_conditions ?? null,
            'theme' => $erpTheme ?? [],
        ]) }};
    </script>

    <link rel="stylesheet" href="{{ asset('theme/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/simplebar/simplebar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/node-waves/waves.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/@simonwep/pickr/themes/classic.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/autocomplete.js/css/autoComplete.02.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/datatables.net/css/dataTables.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/datatables.net-responsive/css/responsive.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/libs/datatables.net-buttons/css/buttons.dataTables.min.css') }}">

    <link rel="stylesheet" href="{{ asset('theme/assets/css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/assets/css/styles.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('theme/assets/css/sneat-lucky.css') }}">
    @stack('styles')

    <style>
        :root {
            --lt-primary: {{ $erpTheme['color'] ?? '#696cff' }};
        }
    </style>
</head>
<body class="lt-app sneat-skin layout-wrapper layout-content-navbar">
    <div class="lt-shell layout-container">
        @include('layouts.partials.sidebar')

        <div class="lt-main-wrapper layout-page d-flex min-vh-100 flex-column">
            @include('layouts.partials.header')

            <main class="lt-main erp-content content-wrapper flex-grow-1">
                <div class="lt-page-shell container-xxl flex-grow-1 container-p-y">
                    @include('layouts.partials.alerts')
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>

    <script src="{{ asset('theme/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/@simonwep/pickr/pickr.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/autocomplete.js/autoComplete.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('theme/assets/libs/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('theme/assets/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
