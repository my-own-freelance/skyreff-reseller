@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('/dashboard/css/toggle-status.css') }}">
    <style>
        .wrap-text {
            max-width: 700px;
            word-wrap: break-word;
            white-space: normal;
        }

        #proofImg {
            max-width: 100%;
            max-height: 400px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="flaticon-chart-pie text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Saldo Aktif</p>
                                <h4 class="card-title" id="balance">Rp. 0</h4>
                            </div>
                        </div>
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
                        <h5 class="text-uppercase title">List Transaksi Topup</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Topup</button>
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
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="trxTopupDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Code</th>
                                    <th class="all">Status</th>
                                    <th class="all">Nominal</th>
                                    <th class="all">Bank Bayar</th>
                                    <th class="all">Catatan</th>
                                    <th class="all">Tanggal Request</th>
                                    <th class="all">Tanggal Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center"><small>Tidak Ada Data</small></td>
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
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM BAYAR --}}
        <div class="col-md-4 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Ajukan Topup</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-sm btn-warning" onclick="return closeForm(this)" id="btnCloseForm">
                            <i class="ion-android-close"></i>
                        </button>
                    </div>
                </div>
                <div class="card-block">
                    <form>
                        <input class="form-control" id="id" type="hidden" name="id" />
                        <div class="form-group">
                            <label for="fAmount">Nominal</label>
                            <input class="form-control" id="fAmount" type="number" min="1" name="fAmount"
                                placeholder="masukkan jumlah pembayaran" required />
                        </div>
                        <div class="form-group">
                            <label for="fBank">Bank Transfer</label>
                            <select class="form-control form-control" id="fBank" name="fBank" required>
                                <option value="">Pilih Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->title }} ({{ $bank->account }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="proofOfPayment">Bukti Transfer</label>
                            <input class="form-control" id="proofOfPayment" type="file" name="proofOfPayment"
                                placeholder="upload gambar" />
                            <small class="text-danger">Max ukuran 2MB</small>
                        </div>
                        <div class="form-group">
                            <label for="fRemark">Catatan (opsional)</label>
                            <input class="form-control" id="fRemark" type="text" name="fRemark"
                                placeholder="masukkan catatan" />
                        </div>
                        <div class="form-group">
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i><span>Simpan</span>
                            </button>
                            <button class="btn btn-sm btn-default" id="reset" type="reset"
                                style="margin-left : 10px;"><span>Reset</span>
                            </button>
                        </div>
                    </form>
                </div>
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
                    <p id="bankTarget"></p>
                    <p id="amount"></p>
                    <p id="reasonReject"></p>
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
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        let dTable = null;

        $("#fBank").select2({
            theme: "bootstrap"
        })

        $(function() {
            $('#dateFrom').datetimepicker({
                format: 'DD/MM/YYYY',
            });
            $('#dateTo').datetimepicker({
                format: 'DD/MM/YYYY',
            });
            $("#dateFrom").val(moment().startOf('month').format("DD/MM/YYYY"))
            $("#dateTo").val(moment().endOf('month').format("DD/MM/YYYY"))
            getStatikSession();
            dataTable();
        })

        function getStatikSession() {
            $.ajax({
                url: "{{ route('statik-session') }}",
                header: {
                    'Content-Type': 'application/json'
                },
                success: function(resp) {
                    let data = resp.data;
                    $("#balance").html(convertToRupiah(data.balance));
                },
                error: function(err) {
                    console.log("error get static session :", err)
                }
            })
        }

        function dataTable(filter) {
            let url = "{{ route('trx-topup.datatable') }}";
            if (filter) url += "?" + filter;

            dTable = $("#trxTopupDataTable").DataTable({
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
                    data: "amount",
                    render: function(data, type, row) {
                        return convertToRupiah(data);
                    }
                }, {
                    data: "bank"
                }, {
                    data: "remark",
                    "render": function(data, type, row, meta) {
                        if (type === 'display') {
                            return `<div class="wrap-text">${data}</div>`;
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
                            let amount = 0;

                            d.map((r) => {
                                amount += r.amount;
                            });

                            $(api.column(3).footer()).html('TOTAL');
                            $(api.column(4).footer()).html(convertToRupiah(amount));
                        }
                    })
                },
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
            getStatikSession();
        }

        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                tgl_awal: $("#dateFrom").val(),
                tgl_akhir: $("#dateTo").val(),
                status: $("#fStatus").val(),
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })

        function addData() {
            $("#formEditable").fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12");
                $("#reset").click();
            })
        }

        $("#formEditable form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("amount", $("#fAmount").val());
            formData.append('bank_id', $("#fBank").val());
            formData.append("remark", $("#fRemark").val());
            formData.append("proof_of_payment", document.getElementById("proofOfPayment").files[0]);

            let c = confirm("Anda yakin data yang dimasukan sudah sesuai ?")
            if (c) saveData(formData);
            return false;
        });

        function saveData(data, ) {
            $.ajax({
                url: "{{ route('trx-topup.create') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function getData(id, status) {
            $.ajax({
                url: "{{ route('trx-topup.detail', ['id' => ':id']) }}".replace(':id', id),
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
                    $("#modalDetailTitle").html("ALASAN TOLAK BAYAR")
                    $("#reasonReject").html(data.remark);
                    if (data.proof_of_return) {
                        $("#proofImg").attr("src", data.proof_of_return);
                    }
                }

                if (status == "SHOW-PROOF-PAYMENT") {
                    $("#modalDetailTitle").html("BUKTI PEMBAYARAN TOPUP")
                    $("#bankTarget").html(`TUJUAN : ${data.bank != "" ? data.bank : "Dibayar Manual Oleh Admin"}`);
                    $("#amount").html(`Nominal : ${data.amount}`);
                    // biasanya di payment manual oleh admin
                    if (data.status == "SUCCESS") {
                        $("#reasonReject").html(`Catatan : ${data.remark}`);
                    }
                    if (data.proof_of_payment) {
                        $("#proofImg").attr("src", data.proof_of_payment);
                    }
                }
            });

            return false;
        }

        $("#modalDetail").on("hidden.bs.modal", function() {
            $("#reasonReject").html("");
            $("#bankTarget").html("");
            $("#amount").html("");
            $("#admin").html("");
            $("#totalAmount").html("");
            $("#proofImg").attr("src", "");
        });

        function changeStatus(id, status) {
            let c = confirm(`Anda yakin untuk mengubah status transaksi menjadi '${status}' ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("id", id);
                dataToSend.append("status", status);

                $.ajax({
                    url: "{{ route('trx-topup.change-status') }}",
                    contentType: false,
                    processData: false,
                    method: "POST",
                    data: dataToSend,
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                        refreshData();
                    },
                    error: function(err) {
                        console.log("error :", err);
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                            ?.message);
                    }
                })
            }
            return false;
        }
    </script>
@endpush
