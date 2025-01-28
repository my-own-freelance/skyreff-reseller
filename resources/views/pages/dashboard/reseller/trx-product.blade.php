@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('/dashboard/css/toggle-status.css') }}">
@endpush
@section('content')
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
                                    <th class="all">Produk</th>
                                    <th class="all">Harga</th>
                                    <th class="all">Qty</th>
                                    <th class="all">Total</th>
                                    <th class="all">Komisi</th>
                                    <th class="all">Tipe Bayar</th>
                                    <th class="all">Tanggal Trx</th>
                                    <th class="all">Tanggal Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="11" class="text-center"><small>Tidak Ada Data</small></td>
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
                            </tfoot>
                        </table>
                    </div>
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
                        return convertToRupiah(data);
                    }
                }, {
                    data: "commission",
                    render: function(data, type, row) {
                        return `<span class="text-success">+ ${convertToRupiah(data)}</span>`;
                    }
                }, {
                    data: "payment_type"
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
                            let commission = 0;

                            d.map((r) => {
                                commission += r.commission
                            });

                            $(api.column(6).footer()).html('TOTAL KOMISI');
                            $(api.column(7).footer()).html(convertToRupiah(commission));
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

        function getData(id) {
            $.ajax({
                url: "{{ route('banner.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
                        let d = res.data;
                        $("#id").val(d.id);
                        $("#title").val(d.title);
                        $("#excerpt").val(d.excerpt);
                        $("#is_active").val(d.is_active).change();
                        $("#image").attr("required", false);
                    })
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }
    </script>
@endpush
