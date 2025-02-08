@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>
        .card_content_wrapper {
            width: 220px;
        }
    </style>
@endpush
@section('content')
    <div class="row mt--2">
        <div class="col-md-12">
            <div class="card card-primary text-white">
                <div class="card-body">
                    <h1 class="fw-bold">PAKET REGULAR</h1>
                    <h5 class="op-8">Jenis paket yang aktif untuk akun reseller anda adalah Regular</h5>
                    @if ($data['level'] == 'REGULAR')
                        <div class="pull-right mt-2">
                            <button class="btn btn-block btn-sm" onclick="upgradeAccount()">
                                <i class="fas fa-arrow-circle-up mr-1"></i>
                                Upgrade VIP
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <h2 class="mt-2"><b class="text2_primary" style="font-weight: 900;">SALDO EFEKTIF</b></h2>
                    <h1><i class="fas fa-money-bill-wave" style="font-size: 300%;"></i></h1>
                    <h4><b style="font-size:150%;" id="w3_balance">{{ $data['commission'] }}</b></h4>
                    <div class="text-muted mb-3">Penarikan Tersedia</div>
                    <div class="separator-dashed"></div>
                    <h4><b style="font-size:150%;" id="w3_wd_pending">{{ $data['wd_commission'] }}</b></h4>
                    <div class="text-muted">Penarikan Menunggu Konfirmasi
                        <a class="btn btn-icon btn-link" href="{{ route('trx-commission') }}" type="button">
                            <i class="fa fa-external-link-alt"></i>
                        </a>
                    </div>
                    <div class="separator-dashed"></div><a class="btn btn-primary text-white btn-block"
                        href="{{ route('trx-commission.request-wd') }}"> Tarik Saldo Efektif</a>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body skew-shadow">
                            <h1 class="mt-4">{{ $data['trx_product'] }}</h1>
                            <h3 class="mt-3">Total Transaksi</h3>
                            <div class="pull-right mt-4"><small>Estimasi Transaksi Bulan Ini</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body bubble-shadow">
                            <h1 class="mt-4">{{ $data['debt_limit'] }}</h1>
                            <h3 class="mt-3">Limit Pihutang</h3>
                            <div class="pull-right mt-4"><small>Total Limit Pihutang</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body skew-shadow">
                            <h1 class="mt-4">{{ $data['total_debt'] }}</h1>
                            <h3 class="mt-3">Total Hutang</h3>
                            <div class="pull-right"><a class="text-white" href="{{ route('trx-debt') }}">
                                    <small class="fw-bold op-9">Bayar Sekarang<i
                                            class="fas fa-external-link-alt ml-2"></i></small>
                                </a></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body skew-shadow">
                            <h1 class="mt-4">{{ $data['month_commission'] }}</h1>
                            <h3 class="mt-3">Komisi Bulan Ini</h3>
                            <div class="pull-right">
                                <a class="text-white" href="{{ route('mutation-commission') }}">
                                    <small class="fw-bold op-9">Cek Mutasi<i
                                            class="fas fa-external-link-alt ml-2"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-project mt-5">
        {{-- BANNER --}}
        @forelse ($data['banners'] as $banner)
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="p-2">
                        <img class="card-img-top rounded" src="{{ Storage::url($banner->image) }}"
                            style="height: 250px; object-fit: cover;" alt="Product 1">
                    </div>
                    <div class="card-body pt-2">
                        <h4 class="mb-1 fw-bold">{{ $banner->title }}</h4>
                        <p class="text-muted small mb-2">{{ $banner->excerpt }}</p>
                    </div>
                </div>
            </div>
        @empty
        @endforelse
    </div>
@endsection
@push('scripts')
    <script>
        // LOAD NOTIFIKASI DARI ADMIN
        (function loadNotification() {
            let data = {!! json_encode($data['informations']) !!};
            $.each(data, function(index, item) {
                setTimeout(() => {
                    let content = {
                        title: item.subject,
                        message: item.message,
                        icon: 'fa fa-bell',
                        // url: "/",
                    }

                    let state = "info"
                    switch (item.type) {
                        case "P":
                            state = "primary";
                            break;
                        case "I":
                            state = "info";
                            break;
                        case "S":
                            state = "success";
                            break;
                        case "W":
                            state = "warning";
                            break;
                        case "D":
                            state = "danger";
                            break;
                    }

                    $.notify(content, {
                        type: state,
                        placement: {
                            from: "top",
                            align: "right"
                        },
                        time: 10000,
                        // delay: 0,
                    });
                }, index * 2000);
            });
        })()

        function upgradeAccount() {
            let c = confirm("Anda yakin ingin melakukan upgrade akun level ke VIP ?")
            if (c) {
                $.ajax({
                    url: "{{ route('trx-upgrade.create') }}",
                    method: 'POST',
                    header: {
                        'Content-Type': 'application/json'
                    },
                    data: {
                        user_id: parseInt("{{ $data['reseller_id'] }}")
                    },
                    beforeSend: function() {
                        console.log('Loading...')
                    },
                    success: function(res) {
                        showMessage('success', 'flaticon-alarm-1', 'Sucess !', res.message)
                    },
                    error: function(err) {
                        console.log("error :", err);
                        showMessage("warning", "flaticon-error", "Peringatan", err.message || err
                            .responseJSON
                            ?.message);
                    }
                })
            }
        }
    </script>
@endpush
