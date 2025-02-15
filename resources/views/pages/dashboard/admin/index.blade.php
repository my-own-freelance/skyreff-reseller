@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>

    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-4 col-lg-4">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <h2 class="mt-2"><b class="text2_primary" style="font-weight: 900;">ESTIMASI BULANAN</b></h2>
                    <h1><i class="fas fa-money-bill-wave" style="font-size: 300%;"></i></h1>
                    <h4><b style="font-size:150%;" id="w3_balance">{{ $data['trx_total_amount'] }}</b></h4>
                    <div class="text-muted mb-3">Pendapatan Bruto</div>
                    <div class="separator-dashed"></div>
                    <div class="row">
                        <div class="col-md-6" style="border-right: 2px solid #dedede">
                            <h4><b style="font-size:150%;"></b>{{ $data['trx_transfer'] }}</h4>
                            Transfer
                        </div>
                        <div class="col-md-6">
                            <h4><b style="font-size:150%;"></b>{{ $data['trx_debt'] }}</h4>
                            Hutang
                        </div>
                    </div>
                    <div class="separator-dashed"></div>
                    <a class="btn btn-primary text-white btn-block" href="{{ route('trx-product') }}"> Traksaksi Produk</a>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body skew-shadow">
                            <h1 class="mt-4">{{ $data['trx_profit'] }}</h1>
                            <h3 class="mt-3">Total Profit</h3>
                            <div class="pull-right mt-4"><small>Estimasi Profit Bulan Ini</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body bubble-shadow">
                            <h1 class="mt-4">{{ $data['trx_commission'] }}</h1>
                            <h3 class="mt-3">Sharing Komisi</h3>
                            <div class="pull-right mt-4"><small>Estimasi Komisi Bulan Ini</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body skew-shadow">
                            <h1 class="mt-4">{{ $data['req_wd'] }}</h1>
                            <h3 class="mt-3">Pengajuan Withdraw</h3>
                            <div class="pull-right"><a class="text-white" href="{{ route('trx-commission') }}">
                                    <small class="fw-bold op-9">Bayar Sekarang<i
                                            class="fas fa-external-link-alt ml-2"></i></small>
                                </a></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body bubble-shadow">
                            <div class="row">
                                <div class="col-6" style="border-right: 2px solid #dedede">
                                    <h1 class="mt-4" style="font-size: 16px">{{ $data['debt_all_reseller'] }}</h1>
                                    <h3 class="mt-3" style="font-size: 14px">Total Hutang</h3>
                                </div>
                                <div class="col-6" style="text-align: right !important">
                                    <h1 class="mt-4" style="font-size: 16px">{{ $data['commission_all_reseller'] }}</h1>
                                    <h3 class="mt-3" style="font-size: 14px">Total Komisi</h3>
                                </div>
                            </div>
                            <div class="text-center mt-4"><small>Estimasi Reseller</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('product') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md background_primary mr-3"><i class="fas fa-cubes"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_product'] }}</b></h5>
                            <small class="text-muted">Total Produk</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('trx-product') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-success mr-3"><i class="fas fa-shopping-cart"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_trx'] }}</b></h5>
                            <small class="text-muted">Transaksi Bulanan</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('reseller') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-info mr-3"><i class="fas fa-user-tag"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_reseller'] }}</b></h5>
                            <small class="text-muted">Total Reseller</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('trx-compensation') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-danger mr-3"><i class="fas fa-clipboard-list"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_compensation'] }}</b></h5>
                            <small class="text-muted">Pengajuan Komplain</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                        <h4 class="title">
                            <b>
                                <i class="fas fa-chart-line mr-2"></i>
                                Statik Omset Bulanan
                            </b>
                        </h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                        <canvas id="statisticsChart"></canvas>
                    </div>
                    <div id="myChartLegend"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/chart.js/chart.min.js') }}"></script>
    <script>
        $(function() {
            getStatisticChart()
        })

        function getStatisticChart() {
            $.ajax({
                url: "{{ route('statistic-chart') }}",
                method: "GET",
                dataType: "json",
                success: function(res) {
                    console.log("res :", res);
                    renderChart(res.data)
                },
                error: function(err) {
                    console.log("error :", err);
                    renderChart(err.data)
                }
            })
        }

        function renderChart(data) {
            console.log("data :", data)
            //Chart
            var ctx = document.getElementById('statisticsChart').getContext('2d');

            var statisticsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: data
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        bodySpacing: 4,
                        mode: "nearest",
                        intersect: 0,
                        position: "nearest",
                        xPadding: 10,
                        yPadding: 10,
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                const value = convertToRupiah(tooltipItem.yLabel)

                                return datasetLabel + ': ' + value;
                            }
                        }
                    },
                    layout: {
                        padding: {
                            left: 5,
                            right: 5,
                            top: 15,
                            bottom: 15
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontStyle: "500",
                                beginAtZero: false,
                                maxTicksLimit: 5,
                                padding: 10,
                                callback: function(value) {
                                    return convertToRupiah(value)
                                }
                            },
                            gridLines: {
                                drawTicks: false,
                                display: false
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 10,
                                fontStyle: "500"
                            }
                        }]
                    },
                    legendCallback: function(chart) {
                        var text = [];
                        text.push('<ul class="' + chart.id + '-legend html-legend">');
                        for (var i = 0; i < chart.data.datasets.length; i++) {
                            text.push('<li><span style="background-color:' + chart.data.datasets[i]
                                .legendColor +
                                '"></span>');
                            if (chart.data.datasets[i].label) {
                                text.push(chart.data.datasets[i].label);
                            }
                            text.push('</li>');
                        }
                        text.push('</ul>');
                        return text.join('');
                    }
                }
            });

            var myLegendContainer = document.getElementById("myChartLegend");

            // generate HTML legend
            myLegendContainer.innerHTML = statisticsChart.generateLegend();

            // bind onClick event to all LI-tags of the legend
            var legendItems = myLegendContainer.getElementsByTagName('li');
            for (var i = 0; i < legendItems.length; i += 1) {
                legendItems[i].addEventListener("click", legendClickCallback, false);
            }
        }
    </script>
@endpush
