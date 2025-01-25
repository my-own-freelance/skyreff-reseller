<!-- Sidebar -->
@php
    $routename = request()->route()->getName();
    $user = Auth()->user();
@endphp
<div class="sidebar sidebar-style-2" data-background-color="{{ $sidebarColor }}">
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-primary">
                <li class="nav-item ml-3 {{ $routename == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin') }}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">MASTER</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'bank' ? 'active' : '' }}">
                    <a href="{{ route('bank') }}">
                        <i class="fas fa-building"></i>
                        <p>Bank</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'banner' ? 'active' : '' }}">
                    <a href="{{ route('banner') }}">
                        <i class="fas fa-image"></i>
                        <p>Banner</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'information' ? 'active' : '' }}">
                    <a href="{{ route('information') }}">
                        <i class="fas fa-info-circle"></i>
                        <p>Information</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'product-category' ? 'active' : '' }}">
                    <a href="{{ route('product-category') }}">
                        <i class="fas fa-tags"></i>
                        <p>Kategori Produk</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'product' ? 'active' : '' }}">
                    <a href="{{ route('product') }}">
                        <i class="fas fa-cubes"></i>
                        <p>Produk</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'reward' ? 'active' : '' }}">
                    <a href="{{ route('reward') }}">
                        <i class="fas fa-trophy"></i>
                        <p>Reward</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">MANAGE</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'reseller' ? 'active' : '' }}">
                    <a href="{{ route('reseller') }}">
                        <i class="fas fa-user-tag"></i>
                        <p>Reseller</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Logout</h4>
                </li>
                <li class="nav-item ml-3">
                    <a href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- End Sidebar -->
