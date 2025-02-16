@php
    $config = \App\Models\WebConfig::first();
    $webTitle = $config && $config->web_title ? $config->web_title : 'Skyreff';
    $metaImage =
        $config && $config->meta_image ? url('/') . Storage::url($config->meta_image) : asset('skyreff-logo.jpeg');
    $webLogo = $config && $config->web_logo ? url('/') . Storage::url($config->web_logo) : asset('skyreff-logo.jpeg');
    $webDescription = $config && $config->web_description ? $config->web_description : 'Situs Jual Beli Produk Digital';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="generator" content="Hugo 0.48" />
    <meta charset="utf-8">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $webTitle }}</title>
    <meta name="keywords" content="yeo">
    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('dashboard/css/bootstrap.min.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontpage/css/spectre.css') }}">
    <link rel="stylesheet" href="{{ asset('frontpage/css/spectre-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('frontpage/css/spectre-exp.css') }}">
    <link rel="stylesheet" href="{{ asset('frontpage/css/yeo.css') }}">
    <link rel="shortcut icon" href="{{ $webLogo }}" type="image/x-icon" />
    <meta property="og:title" content="{{ $webTitle }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:description" content="{{ $webDescription }}">
    <meta property="og:site_name" content="{{ $webTitle }}">
    <meta property="og:type" content="product">
    <meta property="og:image" content="{{ $metaImage }}">

    <style>
        /* Styling agar mirip Bootstrap */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.1);
        }

        thead {
            background: #007bff;
            color: white;
            text-align: center;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        tbody tr:hover {
            background: #e9ecef;
        }
    </style>
</head>

<body>
    <div class="yeo-slogan">
        <div class="container yeo-header">
            <div class="columns">
                <div class="column col-12">
                    <header class="navbar">
                        <section class="navbar-section">
                            <a class="navbar-brand logo" href="./">
                                <img class="logo-img" src="{{ $webLogo }}"
                                    alt=""><span>{{ $webTitle }}</span>
                            </a>
                        </section>
                        <section class="navbar-section hide-sm">
                            <a class="btn btn-link" href="#layanan">Layanan</a>
                            <a class="btn btn-link" href="#penawaran">Penawaran</a>
                            <a class="btn btn-link" href="#produk">Produk</a>
                            @if (auth()->check())
                                @if (auth()->user()->role == 'ADMIN')
                                    <a class="btn btn-primary btn-hire-me"
                                        href="{{ route('dashboard.admin') }}">Dashboard</a>
                                @else
                                    <a class="btn btn-primary btn-hire-me"
                                        href="{{ route('dashboard.reseller') }}">Dashboard</a>
                                @endif
                            @else
                                <a class="btn btn-primary btn-hire-me" href="{{ route('login') }}">Login / Register</a>
                            @endif
                        </section>
                    </header>
                </div>
            </div>
        </div>
        <div class="container slogan">
            <div class="columns">
                <div class="column col-7 col-sm-12">
                    <div class="slogan-content">
                        <h2>
                            <span class="slogan-bold">Solusi Digital Terbaik</span>
                            <span class="slogan-bold">Kemudahan Transaksi Online</span>
                            <span class="slogan-bold">Layanan Cepat & Terpercaya</span>
                        </h2>
                        <p>Kami menyediakan berbagai layanan digital untuk memenuhi kebutuhan Anda dengan mudah, cepat,
                            dan aman. 🚀</p>
                        @if (auth()->check())
                            @if (auth()->user()->role == 'ADMIN')
                                <a class="btn btn-primary btn-lg btn-start" target="_blank"
                                    href="{{ route('dashboard.admin') }}">Bergabung Sekarang</a>
                            @else
                                <a class="btn btn-primary btn-lg btn-start" target="_blank"
                                    href="{{ route('dashboard.reseller') }}">Bergabung Sekarang</a>
                            @endif
                        @else
                            <a class="btn btn-primary btn-lg btn-start" target="_blank"
                                href="{{ route('login') }}">Bergabung Sekarang</a>
                        @endif

                    </div>
                </div>
                <div class="column col-5 hide-sm">
                    <img class="slogan-img" src="{{ asset('frontpage/images/yeo-feature-1.svg') }}" alt="">
                </div>
            </div>
        </div>
    </div>
    <div class="yeo-do" id="layanan">
        <div class="container yeo-body">
            <div class="columns">
                <div class="column col-12">
                    <h2 class="feature-title">Layanan Kami</h2>
                </div>
                <div class="column col-4 col-sm-12">
                    <div class="yeo-do-content">
                        <h3>📱 Layanan Digital</h3>
                        <p>Kami menyediakan berbagai layanan digital seperti pulsa, paket data, dan layanan internet
                            dengan proses cepat dan mudah.</p>
                    </div>
                </div>
                <div class="column col-4 col-sm-12">
                    <div class="yeo-do-content">
                        <h3>⚡ Produk Digital Instan</h3>
                        <p>Dapatkan berbagai produk digital dengan pengiriman instan, mulai dari voucher game, token
                            listrik, hingga langganan hiburan.</p>
                    </div>
                </div>
                <div class="column col-4 col-sm-12">
                    <div class="yeo-do-content">
                        <h3>💡 Solusi Digital Terpadu</h3>
                        <p>Kami menghadirkan berbagai solusi digital untuk mendukung kebutuhan bisnis dan pribadi Anda
                            dengan layanan yang andal.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="yeo-team" id="penawaran">
        <div class="container yeo-body">
            <div class="columns">
                <div class="column col-12">
                    <h2 class="feature-title">Penawaran Menarik</h2>
                </div>
                @forelse ($banners as $banner)
                    <div class="column col-3 col-sm-12" style="margin-bottom: 20px">
                        <img class="s-circle" src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}"
                            style="width: 100% !important; height: 150px !important; object-fit: cover !important;">
                        <span class="name" style="font-size: 16px">{{ $banner->title }}</span>
                        <span class="title" style="font-size: 12px !important;">{{ $banner->excerpt }}</span>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
    <div class="yeo-open-source" id="produk">
        <div class="container yeo-body">
            <div class="columns">
                <div class="column col-12">
                    <h2 class="feature-title">Produk Kami</h2>
                </div>
                <div class="column col-12 centered col-sm-12" id="productDataTable">
                    <section class="">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th class="text-center">Gambar</th>
                                    <th class="text-center">Produk</th>
                                    <th class="text-center">Kategori</th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $index => $product)
                                    <tr>
                                        <td>{{ $products->firstItem() + $index }}</td>
                                        <td>
                                            <div class="thumbnail">
                                                <div class="thumb">
                                                    <img src="{{ $product->ProductCategory ? Storage::url($product->ProductCategory->image) : '' }}"
                                                        style="width:100px !important; height: 75px !important; object-fit: cover !important;"
                                                        alt="{{ $product->title }}">
                                                </div>
                                            </div>
                                        </td>
                                        <td width="20%">
                                            <small>
                                                <strong>Judul</strong> :
                                                {{ Str::limit(strip_tags($product->title), 100) }}
                                                <br>
                                                <strong>Code</strong> : {{ $product->code }}
                                                <br>
                                            </small>
                                        </td>
                                        <td>{{ $product->ProductCategory ? $product->ProductCategory->title : '' }}
                                        </td>
                                        <td width="20%">Rp.
                                            {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>{{ Str::limit(strip_tags($product->excerpt), 150) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">File Regulasi Kosong</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </section>
                    <div style="margin-top: 25px; float: left;">

                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="yeo-footer">
        <div class="container">
            <div class="columns">
                <div class="column col-3 col-sm-6" style="margin-left: auto">
                    <div class="yeo-footer-content">
                        <h4>Contact Us</h4>
                        <ul class="nav">
                            <li class="nav-item">
                                <a href="mailto:?body={{ $config->email ?? '' }}">Email</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $config->twitter ?? '' }}">Twitter</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $config->facebook ?? '' }}">Facebook</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ $config->youtube ?? '' }}">Youtube</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="column col-3 col-sm-6">
                    <div class="yeo-footer-content">
                        <h4>Information</h4>
                        <ul class="nav">
                            <li class="nav-item">
                                <a href="#">Telpon : {{ $config->phone_number ?? '' }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="">Alamat : {{ $config->address ?? 'Indonesia' }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </script>
</body>

</html>
