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
    </style>
@endpush
@section('content')
    <div class="row mb-5 mt--5">
        <div class="col-md-12" id="boxTable">
            <ul class="nav nav-tabs md-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active text-uppercase" id="tabWaiting" data-toggle="tab" href="#waiting"
                        role="tab">WAITING</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase" id="tabRegular" data-toggle="tab" href="#regular"
                        role="tab">REGULAR</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase" id="tabVip" data-toggle="tab" href="#vip"
                        role="tab">VIP</a>
                    <div class="slide"></div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase" id="tabDeleted" data-toggle="tab" href="#deleted"
                        role="tab">DELETED</a>
                    <div class="slide"></div>
                </li>
            </ul>
            <div class="tab-content card-block" style="padding: 0px; padding-top: 1.25em">
                <div class="tab-pane active" id="waiting" role="tabpanel">
                    <center>
                        <h5>Loading ....</h5>
                    </center>
                </div>
                <div class="tab-pane" id="regular">
                    <center>
                        <h5>Loading ....</h5>
                    </center>
                </div>
                <div class="tab-pane" id="vip">
                    <center>
                        <h5>Loading .... </h5>
                    </center>
                </div>
                <div class="tab-pane" id="deleted">
                    <center>
                        <h5>Loading .... </h5>
                    </center>
                </div>
            </div>
        </div>

        {{-- FORM EDITABLE --}}
        <div class="col-md-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 id="form-title">Tambah / Edit Data</h5>
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

                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 id="form-title">Informasi Umum</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input class="form-control" id="name" type="text" name="name"
                                                placeholder="masukkan nama reseller" required />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="username">Username <span class="text-danger">*</span></label>
                                            <input class="form-control" id="username" type="text" name="username"
                                                placeholder="masukkan username reseller" required />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="password">Password <span class="text-danger">*</span></label>
                                            <input class="form-control" id="password" type="password" name="password"
                                                placeholder="masukkan password reseller" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="phone_number">Nomor Telpon <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" id="phone_number" type="text"
                                                name="phone_number" placeholder="masukkan nomor telpon reseller"
                                                required />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="level">Level <span class="text-danger">*</span></label>
                                            <select class="form-control form-control" id="level" name="level"
                                                required>
                                                <option value= "">Pilih Level</option>
                                                <option value="REGULAR">Regular</option>
                                                <option value="VIP">VIP</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="is_active">Status <span class="text-danger">*</span></label>
                                            <select class="form-control form-control" id="is_active" name="is_active"
                                                required>
                                                <option value= "">Pilih Status</option>
                                                <option value="Y">Enable</option>
                                                <option value="N">Disable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="debt_limit">Limit Hutang <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                                </div>
                                                <input class="form-control" id="debt_limit" type="text"
                                                    min="0" name="debt_limit" placeholder="masukkan limit hutang"
                                                    required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="input-file input-file-image">
                                                <img class="img-upload-preview" width="50%"
                                                    src="{{ asset('dashboard/img/no-image.jpg') }}" alt="preview">
                                                <input type="file" class="form-control form-control-file"
                                                    id="uploadImg2" name="uploadImg2" accept="image/*">
                                                <label for="uploadImg2"
                                                    class="  label-input-file btn btn-black btn-round">
                                                    <span class="btn-label">
                                                        <i class="fa fa-file-image"></i>
                                                    </span>
                                                    Upload Gambar
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 id="form-title">Informasi Umum</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="province_id">Provinsi <span class="text-danger">*</span></label>
                                            <select class="form-control form-control" id="province_id" name="province_id"
                                                required>
                                                <option value = "">Pilih Provinsi</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="district_id">Kabupaten <span class="text-danger">*</span></label>
                                            <select class="form-control form-control" id="district_id" name="district_id"
                                                required>
                                                <option value = "">Pilih Kabupaten</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sub_district_id">Kecamatan <span class="text-danger">*</span></label>
                                            <select class="form-control form-control" id="sub_district_id"
                                                name="sub_district_id" required>
                                                <option value = "">Pilih Kecamatan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="address">Alamat <span class="text-danger">*</span></label>
                                            <input class="form-control" id="address" type="text" name="address"
                                                placeholder="masukan alamat" required />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-right mt-3">
                            </button>
                            <button class="btn btn-sm btn-default" id="reset" type="reset"
                                style="margin-left : 10px;"><span>Reset</span>
                            </button>
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i><span>Simpan</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>
        let waitingTable = null;
        let regularTable = null;
        let vipTable = null;
        let deletedTable = null;

        $(function() {
            $("#waiting").load("{{ route('reseller.waiting') }}", function() {
                waitingDataTable()
            })
            $("#regular").load("{{ route('reseller.regular') }}", function() {
                regularDataTable()
            })
            $("#vip").load("{{ route('reseller.vip') }}", function() {
                vipDataTable()
            })

            $("#deleted").load("{{ route('reseller.deleted') }}", function() {
                deletedDataTable()
            })

            $(".tab-pane").hide()
            $("#waiting").show()


            $("#tabWaiting").click(function() {
                showTab("waiting")
            })

            $("#tabRegular").click(function() {
                showTab("regular")
            })

            $("#tabVip").click(function() {
                showTab("vip")
            })

            $("#tabDeleted").click(function() {
                showTab("deleted")
            })
        })


        function showTab(tabName) {
            $(".tab-pane").hide();
            $('#' + tabName).show();
        }

        function refreshData(tableName) {
            if (tableName == "waiting") {
                waitingTable.ajax.reload(null, false);
            } else if (tableName == "regular") {
                regularTable.ajax.reload(null, false);
            } else if (tableName == "vip") {
                vipTable.ajax.reload(null, false);
            } else if (tableName == "deleted") {
                deletedTable.ajax.reload(null, false);
            }
        }

        function filterData(tableName) {
            if (tableName == "regular") {
                let dataFilter = {}
                let inputFilter = $("#formFilterRegular").serializeArray();
                $.each(inputFilter, function(i, field) {
                    dataFilter[field.name] = field.value;
                });
                regularTable.clear();
                regularTable.destroy();
                regularDataTable($.param(dataFilter))
            } else if (tableName == "vip") {
                let dataFilter = {};
                let inputFilter = $("#formFilterVip").serializeArray();
                $.each(inputFilter, function(i, field) {
                    dataFilter[field.name] = field.value;
                });
                vipTable.clear();
                vipTable.destroy();
                vipDataTable($.param(dataFilter))
            }
        }

        // DATA TABLE LOADED
        function waitingDataTable(filter) {
            let url = "{{ route('reseller.datatable') }}" + "?is_active=N";
            if (filter) url += '&' + filter;
            waitingTable = $("#waitingDataTable").DataTable({
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
                    data: "name"
                }, {
                    data: "username"
                }, {
                    data: "code"
                }, {
                    data: "phone_number"
                }, {
                    data: "level"
                }, {
                    data: "debt_limit"
                }, {
                    data: "total_debt"
                }, {
                    data: "commission"
                }, {
                    data: "is_active"
                }, {
                    data: "address"
                }, {
                    data: "created"
                }],
                pageLength: 25,
            });
        }

        function regularDataTable(filter) {
            let url = "{{ route('reseller.datatable') }}" + "?level=REGULAR";
            if (filter) url += '&' + filter;
            regularTable = $("#regularDataTable").DataTable({
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
                    data: "name"
                }, {
                    data: "username"
                }, {
                    data: "code"
                }, {
                    data: "phone_number"
                }, {
                    data: "debt_limit"
                }, {
                    data: "total_debt"
                }, {
                    data: "commission"
                }, {
                    data: "is_active"
                }, {
                    data: "address"
                }, {
                    data: "created"
                }],
                pageLength: 25,
            });
        }

        function vipDataTable(filter) {
            let url = "{{ route('reseller.datatable') }}" + "?level=VIP";
            if (filter) url += '&' + filter;
            vipTable = $("#vipDataTable").DataTable({
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
                    data: "name"
                }, {
                    data: "username"
                }, {
                    data: "code"
                }, {
                    data: "phone_number"
                }, {
                    data: "debt_limit"
                }, {
                    data: "total_debt"
                }, {
                    data: "commission"
                }, {
                    data: "is_active"
                }, {
                    data: "address"
                }, {
                    data: "created"
                }],
                pageLength: 25,
            });
        }

        function deletedDataTable(filter) {
            let url = "{{ route('reseller.datatable') }}" + "?status_data=DELETED";
            if (filter) url += '&' + filter;
            deletedTable = $("#deletedDataTable").DataTable({
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
                    data: "name"
                }, {
                    data: "username"
                }, {
                    data: "code"
                }, {
                    data: "phone_number"
                }, {
                    data: "level"
                }, {
                    data: "debt_limit"
                }, {
                    data: "total_debt"
                }, {
                    data: "commission"
                }, {
                    data: "is_active"
                }, {
                    data: "address"
                }, {
                    data: "created"
                }],
                pageLength: 25,
            });
        }

        function softDelete(id, oldStatus, newStatus) {
            let c = confirm("Apakah anda yakin untuk memindahkan data ke daftar hapus ?");
            if (c) {
                $.ajax({
                    url: "{{ route('reseller.soft-delete') }}",
                    method: "DELETE",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        refreshData(oldStatus);
                        refreshData(newStatus);
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

        function restoreData(id, oldStatus, newStatus) {
            let c = confirm("Apakah anda yakin untuk me-restore data ini ?");
            if (c) {
                $.ajax({
                    url: "{{ route('reseller.restore') }}",
                    method: "POST",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        refreshData(oldStatus);
                        refreshData(newStatus);
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

        function updateStatus(id, status, refTable) {
            let c = confirm(`Anda yakin ingin mengubah status ke ${status} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("is_active", status == "Disabled" ? "N" : "Y");
                dataToSend.append("id", id);
                updateStatusData(dataToSend, refTable);
            }
        }

        function updateStatusData(data, refTable) {
            $.ajax({
                url: "{{ route('reseller.change-status') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData('waiting');
                    refreshData(refTable)
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        //CREATE UPDATE DATA AGEN
        function addData() {
            $("#formEditable").attr('data-action', 'add').fadeIn(200, function() {
                $("#form-title").html("TAMBAH AGEN")
                $("#boxTable").slideUp(200)
                $("#name").focus();
                $("#password").attr("required", true);
                $("#uploadImg2").attr("required", true);
                getProvinces()
            });
            // $("#boxTableWaiting").removeClass("col-md-12").addClass("col-md-7");

        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").addClass("col-md-12").fadeIn(200);
                // $("#boxTableWaiting").removeClass("col-md-7").addClass("col-md-12");
                $("#reset").click();
            })
        }

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

        $('#debt_limit').on('keyup', function() {
            let value = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatToRupiah(value));
        });

        function getData(id) {
            $.ajax({
                url: "{{ route('reseller.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    let d = res.data;
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#form-title").html("EDIT AGEN")
                        $("#boxTable").slideUp(200)
                        $("#name").focus();

                        $("#id").val(d.id);
                        $("#name").val(d.name);
                        $("#username").val(d.username);
                        $("#username").attr("readonly", true);
                        $("#phone_number").val(d.phone_number);
                        $("#level").val(d.level).change();
                        $("#is_active").val(d.is_active).change();
                        $("#debt_limit").val(formatToRupiah(d.debt_limit));
                        $("#address").val(d.address);

                        if (d.province_id && d.district_id && d.sub_district_id) {
                            getProvinces(true, d.province_id);
                            getDistricts(d.province_id, true, d.district_id);
                            getSubDistricts(d.district_id, true, d.sub_district_id);
                        } else {
                            getProvinces();
                        }

                        $("#password").attr("required", false);
                        $("#uploadImg2").attr("required", false);
                        if(d.image){
                            $(".img-upload-preview").attr("src", d.image);
                        }else {
                            $(".img-upload-preview").attr("src", "{{ asset('dashboard/img/no-image.jpg') }}");
                        }
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
            formData.append("id", $("#id").val());
            formData.append("name", $("#name").val());
            formData.append("username", $("#username").val());
            formData.append("password", $("#password").val());
            formData.append("phone_number", $("#phone_number").val());
            formData.append("level", $("#level").val());
            formData.append("is_active", $("#is_active").val());
            formData.append("province_id", $("#province_id").val());
            formData.append("district_id", $("#district_id").val());
            formData.append("sub_district_id", $("#sub_district_id").val());
            formData.append("address", $("#address").val());
            formData.append("image", document.getElementById("uploadImg2").files[0]);

            if (!isNaN(removeRupiahFormat($('#debt_limit').val()))) {
                formData.append("debt_limit", removeRupiahFormat($("#debt_limit").val()));
            }

            saveData(formData, $("#formEditable").attr("data-action"));
            return false;
        })

        function saveData(data, action) {
            $.ajax({
                url: action == "update" ? "{{ route('reseller.update') }}" : "{{ route('reseller.create') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                    $("#submit").attr("disabled", true)
                },
                success: function(res) {
                    $("#submit").attr("disabled", false)
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData('waiting');
                    refreshData('regular');
                    refreshData('vip');
                },
                error: function(err) {
                    $("#submit").attr("disabled", false)
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        // END CREATE UPDATE DATA AGEN


        // LOAD LOCATION
        $('#province_id,#district_id,#subdistrict_id,#level,#is_active').select2({
            theme: "bootstrap"
        });

        function getProvinces(onDetail = false, province_id = null) {
            $.ajax({
                url: "{{ route('dropdown.province') }}",
                method: "GET",
                header: {
                    "Content-Type": "application/json"
                },
                beforeSend: function() {
                    console.log("Sending data...!")
                },
                success: function(res) {
                    // update input form
                    $("#province_id").empty();
                    $('#province_id').append("<option value =''>Pilih Provinsi</option > ");
                    $.each(res.data, function(index, r) {
                        $('#province_id').append("<option value = '" + r.id + "' > " + r
                            .name + " </option > ");
                    })

                    if (onDetail) {
                        $("#province_id").val(province_id)
                    }
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })
        }

        $("#province_id").change(function() {
            let province_id = $(this).val();
            getDistricts(province_id);
            // reset sub district
            $("#sub_district_id").empty();
            $('#sub_district_id').append("<option value =''>Pilih Kecamatan</option > ");
        })

        function getDistricts(province_id, onDetail = false, district_id = null) {
            $.ajax({
                url: "{{ route('dropdown.district', ['provinceId' => ':provinceId']) }}".replace(':provinceId',
                    province_id),
                method: "GET",
                header: {
                    "Content-Type": "application/json"
                },
                beforeSend: function() {
                    console.log("Sending data...!")
                },
                success: function(res) {
                    // update input form
                    $("#district_id").empty();
                    $('#district_id').append("<option value =''>Pilih Kabupaten</option > ");
                    $.each(res.data, function(index, r) {
                        $('#district_id').append("<option value = '" + r.id + "' > " + r
                            .name + " </option > ");
                    })

                    if (onDetail) {
                        $("#district_id").val(district_id)
                    }
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })
        }

        $("#district_id").change(function() {
            let district_id = $(this).val();
            getSubDistricts(district_id);
        })

        function getSubDistricts(district_id, onDetail = false, sub_district_id = null) {
            $.ajax({
                url: "{{ route('dropdown.subdistrict', ['districtId' => ':districtId']) }}".replace(':districtId',
                    district_id),
                method: "GET",
                header: {
                    "Content-Type": "application/json"
                },
                beforeSend: function() {
                    console.log("Sending data...!")
                },
                success: function(res) {
                    // update input form
                    $("#sub_district_id").empty();
                    $('#sub_district_id').append("<option value =''>Pilih Kecamatan</option > ");
                    $.each(res.data, function(index, r) {
                        $('#sub_district_id').append("<option value = '" + r.id + "' > " + r
                            .name + " </option > ");
                    })

                    if (onDetail) {
                        $("#sub_district_id").val(sub_district_id);
                    }
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
