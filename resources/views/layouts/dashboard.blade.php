@php
    $config = \App\Models\WebConfig::first();
    $logoColor = $config && $config->logo_header_color ? $config->logo_header_color : 'blue';
    $topbarColor = $config && $config->topbar_color ? $config->topbar_color : 'blue2';
    $sidebarColor = $config && $config->sidebar_color ? $config->sidebar_color : 'white';
    $bgColor = $config && $config->bd_color ? $config->bd_color : 'bg1';
    $webTitle = $config && $config->web_title ? $config->web_title : 'Skyreff';
    $webLogo = $config && $config->web_logo ? url('/') . Storage::url($config->web_logo) : asset('skyreff-logo.jpeg');
    $webDesciption = $config && $config->web_description ? $config->web_description : '';
    $user = Auth::user();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <link rel="icon" href="{{ $webLogo }}" type="image/x-icon" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="description" content="{{ $webDesciption }}" />
    @include('partials.dashboard.styles')
    <title>@yield('title')</title>
    @stack('styles')
</head>

<body data-background-color="{{ $bgColor }}">
    <div class="wrapper">
        <div class="main-header">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="{{ $logoColor }}">
                <a href="{{ $user->role == 'ADMIN' ? route('dashboard.admin') : route('dashboard.reseller') }}"
                    class="logo">
                    <h4 class="text-white mt-3" style="font-weight: 800!important">SKYREFF RESELLER</h4>
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="icon-menu"></i>
                    </span>
                </button>
                <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="icon-menu"></i>
                    </button>
                </div>
            </div>
            <!-- End Logo Header -->

            {{-- navbar header --}}
            @include('partials.dashboard.navbar')
            {{-- end navbar header --}}
        </div>

        @if ($user->role == 'ADMIN')
            @include('partials.dashboard.sidebar-admin')
        @else
            @include('partials.dashboard.sidebar-reseller')
        @endif

        <div class="main-panel">
            <div class="container">
                <div class="page-inner mt-5">
                    @yield('content')
                </div>
            </div>
            @include('partials.dashboard.footer')
        </div>
        @if ($user->role == 'ADMIN')
            @include('partials.dashboard.custom-template')
        @endif
    </div>
    @include('partials.dashboard.scripts')
    @stack('scripts')
    <script>
        $(function() {
            getSessionStatik();
        });

        function convertToRupiah(angka) {
            var rupiah = '';
            var angkarev = angka.toString().split('').reverse().join('');
            for (var i = 0; i < angkarev.length; i++)
                if (i % 3 == 0) rupiah += angkarev.substr(i, 3) + '.';
            return 'Rp. ' + rupiah.split('', rupiah.length - 1).reverse().join('');
        }

        function getSessionStatik() {
            $.ajax({
                url: "{{ route('statik-session') }}",
                header: {
                    'Content-Type': 'application/json'
                },
                success: function(resp) {
                    let data = resp.data;
                    $("#w1_balanceReseller").html(convertToRupiah(data.balance));
                },
                error: function(err) {
                    console.log("error get static session :", err)
                }
            })
        };
    </script>
</body>

</html>
