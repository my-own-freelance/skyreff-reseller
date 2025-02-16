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

        .spinner-loader {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 3px solid white;
            border-top: 3px solid transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
@section('content')
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card card-primary shadow-lg border-0 rounded-3" style="color: white;">
                <div class="card-body p-4 text-center bubble-shadow">
                    <h2 class="mt-2">
                        <b class="text-uppercase" style="font-weight: 900; text-shadow: 2px 2px 5px rgba(0,0,0,0.2);">Pusat
                            Hadiah</b>
                    </h2>
                    <h1 class="my-3">
                        <i class="fas fa-gift" style="font-size: 100px; text-shadow: 3px 3px 10px rgba(0,0,0,0.3);"></i>
                    </h1>
                    <div class="text-light mb-4" style="font-size: 16px; font-weight: 500;">Dapatkan Berbagai Macam Hadiah
                        Menarik</div>
                    <button id="claimReward" class="btn btn-light text-dark btn-lg fw-bold px-4 py-2 rounded-pill shadow-sm"
                        onclick="claimReward()" style="transition: 0.3s; border: 2px solid white;">
                        🎉 Claim Hadiah 🎉
                    </button>
                </div>
            </div>

        </div>
        <div class="col-md-8" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">Transaksi Reward</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                    </div>
                    <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Tanggal Mulai</label>
                                    <input class="form-control date-picker" id="dateFrom" type="text"
                                        placeholder="Pilih tanggal awal" />
                                </div>
                            </div>
                            <div class="col-md-4">
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
                        <table class="table table-striped table-bordered nowrap dataTable" id="trxRewardDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Status</th>
                                    <th class="all">Reward</th>
                                    <th class="all">Waktu Claim</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
                    <p id="reason"></p>
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
    {{-- <script src="{{ asset('/dashboard/js/plugin/sweetalert/sweetalert.min.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

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
            let url = "{{ route('trx-reward.datatable') }}";
            if (filter) url += "?" + filter;

            dTable = $("#trxRewardDataTable").DataTable({
                searching: false,
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
                    data: "status"
                }, {
                    data: "reward"
                }, {
                    data: "created"
                }],
                pageLength: 25,
            });
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

        function refreshData() {
            dTable.ajax.reload(null, false);
        }

        function claimReward() {
            let $btn = $("#claimReward"); // Ambil tombol
            let originalText = $btn.html(); // Simpan teks asli

            // Ubah tombol jadi loader
            $btn.html(`<span class="spinner-loader"></span> <span class="loading-text">Memproses...</span>`).prop(
                'disabled', true);

            setTimeout(() => {

                $.ajax({
                    url: "{{ route('trx-reward.create') }}",
                    contentType: false,
                    processData: false,
                    method: "POST",
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        $btn.html(originalText).prop('disabled', false);
                        // showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                        succesClaim(res.data)
                        refreshData();
                    },
                    error: function(err) {
                        console.log("error :", err);
                        $btn.html(originalText).prop("disabled", false);
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                            .responseJSON
                            ?.message);
                    }
                })
            }, 2000)
        }

        function getData(id, status) {
            $.ajax({
                url: "{{ route('trx-reward.detail', ['id' => ':id']) }}".replace(':id', id),
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
                    $("#modalDetailTitle").html("ALASAN DITOLAK")
                    $("#reason").html(data.remark);
                }

                if (status == "SHOW-PROOF-ACCEPTION") {
                    $("#modalDetailTitle").html("BUKTI HADIAH DITERIMA")
                    $("#reason").html(`Catatan : ${data.remark}`);
                    if (data.proof_of_payment) {
                        $("#proofImg").attr("src", data.proof_of_payment);
                    }
                }
            });

            return false;
        }

        $("#modalDetail").on("hidden.bs.modal", function() {
            $("#reason").html("");
            $("#proofImg").attr("src", "");
        });

        function succesClaim(reward) {
            Swal.fire({
                title: 'Selamat! 🎉',
                html: `
                    <p>Anda berhasil mengklaim reward berikut:</p>
                    <div style="text-align: center;">
                        <img src="${reward.image}" alt="${reward.title}" style="width: 250px; height: 250px; object-fit: cover; border-radius: 10px; margin-bottom: 10px;">
                        <h3 style="color: #6610f2; font-weight: bold;">${reward.title}</h3>
                    </div>
                `,
                icon: 'success',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745',
                timer: 5000,
                timerProgressBar: true,
            });
        }
    </script>
@endpush
