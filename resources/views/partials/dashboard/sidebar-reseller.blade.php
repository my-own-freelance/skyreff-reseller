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
                    <a href="{{ route('dashboard.reseller') }}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Reseller</h4>
                </li>

                {{-- <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Properti</h4>
                </li>
                @if ($user->role == 'owner')
                    <li class="nav-item ml-3 {{ $routename == 'property-transaction' ? 'active' : '' }}">
                        <a href="{{ route('property-transaction') }}">
                            <i class="fas fa-exchange-alt"></i>
                            <p>Tipe Transaksi</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'property-type' ? 'active' : '' }}">
                        <a href="{{ route('property-type') }}">
                            <i class="fas fa-building"></i>
                            <p>Tipe Properti</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'property-certificate' ? 'active' : '' }}">
                        <a href="{{ route('property-certificate') }}">
                            <i class="fas fa-file-contract"></i>
                            <p>Tipe Sertifikat</p>
                        </a>
                    </li>
                @endif
                <li class="nav-item ml-3 {{ $routename == 'property' ? 'active' : '' }}">
                    <a href="{{ route('property') }}">
                        <i class="fas fa-home"></i>
                        <p>Properti</p>
                    </a>
                </li>
                @if ($user->role == 'owner')
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Master</h4>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'article' ? 'active' : '' }}">
                        <a href="{{ route('article') }}">
                            <i class="fas fa-newspaper"></i>
                            <p>Artikel</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'faq' ? 'active' : '' }}">
                        <a href="{{ route('faq') }}">
                            <i class="fas fa-life-ring"></i>
                            <p>FAQ</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'reason-to-choose-us' ? 'active' : '' }}">
                        <a href="{{ route('reason-to-choose-us') }}">
                            <i class="fas fa-list"></i>
                            <p>Daftar Alasan</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'review' ? 'active' : '' }}">
                        <a href="{{ route('review') }}">
                            <i class="fas fa-comment-dots"></i>
                            <p>Review</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'partnership' ? 'active' : '' }}">
                        <a href="{{ route('partnership') }}">
                            <i class="fas fa-hand-holding-heart"></i>
                            <p>Partnership</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'contact' ? 'active' : '' }}">
                        <a href="{{ route('contact') }}">
                            <i class="fas fa-envelope"></i>
                            <p>Pesan Masuk</p>
                        </a>
                    </li>
                @endif
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Management</h4>
                </li>
                <li class="nav-item ml-3">
                    <a href="{{ route('account') }}">
                        <i class="fas fa-user-cog"></i>
                        <p>Setting Account</p>
                    </a>
                </li>
                @if ($user->role == 'owner')
                    <li class="nav-item ml-3 {{ $routename == 'setting' ? 'active' : '' }}">
                        <a href="{{ route('setting') }}">
                            <i class="fas fa-cogs"></i>
                            <p>Setting Web</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'agen' ? 'active' : '' }}">
                        <a href="{{ route('agen') }}">
                            <i class="fas fa-users"></i>
                            <p>Agen</p>
                        </a>
                    </li>
                    <li class="nav-item ml-3 {{ $routename == 'owner' ? 'active' : '' }}">
                        <a href="{{ route('owner') }}">
                            <i class="fas fa-users-cog"></i>
                            <p>Owner</p>
                        </a>
                    </li>
                @endif
                <li class="nav-item ml-3">
                    <a href="{{ route('home') }}" target="__blank">
                        <i class="fas fa-arrow-left"></i>
                        <p>Website Page</p>
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
                </li> --}}
            </ul>
        </div>
    </div>
</div>
<!-- End Sidebar -->
