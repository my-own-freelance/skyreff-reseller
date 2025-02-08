@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('/dashboard/css/toggle-status.css') }}">
    <style>
        .wrap-text {
            max-width: 500px;
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
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">List Kompalin Transaksi</h5>
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
                        <table class="table table-striped table-bordered nowrap dataTable" id="trxCompensationDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Code</th>
                                    <th class="all">Status</th>
                                    <th class="all">Reseller</th>
                                    <th class="all">Trx Ref</th>
                                    <th class="all">Kendala</th>
                                    <th class="all">Tanggal Request</th>
                                    <th class="all">Tanggal Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- MODAL REJECT / SUCCESS WITH REASON --}}
    <div class="modal fade" id="modalRejectSuccess" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="formRejectSuccess">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <input type="hidden" name="trxId" id="trxId">
                        <input type="hidden" name="reqStatus" id="reqStatus">
                        <div class="form-group">
                            <label for="reason">Catatan</label>
                            <textarea class="form-control" name="reason" id="reason" cols="30" rows="5" required></textarea>
                        </div>
                        <div class="form-group" id="divProofSolution" style="display: none;">
                            <label for=proofSolution">Bukti Penyelesaian</label>
                            <input class="form-control" id="proofSolution" type="file" name="proofSolution"
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
                    <p id="notes"></p>
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
            let url = "{{ route('trx-compensation.datatable') }}";
            if (filter) url += "?" + filter;

            dTable = $("#trxCompensationDataTable").DataTable({
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
                    data: "trx"
                }, {
                    data: "description",
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
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
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

        function getData(id, status) {
            $.ajax({
                url: "{{ route('trx-compensation.detail', ['id' => ':id']) }}".replace(':id', id),
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
                if (status == "DETAIL") {
                    $("#modalDetailTitle").html("DETAIL KOMPLAIN")
                    $("#notes").html(`<strong>Permasalahan : </strong> ${data.description}`);
                    if (data.proof_of_constrain) {
                        $("#proofImg").attr("src", data.proof_of_constrain);
                    }
                }

                if (status == "SHOW-REASON-REJECT") {
                    $("#modalDetailTitle").html("ALASAN DITOLAK")
                    $("#notes").html(data.remark);
                }

                if (status == "SHOW-PROOF-SOLUTION") {
                    $("#modalDetailTitle").html("BUKTI PENYELESAIAN")
                    $("#notes").html(`<strong>Solusi Admin : </strong> ${data.description}`);
                    if (data.proof_of_solution) {
                        $("#proofImg").attr("src", data.proof_of_solution);
                    }
                }
            });

            return false;
        }

        $("#modalDetail").on("hidden.bs.modal", function() {
            $("#notes").html("");
            $("#proofImg").attr("src", "");
        });

        function changeStatus(id, status) {
            if (status == "REJECT" || status == "SUCCESS") {
                laodModalRejectSuccess(id, status);
                return false;
            } else {
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

        function laodModalRejectSuccess(id, status) {
            const modal = $("#modalRejectSuccess");
            modal.modal('show');
            modal.off('shown.bs.modal').on('shown.bs.modal', function() {
                $("#trxId").val(id);
                $("#reqStatus").val(status);
                if (status == "SUCCESS") {
                    $("#divProofSolution").fadeIn(200, function() {
                        $("#proofSolution").attr("required", true);
                    })
                }
            });

            return false;
        }

        $("#formRejectSuccess").submit(function(e) {
            e.preventDefault();
            let c = confirm(`Anda yakin untuk mengubah status transaksi menjadi ${$("#reqStatus").val()} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("id", $("#trxId").val());
                dataToSend.append("status", $("#reqStatus").val());
                dataToSend.append("remark", $("#reason").val());

                if ($("#reqStatus").val() == "SUCCESS") {
                    dataToSend.append("proof_of_solution", document.getElementById("proofSolution").files[0]);
                }
                sendChangeStatus(dataToSend, true) // hide modal after action
            }
            return false;
        })

        $("#modalRejectSuccess").on("hidden.bs.modal", function() {
            $(this).find("form")[0].reset();
            // BY DEFAULT UPLOAD BUKTI TRANSFER DI HIDDEN
            $("#divProofSolution").slideUp(200, function() {
                $("#proofSolution").attr("required", false);
            });
        });


        function sendChangeStatus(data, hideModal = false) {
            $.ajax({
                url: "{{ route('trx-compensation.change-status') }}",
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
                        $("#modalRejectSuccess").modal('hide')
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
    </script>
@endpush
