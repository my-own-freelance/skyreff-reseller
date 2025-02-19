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

        /* WRAPPER */
        .info-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: start;
        }

        .info-box {
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            width: 150px;
            text-align: center;
        }

        .info-title {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 18px;
            color: #000;
        }

        /* END WRAPPER */

        /* INFO DETAIL WRAPPER */
        .info-detail-wrapper {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .info-detail {
            display: flex;
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            margin-right: 10px;
            white-space: nowrap;
            min-width: 150px;
        }

        .info-value {
            font-size: 14px;
            color: #555;
        }

        /* END INFO DETAIL WRAPPER */
    </style>
@endpush
@section('content')
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">List Produk</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                    </div>
                    <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                        <div class="row">
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
                                <div class="pt-3">
                                    <button class="mt-4 btn btn-sm btn-success mr-3" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="productDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Gambar</th>
                                    <th class="all">Produk</th>
                                    <th class="all">Kutipan</th>
                                    <th class="all">Kategori</th>
                                    <th class="all">Harga</th>
                                    <th class="all">Komisi</th>
                                    <th class="all">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOX DETAIL --}}
        <div class="col-md-12" style="display: none" id="boxDetail">
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
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-chart-pie text-info"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Limit Hutang</p>
                                        <h4 class="card-title" id="limitDebt">Rp. 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-chart-pie text-danger"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Total Hutang</p>
                                        <h4 class="card-title" id="debtTotal">Rp. 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- DETAIL SECTION --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-header-left">
                                <h5>DETAIL PRODUK</h5>
                            </div>
                            <div class="card-header-right">
                                <button class="btn btn-sm btn-warning" onclick="return closeForm(this)">
                                    <i class="ion-android-close"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">

                            {{-- title --}}
                            <div class="row mt-3">
                                <div class="col-md-2">
                                    <img src="{{ asset('dashboard/img/no-image.jpg') }}" alt="" id="pProductImage"
                                        style="width:100%; height:auto; object-fit:cover;">
                                </div>
                                <div class="col-md-10">
                                    <h1 id="pTitle" class="font-weight-bold"></h1>
                                    <h4 id="pExcerpt"></h4>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <div class="col-md-8">
                                    <h4 class="font-weight-bold">Detail Produk</h4>
                                    <div class="info-detail-wrapper">
                                        {{-- rendered detail information --}}
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4 class="font-weight-bold">Deskripsi</h4>
                                    <div class="description-wrapper">
                                        {{-- rendered description --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD SECTION --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="invoice-detail">
                                <div class="invoice-top">
                                    <h5 class="title"><strong>Detail Tagihan</strong></h5>
                                </div>
                                <div class="separator-solid"></div>
                                <div class="invoice-item mt-3" id="formCheckout">
                                    <form>
                                        <input class="form-control" id="cpProductId" type="hidden" name="id" />
                                        <div class="form-group" style="margin-bottom: -10px; margin-top: -10px">
                                            <label for="cpPrice">Harga Produk</label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                                </div>
                                                <input class="form-control" id="cpPrice" type="text" readonly />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cpQty">Jumlah Pesanan<span class="text-danger">*</span></label>
                                            <input class="form-control" id="cpQty" type="number" name="qty"
                                                min="1" placeholder="masukkan masukan jumlah pesanan" required />
                                        </div>
                                        <div class="form-group" style="margin-bottom: -10px; margin-top: -10px">
                                            <label for="cpTotalAmount">Total Tagihan</label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                                </div>
                                                <input class="form-control" id="cpTotalAmount" type="text" readonly />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cNotes">Catatan <span class="text-danger">*</span></label>
                                            <input class="form-control" id="cNotes" type="text" name="cNotes"placeholder="berikan catatan pesanan anda" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="cpPaymentType">Metode Bayar <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-control" id="cpPaymentType"
                                                name="cpPaymentType" required>
                                                <option value = "">Pilih Metode Bayar</option>
                                                <option value = "BALANCE">SALDO</option>
                                                <option value = "DEBT">HUTANG</option>
                                                <option value = "TRANSFER">TRANSFER</option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="divCBank" style="display: none;">
                                            <label for="cpBank">Bank Owner <span class="text-danger">*</span></label>
                                            <select class="form-control form-control" id="cpBank" name="cpBank">
                                                <option value = "">Pilih Bank Tujuan</option>
                                                @forelse ($banks as $bank)
                                                    <option value="{{ $bank->id }}">{{ $bank->title }} -
                                                        {{ $bank->account }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>

                                        <div class="form-group" id="divCProofPay" style="display: none;">
                                            <label for="cpProofPayment">Bukti Transfer</label>
                                            <input class="form-control" id="cpProofPayment" type="file"
                                                name="cpProofPayment" placeholder="upload gambar" />
                                            <small class="text-danger">Max ukuran 2MB</small>
                                        </div>

                                        <button class="btn btn-secondary btn-block mt-4" type="submit">
                                            <span class="btn-label mr-2">
                                                <i class="far fa-credit-card"></i>
                                            </span>
                                            Proses Pembayaran
                                        </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- MODAL CHECKOUT --}}
        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('/dashboard/js/plugin/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        // GLOBAL VARIABEL
        let productPrice = 0;

        // END GLOBAL VARIABEL
        $("#fProductCategoryId, #cpBank").select2({
            theme: "bootstrap"
        })

        function formatToRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                // style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        function removeRupiahFormat(rupiah) {
            return parseInt(rupiah.replace(/[^,\d]/g, ''), 10);
        }

        let dTable = null;

        $(function() {
            dataTable();
        })

        function dataTable(filter) {
            let url = "{{ route('product.datatable') }}";
            if (filter) url += "?" + filter;

            dTable = $("#productDataTable").DataTable({
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
                    data: "image"
                }, {
                    data: "title"
                }, {
                    data: "excerpt",
                    "render": function(data, type, row, meta) {
                        if (type === 'display') {
                            return `<div class="wrap-text">${data}</div>`;
                        }
                        return data;
                    }
                }, {
                    data: "category"
                }, {
                    data: "price"
                }, {
                    data: "commission"
                }, {
                    data: "stock"
                }],
                pageLength: 10,
            });
        }

        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                product_category_id: $("#fProductCategoryId").val(),
                is_active: $("#fStatus").val()
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })

        function refreshData() {
            dTable.ajax.reload(null, false);
            $("#summernote").summernote('code', "");
        }

        function closeForm() {
            $("#formCheckout").find("form")[0].reset();
            $("#boxTable").fadeIn(200);
            $("#boxDetail").slideUp(200);
        }

        function getStatikSession() {
            $.ajax({
                url: "{{ route('statik-session') }}",
                header: {
                    'Content-Type': 'application/json'
                },
                success: function(resp) {
                    let data = resp.data;
                    $("#balance").html(convertToRupiah(data.balance));
                    $("#limitDebt").html(convertToRupiah(data.debt_limit));
                    $("#debtTotal").html(convertToRupiah(data.total_debt));
                },
                error: function(err) {
                    console.log("error get static session :", err)
                }
            })
        }

        function loadCheckout(id) {
            $.ajax({
                url: "{{ route('product.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    let data = res.data;
                    $("#boxDetail").fadeIn(200, function() {
                        $("#boxTable").slideUp(200);
                        getStatikSession()
                        // RESET RENDERER WRAPPER
                        $(".info-detail-wrapper").empty();
                        $(".description-wrapper").empty();
                        $("#divCheckout").empty();

                        // INFORMASI UMUM PRODUK
                        if (data.image) {
                            $("#pProductImage").attr("src", data.image);
                        }
                        $("#pTitle").html(data.title);
                        $("#pExcerpt").html(data.excerpt);

                        // DETAIL PRODUK
                        const availableStatus = data.is_active == "Y" ?
                            '<div class="badge badge-success">Tersedia</div>' :
                            '<div class="badge badge-danger">Tidak Tersedia</div>'

                        $(".info-detail-wrapper").append(generateInfoDetail("Status", availableStatus));
                        $(".info-detail-wrapper").append(generateInfoDetail("Code",
                            `<strong>${data.code}</strong>`));
                        $(".info-detail-wrapper").append(generateInfoDetail("Kategori", data.category));
                        $(".info-detail-wrapper").append(generateInfoDetail("Harga",
                            `Rp. ${formatToRupiah(data.price)}`));
                        $(".info-detail-wrapper").append(generateInfoDetail("Komisi",
                            `Rp. ${formatToRupiah(data.commission)}`));
                        $(".info-detail-wrapper").append(generateInfoDetail("Stok",
                            `${data.stock} Produk`));


                        // DESCRIPTION PROPERTY
                        $(".description-wrapper").append(data.description);

                        // CART CHECKOUT
                        $("#cpProductId").val(data.id);
                        $("#cpPrice").val(`${formatToRupiah(data.price)}`);
                        productPrice = data.price;
                        $("#cpBank").attr("required", false);
                        $("#cpProofPayment").attr("required", false);

                    })
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function generateInfoDetail(label, value) {
            return $(`<div class="info-detail">
                        <div class="info-label">${label}</div>
                        <div class="info-value">: ${value}</div>
                    </div>`)
        }


        // CHECKOUT PRODUK
        $("#cpQty").on('keyup', function() {
            console.log("change")
            let qty = $(this).val();
            let cpTotalAmount = qty * productPrice;
            $("#cpTotalAmount").val(`${formatToRupiah(cpTotalAmount)}`)

        });

        $('#cpPaymentType').change(function() {
            let type = $(this).val();

            if (type == 'DEBT' || type == 'BALANCE') {
                $('#divCBank').slideUp(200, function() {
                    $("#cpBank").attr("required", false);
                })
                $('#divCProofPay').slideUp(200, function() {
                    $("#cpProofPayment").attr("required", false);
                })
            } else {
                $('#divCBank').fadeIn(200, function() {
                    $("#cpBank").attr("required", true);
                })
                $('#divCProofPay').fadeIn(200, function() {
                    $("#cpProofPayment").attr("required", true);
                })
            }
        })


        $("#formCheckout form").submit(function(e) {
            e.preventDefault();
            let paymentType = $("#cpPaymentType").val();
            let formData = new FormData();
            formData.append("product_id", $("#cpProductId").val());
            formData.append("qty", $("#cpQty").val());
            formData.append("notes", $("#cNotes").val());
            formData.append("payment_type", paymentType);

            if (paymentType == "TRANSFER") {
                formData.append("bank_id", $("#cpBank").val());
                formData.append("proof_of_payment", document.getElementById("cpProofPayment").files[0]);
            }

            let c = confirm("Anda yakin pesanan anda sudah sesuai ?")
            if (c) {
                checkoutProduct(formData);
            }

            return false;
        })

        function checkoutProduct(data) {
            $.ajax({
                url: "{{ route('trx-product.create') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                success: function(res) {
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData()
                    setTimeout(() => {
                        window.location.href = "{{ route('trx-product') }}"
                    }, 3000)
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }
    </script>
@endpush
