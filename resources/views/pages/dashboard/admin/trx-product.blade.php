@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('/dashboard/css/toggle-status.css') }}">
    <style>
        #proofImg {
            max-width: 100%;
            max-height: 400px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }

        .wrap-text {
            max-width: 500px;
            word-wrap: break-word;
            white-space: normal;
        }
    </style>
@endpush
@section('content')
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
        <div>
            <h2 class="pb-2 fw-bold">{{ $title }}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card card-dark card-primary">
                <div class="card-body bubble-shadow">
                    <h1 id="w1_totalEstimated" style="font-size: 32px" class="mt-5">Rp. 0</h1>
                    <h5></h5>
                    <div class="pull-right mt-5">
                        <h3 class="fw-bold">Total Estimasi Pendapatan</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-sm-6 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-chart-pie text-primary"></i>
                                    </div>
                                </div>
                                <div class="col-9 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Estimasi Pembayaran Transfer</p>
                                        <h4 class="card-title" id="w1_estTransfer">Rp. 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-chart-pie text-danger"></i>
                                    </div>
                                </div>
                                <div class="col-9 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Estimasi Pembayaran Hutang</p>
                                        <h4 class="card-title" id="w1_estDebt">Rp. 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-chart-pie text-secondary"></i>
                                    </div>
                                </div>
                                <div class="col-9 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Estimasi Pembayaran Saldo</p>
                                        <h4 class="card-title" id="w1_estBalance">Rp. 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-chart-pie text-success"></i>
                                    </div>
                                </div>
                                <div class="col-9 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Estimasi Profit</p>
                                        <h4 class="card-title" id="w1_estProfit">Rp. 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-secondary mr-3">
                        <i class="fa fa-receipt"></i>
                    </span>
                    <div class="card_content_wrapper truncate">
                        <h5 class="mb-1"><b id="w1_totalTrx">0</b></h5>
                        <small class="text-muted" title="Total Transaksi">Total Trx</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-success mr-3">
                        <i class="fa fa-check-circle"></i>
                    </span>
                    <div class="card_content_wrapper truncate">
                        <h5 class="mb-1"><b id="w1_trxSuccess">0</b></h5>
                        <small class="text-muted" title="Transaksi Sukses">Trx Success</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-info mr-3">
                        <i class="fa fa-question-circle"></i>
                    </span>
                    <div class="card_content_wrapper truncate">
                        <h5 class="mb-1"><b id="w1_trxPending">0</b></h5>
                        <small class="text-muted" title="Transaksi Pending">Trx Pending</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-primary mr-3">
                        <i class="fa fa-clock"></i>
                    </span>
                    <div class="card_content_wrapper truncate">
                        <h5 class="mb-1"><b id="w1_TrxProcess">0</b></h5>
                        <small class="text-muted" title="Transaksi Di Proses">Trx Process</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-warning mr-3">
                        <i class="fa fa-ban"></i>
                    </span>
                    <div class="card_content_wrapper truncate">
                        <h5 class="mb-1"><b id="w1_trxCancel">0</b></h5>
                        <small class="text-muted" title="Transaksi Cancel">Trx Cancel</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-danger mr-3">
                        <i class="fa fa-times-circle"></i>
                    </span>
                    <div class="card_content_wrapper truncate">
                        <h5 class="mb-1"><b id="w1_trxReject">0</b></h5>
                        <small class="text-muted" title="Transaksi Reject">Trx Reject</small>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">List Transaksi Produk</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                    </div>
                    <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tanggal Mulai</label>
                                    <input class="form-control date-picker" id="dateFrom" type="text"
                                        placeholder="Pilih tanggal awal" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tanggal Akhir</label>
                                    <input class="form-control date-picker" id="dateTo" type="text"
                                        placeholder="Pilih tanggal akhir" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fProductCategoryId">Filter Kategori</label>
                                    <select class="form-control" id="fProductCategoryId" name="fProductCategoryId">
                                        <option value="">All</option>
                                        @foreach ($categories as $category)
                                            <option value = "{{ $category->id }}">{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fPaymentType">Filter Pembayaran</label>
                                    <select class="form-control" id="fPaymentType" name="fPaymentType">
                                        <option value="">All</option>
                                        <option value="BALANCE">Saldo</option>
                                        <option value="TRANSFER">Transfer</option>
                                        <option value="DEBT">Pihutang</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fStatus">Filter Status</label>
                                    <select class="form-control" id="fStatus" name="fStatus">
                                        <option value="">All</option>
                                        <option value="PENDING">Pending</option>
                                        <option value="PROCESS">Process</option>
                                        <option value="SUCCESS">Success</option>
                                        <option value="REJECT">Reject</option>
                                        <option value="CANCEL">Cancel</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="pt-3">
                                    <button class="mt-4 btn btn-sm btn-success mr-3" type="submit">Submit</button>
                                    <button class="mt-4 btn btn-sm btn-primary mr-3" type="button"
                                        onclick="exportData()">Export</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="trxProductDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Code Trx</th>
                                    <th class="all">Status</th>
                                    <th class="all">Reseller</th>
                                    <th class="all">Produk</th>
                                    <th class="all">Harga</th>
                                    <th class="all">Qty</th>
                                    <th class="all">Total</th>
                                    <th class="">Komisi</th>
                                    <th class="">Profit</th>
                                    <th class="">Tipe Bayar</th>
                                    <th class="">Catatan</th>
                                    <th class="">Tanggal Trx</th>
                                    <th class="">Tanggal Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="14" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                            <tfoot style="border-top: 1px solid #dedede;">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REJECT WITH REASON --}}
    <div class="modal fade" id="modalReject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="formReject">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Tolak Pesanan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <input type="hidden" name="trxId" id="trxId">
                        <input type="hidden" name="reqStatus" id="reqStatus">
                        <input type="hidden" name="paymentType" id="paymentType">
                        <div class="form-group">
                            <label for="reason">Alasan Ditolak</label>
                            <textarea class="form-control" name="reason" id="reason" cols="30" rows="5" required></textarea>
                        </div>
                        <div class="form-group" id="divProofReturn" style="display: none;">
                            <label for=proofReturn">Bukti Pengembalian Saldo</label>
                            <input class="form-control" id="proofReturn" type="file" name="proofReturn"
                                placeholder="upload gambar" />
                            <small class="text-danger">Max ukuran 2MB</small>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="reasonReject"></p>
                    <p id="bankTarget"></p>
                    <img alt="" id="proofImg">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('/dashboard/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('/dashboard/js/plugin/datepicker/bootstrap-datetimepicker.min.js') }}"></script>

    <script>
        let dTable = null;

        $(function() {
            $('#dateFrom').datetimepicker({
                format: 'DD/MM/YYYY',
            });
            $('#dateTo').datetimepicker({
                format: 'DD/MM/YYYY',
            });
            $("#dateFrom").val(moment().startOf('month').format("DD/MM/YYYY"))
            $("#dateTo").val(moment().endOf('month').format("DD/MM/YYYY"))
            dataTable();
        })

        function dataTable(filter) {
            let url = "{{ route('trx-product.datatable') }}";
            if (filter) url += "?" + filter;

            dTable = $("#trxProductDataTable").DataTable({
                searching: true,
                orderng: true,
                lengthChange: true,
                responsive: true,
                processing: true,
                serverSide: true,
                searchDelay: 1000,
                paging: true,
                lengthMenu: [5, 10, 25, 50, 100],
                ajax: url,
                columns: [{
                    data: "action"
                }, {
                    data: "code"
                }, {
                    data: "status"
                }, {
                    data: "reseller"
                }, {
                    data: "product"
                }, {
                    data: "amount",
                    render: function(data, type, row) {
                        return convertToRupiah(data);
                    }
                }, {
                    data: "qty"
                }, {
                    data: "total_amount",
                    render: function(data, type, row) {
                        return `<span class="text-success">+ ${convertToRupiah(data)}</span>`;
                    }
                }, {
                    data: "commission",
                    render: function(data, type, row) {
                        return `<span class="text-danger">- ${convertToRupiah(data)}</span>`;
                    }
                }, {
                    data: "profit",
                    render: function(data, type, row) {
                        return `<span class="text-success">+ ${convertToRupiah(data)}</span>`;
                    }
                }, {
                    data: "payment_type"
                }, {
                    data: "notes",
                    "render": function(data, type, row, meta) {
                        if (type === 'display') {
                            return `<div class="wrap-text">${data || ""}</div>`;
                        }
                        return data;
                    }
                }, {
                    data: "created"
                }, {
                    data: "updated"
                }],
                pageLength: 25,
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api(),
                        data;
                    $.ajax({
                        url: url,
                        data: {
                            search: {
                                value: api.search()
                            }
                        },
                        success: function(msg) {
                            let d = msg.data
                            // for statistik bar
                            let totEstimated = 0,
                                totEstTransfer = 0,
                                totEstDebt = 0,
                                totEstProfit = 0,
                                totEstBalance = 0,
                                totTrx = 0,
                                totTrxSuccess = 0,
                                totTrxPending = 0,
                                totTrxProcess = 0,
                                totTrxCancel = 0,
                                totTrxReject = 0;

                            // for table footer
                            let amount = 0,
                                qty = 0,
                                total_amount = 0,
                                commission = 0,
                                profit = 0;


                            d.map((r) => {
                                // update value statistik bar
                                if (r.trx_status == "SUCCESS") {
                                    totTrxSuccess += 1;
                                    totEstimated += r.total_amount;
                                    totEstProfit += r.profit;
                                    if (r.trx_payment == "TRANSFER") {
                                        totEstTransfer += r.total_amount;
                                    } else if (r.trx_payment == "DEBT") {
                                        totEstDebt += r.total_amount;
                                    } else {
                                        console.log("SINI")
                                        totEstBalance += r.total_amount;
                                    }
                                }

                                totTrx += 1;
                                r.trx_status == "PENDING" ? totTrxPending += 1 : '';
                                r.trx_status == "PROCESS" ? totTrxProcess += 1 : '';
                                r.trx_status == "CANCEL" ? totTrxCancel += 1 : '';
                                r.trx_status == "REJECT" ? totTrxReject += 1 : '';

                                // update value table footer
                                amount += r.amount;
                                qty += r.qty;
                                total_amount += r.total_amount;
                                commission += r.commission;
                                profit += r.profit;
                            });

                            // implement value statistik bar
                            $("#w1_totalEstimated").html(convertToRupiah(totEstimated));
                            $("#w1_estTransfer").html(convertToRupiah(totEstTransfer));
                            $("#w1_estDebt").html(convertToRupiah(totEstDebt));
                            $("#w1_estProfit").html(convertToRupiah(totEstProfit));
                            $("#w1_estBalance").html(convertToRupiah(totEstBalance));
                            $("#w1_totalTrx").html(totTrx);
                            $("#w1_trxSuccess").html(totTrxSuccess);
                            $("#w1_trxPending").html(totTrxPending);
                            $("#w1_TrxProcess").html(totTrxProcess);
                            $("#w1_trxCancel").html(totTrxCancel);
                            $("#w1_trxReject").html(totTrxReject);

                            // implement value footer table
                            $(api.column(4).footer()).html('TOTAL');
                            $(api.column(5).footer()).html(convertToRupiah(amount));
                            $(api.column(6).footer()).html(`${qty}`);
                            $(api.column(7).footer()).html(
                                `<span class="text-success">+ ${convertToRupiah(total_amount)}</span>`
                            );
                            $(api.column(8).footer()).html(
                                `<span class="text-danger">- ${convertToRupiah(commission)}</span>`
                            );
                            $(api.column(9).footer()).html(
                                `<span class="text-success">+ ${convertToRupiah(profit)}</span>`
                            );
                        }
                    })
                },
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
        }


        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                product_category_id: $("#fProductCategoryId").val(),
                payment_type: $("#fPaymentType").val(),
                status: $("#fStatus").val(),
                tgl_awal: $("#dateFrom").val(),
                tgl_akhir: $("#dateTo").val()
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })

        function getData(id, status) {
            $.ajax({
                url: "{{ route('trx-product.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    let data = res.data;
                    loadModelDetail(data, status);
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function loadModelDetail(data, status) {
            const modal = $("#modalDetail");
            modal.modal('show');
            modal.off('shown.bs.modal').on('shown.bs.modal', function() {
                if (status == "SHOW-REASON-REJECT") {
                    $("#modalDetailTitle").html("ALASAN TRANSAKSI DITOLAK")
                    $("#reasonReject").html(data.reason);
                }

                if (status == "SHOW-PROOF-PAYMENT") {
                    $("#modalDetailTitle").html("BUKTI PEMBAYARAN TRANSFER")
                    $("#bankTarget").html(`${data.payment_type} : ${data.bank_target}`);
                    if (data.proof_of_payment) {
                        $("#proofImg").attr("src", data.proof_of_payment);
                    }
                }

                if (status == "SHOW-PROOF-RETURN") {
                    $("#modalDetailTitle").html("ALASAN DITOLAK DAN BUKTI PENGEMBALIAN SALDO")
                    $("#reasonReject").html(data.reason);
                    if (data.proof_of_return) {
                        $("#proofImg").attr("src", data.proof_of_return);
                    }
                }
            });

            return false;
        }

        function changeStatus(id, status, payment_type = "") {
            if (status == "REJECT") {
                loadModalReject(id, status, payment_type);
                return false;
            } else {
                //PROCESS, SUCCESS
                let c = confirm(`Anda yakin untuk mengubah status transaksi menjadi ${status} ?`)
                if (c) {
                    let dataToSend = new FormData();
                    dataToSend.append("id", id);
                    dataToSend.append("status", status);
                    sendChangeStatus(dataToSend)
                }
                return false;
            }
        }

        function loadModalReject(id, status, payment_type) {
            const modal = $("#modalReject");
            modal.modal('show');
            modal.off('shown.bs.modal').on('shown.bs.modal', function() {
                $("#trxId").val(id);
                $("#reqStatus").val(status);
                $("#paymentType").val(payment_type);

                if (payment_type == "TRANSFER") {
                    $("#divProofReturn").fadeIn(200, function() {
                        $("#proofReturn").attr("required", true);
                    })
                }
            });

            return false;
        }

        $("#formReject").submit(function(e) {
            e.preventDefault();
            let c = confirm(`Anda yakin untuk mengubah status transaksi menjadi ${$("#reqStatus").val()} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("id", $("#trxId").val());
                dataToSend.append("status", $("#reqStatus").val());
                dataToSend.append("remark", $("#reason").val());

                if ($("#paymentType").val() == "TRANSFER") {
                    dataToSend.append("proof_of_return", document.getElementById("proofReturn").files[0]);
                }
                sendChangeStatus(dataToSend, true) // hide modal after action
            }
            return false;
        })

        $("#modalReject").on("hidden.bs.modal", function() {
            $(this).find("form")[0].reset();
            // BY DEFAULT UPLOAD BUKTI PENGEMBALIAN DI HIDDEN
            $("#divProofReturn").slideUp(200, function() {
                $("#proofReturn").attr("required", false);
            });
        });

        $("#modalDetail").on("hidden.bs.modal", function() {
            $("#reasonReject").html("");
            $("#bankTarget").html("");
            $("#proofImg").attr("src", "");
        });

        function sendChangeStatus(data, hideModal = false) {
            $.ajax({
                url: "{{ route('trx-product.change-status') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                    if (hideModal) {
                        $("#modalReject").modal('hide')
                    };
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })
        }

        function exportData() {
            let dataFilter = {
                product_category_id: $("#fProductCategoryId").val(),
                payment_type: $("#fPaymentType").val(),
                status: $("#fStatus").val(),
                tgl_awal: $("#dateFrom").val(),
                tgl_akhir: $("#dateTo").val()
            }

            window.location.href = `{{ route('export.trx-product') }}?${$.param(dataFilter)}`
        }
    </script>
@endpush
