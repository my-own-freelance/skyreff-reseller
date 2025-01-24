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

         /* PRODUCT IMAGE */
         .image-wrapper {
            position: relative !important;
            max-width: 300px;
            height: 300px;
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .delete-button {
            position: absolute;
            top: 10px;
            right: 10px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #767676;
            box-sizing: border-box;
        }

        .delete-button i {
            color: white;
        }

        /* END PRODUCT IMAGE */

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
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah Data</button>
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
                                <div class="form-group">
                                    <label for="fStatus">Filter Status</label>
                                    <select class="form-control" id="fStatus" name="fStatus">
                                        <option value="">All</option>
                                        <option value="Y">Publish</option>
                                        <option value="N">Draft</option>
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
                                    <th class="all">Judul</th>
                                    <th class="all">Kutipan</th>
                                    <th class="all">Kategori</th>
                                    <th class="all">Harga</th>
                                    <th class="all">Komisi</th>
                                    <th class="all">Stock</th>
                                    <th class="all">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Data</h5>
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
                            <label for="title">Judul <span class="text-danger">*</span></label>
                            <input class="form-control" id="title" type="text" name="title"
                                placeholder="masukkan judul" required />
                        </div>
                        <div class="form-group">
                            <label for="excerpt">Kutipan <span class="text-danger">*</span></label>
                            <input class="form-control" id="excerpt" type="text" name="excerpt"
                                placeholder="masukkan kutipan singkat mengenai product" required />
                        </div>
                        <div class="form-group">
                            <label for="code">Kode Produk <span class="text-muted">(optional)</span></label>
                            <input class="form-control" id="code" type="text" name="code"
                                placeholder="masukkan code produk (optional)" />
                        </div>
                        <div class="form-group">
                            <label for="product_category_id">Kategori <span class="text-danger">*</span></label>
                            <select class="form-control form-control" id="product_category_id" name="product_category_id"
                                required>
                                <option value = "">Pilih Kategori</option>
                                @foreach ($categories as $category)
                                    <option value = "{{ $category->id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purchase_price">Harga Beli <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                </div>
                                <input class="form-control" id="purchase_price" type="text" min="1"
                                    name="purchase_price" placeholder="masukkan harga beli" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="selling_price">Harga Jual<span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                </div>
                                <input class="form-control" id="selling_price" type="text" min="1"
                                    name="selling_price" placeholder="masukkan harga jual" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="commission_regular">Komisi Regular <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                </div>
                                <input class="form-control" id="commission_regular" type="text" min="1"
                                    name="commission_regular" placeholder="masukkan komisi" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="commission_vip">Komisi VIP <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                </div>
                                <input class="form-control" id="commission_vip" type="text" min="1"
                                    name="commission_vip" placeholder="masukkan komisi" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="is_active">Status <span class="text-danger">*</span></label>
                            <select class="form-control form-control" id="is_active" name="is_active" required>
                                <option value= "">Pilih Status</option>
                                <option value="Y">Publish</option>
                                <option value="N">Draft</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stok <span class="text-danger">*</span></label>
                            <input class="form-control" id="stock" type="number" min='0' name="stock"
                                placeholder="masukkan stok" required />
                        </div>
                        <div class="form-group">
                            <label for="image">Gambar</label>
                            <input class="form-control" id="image" type="file" name="image"
                                placeholder="upload gambar" />
                            <small class="text-danger">Max ukuran 2MB</small>
                        </div>
                        <div class="form-group">
                            <label for="description">Dekripsi</label>
                            <div id="summernote" name="description"></div>
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

        <div class="col-md-12" style="display: none" id="formProductImage">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>GALLERY PROPERTY</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-sm btn-primary" onclick="return addFormImage()">
                            <i class="icon-plus text-white"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="return closeForm(this)">
                            <i class="ion-android-close"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12" id="boxproductImage">
                            <div class="row" id="productImage">
                                {{-- rendered image list --}}
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12" style="display: none" data-action="update" id="formAddImage">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-header-left">
                                        <h5>Tambah</h5>
                                    </div>
                                    <div class="card-header-right">
                                        <button class="btn btn-sm btn-warning" onclick="return closeFormImage(this)"
                                            id="btnCloseForm">
                                            <i class="ion-android-close"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <form>
                                        <input class="form-control" id="product_id" type="hidden" name="id" />
                                        <div class="form-group">
                                            <label for="property_image">Gambar</label>
                                            <input class="form-control" id="property_image" type="file"
                                                name="property_image[]" multiple placeholder="upload gambar" required />
                                            <small class="text-danger">Max ukuran 5MB</small>
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-sm btn-primary" type="submit" id="submitImage">
                                                <i class="ti-save"></i><span>Simpan</span>
                                            </button>
                                            <button class="btn btn-sm btn-default" id="resetImage" type="reset"
                                                style="margin-left : 10px;"><span>Reset</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
        $('#summernote').summernote({
            placeholder: 'masukkan deskripsi',
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
            tabsize: 2,
            height: 300
        });

        $('#fProductCategoryId,#fStatus,#product_category_id,#is_active').select2({
            theme: "bootstrap"
        });

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

        $('#purchase_price, #selling_price, #commission_regular, #commission_vip').on('keyup', function() {
            let value = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatToRupiah(value));
        });

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
                }, {
                    data: "is_active"
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


        function addData() {
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
            $("#image").attr("required", true);
            $("#title").focus();
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12").fadeIn(200);
                $("#reset").click();
                $("#summernote").summernote('code', "");

            })
            // view product image
            $("#formProductImage").slideUp(200);
        }

        function getData(id) {
            $.ajax({
                url: "{{ route('product.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
                        let d = res.data;
                        $("#id").val(d.id);
                        $("#title").val(d.title);
                        $("#excerpt").val(d.excerpt);
                        $("#code").val(d.code);
                        $("#product_category_id").val(d.product_category_id).change();
                        $("#purchase_price").val(formatToRupiah(d.purchase_price));
                        $("#selling_price").val(formatToRupiah(d.selling_price));
                        $("#commission_regular").val(formatToRupiah(d.commission_regular));
                        $("#commission_vip").val(formatToRupiah(d.commission_vip));
                        $("#is_active").val(d.is_active).change();
                        $("#stock").val(d.stock);
                        $("#image").attr("required", false);
                        $("#summernote").summernote('code', d.description);

                    })
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        $("#formEditable form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("title", $("#title").val());
            formData.append('excerpt', $("#excerpt").val());
            formData.append('code', $("#code").val());
            formData.append('product_category_id', $("#product_category_id").val());
            formData.append("is_active", $("#is_active").val());
            formData.append("stock", $("#stock").val());
            formData.append("image", document.getElementById("image").files[0]);
            formData.append("description", $("#summernote").summernote('code'));

            if (!isNaN(removeRupiahFormat($('#purchase_price').val()))) {
                formData.append("purchase_price", removeRupiahFormat($("#purchase_price").val()));
            }
            if (!isNaN(removeRupiahFormat($('#selling_price').val()))) {
                formData.append("selling_price", removeRupiahFormat($("#selling_price").val()));
            }
            if (!isNaN(removeRupiahFormat($('#commission_regular').val()))) {
                formData.append("commission_regular", removeRupiahFormat($("#commission_regular").val()));
            }
            if (!isNaN(removeRupiahFormat($('#commission_vip').val()))) {
                formData.append("commission_vip", removeRupiahFormat($("#commission_vip").val()));
            }

            saveData(formData, $("#formEditable").attr("data-action"));
            return false;
        });

        function updateStatus(id, status) {
            let c = confirm(`Anda yakin ingin mengubah status ke ${status} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("is_active", status == "Draft" ? "N" : "Y");
                dataToSend.append("id", id);
                updateStatusData(dataToSend);
            }
        }

        function saveData(data, action) {
            $.ajax({
                url: action == "update" ? "{{ route('product.update') }}" : "{{ route('product.create') }}",
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

        function removeData(id) {
            let c = confirm("Apakah anda yakin untuk menghapus data ini ?");
            if (c) {
                $.ajax({
                    url: "{{ route('product.destroy') }}",
                    method: "DELETE",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        refreshData();
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    },
                    error: function(err) {
                        console.log("error :", err);
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                            ?.message);
                    }
                })
            }
        }

        function updateStatusData(data) {
            $.ajax({
                url: "{{ route('product.change-status') }}",
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
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }


        // CRUD GALLERY IMAGE PROPERTY
        function addGallery(product_id) {
            $("#formProductImage").fadeIn(200, function() {
                $("#boxTable").slideUp(200);
                galleryList(product_id)
            })
        }

        function addFormImage() {
            $("#formAddImage").fadeIn(200, function() {
                $("#boxproductImage").removeClass("col-md-12").addClass("col-md-8")
            })
        }

        function closeFormImage() {
            $("#formAddImage").slideUp(200, function() {
                $("#boxproductImage").removeClass("col-md-8").addClass("col-md-12");
                $("#resetImage").click();
            })
        }

        $("#formAddImage form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("product_id", $("#product_id").val());
            let files = document.getElementById("property_image").files;
            for (let i = 0; i < files.length; i++) {
                formData.append("images[]", files[i]);
            }
            saveproductImage(formData);
            return false;
        });

        function saveproductImage(data, action) {
            $.ajax({
                url: "{{ route('product-image.create') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    closeFormImage();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    galleryList($("#product_id").val());
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function removeProductImage(id) {
            let c = confirm("Apakah anda yakin untuk menghapus data ini ?");
            if (c) {
                $.ajax({
                    url: "{{ route('product-image.destroy') }}",
                    method: "DELETE",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        galleryList($("#product_id").val());
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    },
                    error: function(err) {
                        console.log("error :", err);
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                            ?.message);
                    }
                })
            }
        }

        function galleryList(product_id) {
            $.ajax({
                url: "{{ route('product-image.list', ['product_id' => ':product_id']) }}".replace(':product_id', product_id),
                header: {
                    "Content-Type": "application/json",
                },
                method: "GET",
                success: function(res) {
                    $("#productImage").empty();
                    $("#title").html(res.title);
                    $("#product_id").val(product_id);
                    $.each(res.data, function(index, item) {
                        const elImage = $(`
                            <div class='col col-md-3 col-sm-6 col-12'>
                                <div class='image-wrapper mb-3 border' style='padding:5px!important;'>
                                    <img src='${item.image}' alt='Gambar 1' class='img-fluid'>
                                    <button class='btn delete-button' onclick='return removeProductImage("${item.id}")' href='javascript:void(0);'>
                                        <i class='fas fa-trash ml-1'></i>
                                    </button>
                                </div>
                            </div>
                        `);
                        $("#productImage").append(elImage);
                    });
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-danger", "Peringatan", err.message || err.responseJSON
                        ?.message);
                    $("#productImage").empty();
                }

            })
        }
        // END CRUD GALLERY IMAGE PROPERTY
    </script>
@endpush
