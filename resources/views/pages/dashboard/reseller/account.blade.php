@extends('layouts.dashboard')
@section('title', $title)
@section('content')
    <div class="row mb-5">
        <div class="col-md-6" id="boxTable">
            <div class="card card-with-nav">
                <div class="card-header">
                    <div class="card-header-left my-3">
                        <h5 class="text-uppercase title">Management Account</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form id="formCountInformation">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="avatar avatar-xxl mb-3" id="imageProfile">
                                    <img src="{{ asset('dashboard/img/jm_denis.jpg') }}" alt="..."
                                        class="avatar-img rounded-circle">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card card-primary">
                                    <div class="card-body skew-shadow">
                                        <h5 class="mt-3">Total Komisi : <span id="totalComission"></span></h5>
                                        <h5 class="mt-3">Limit Hutang : <span id="debtLimit"></span></h5>
                                        <h5 class="mt-3">Total Hutang : <span id="totalDebt"></span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="id" id="id">
                        <div class="tab-pane active" id="countinformation" (role="tabpanel")>
                            <div class="form-group form-group-default">
                                <label>Nama</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="nama">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Username</label>
                                <input type="text" class="form-control" id="username" name="username" disabled
                                    placeholder="username">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Kode Reseller</label>
                                <input type="text" class="form-control" id="code" name="code" disabled
                                    placeholder="kode">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Password</label>
                                <input type="text" class="form-control" id="password" name="password"
                                    placeholder="ubah password">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Nomor Telpon</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number"
                                    placeholder="nomor telpon">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Nama Bank</label>
                                <input type="text" class="form-control" id="bank_type" name="bank_type"
                                    placeholder="nama Bank">
                            </div>
                            <div class="form-group form-group-default">
                                <label>No Rekening</label>
                                <input type="text" class="form-control" id="bank_account" name="bank_account"
                                    placeholder="nomor rekening">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Foto Pengguna</label>
                                <input class="form-control" id="image" type="file" name="image"
                                    placeholder="upload gambar" />
                                <small class="text-danger">Max ukuran 1MB</small>
                            </div>
                            <div class="form-group form-group-default">
                                <label for="province_id">Provinsi</label>
                                <select class="form-control form-control" id="province_id" name="province_id" required>
                                    <option value = "">Pilih Provinsi</option>
                                </select>
                            </div>
                            <div class="form-group form-group-default">
                                <label for="district_id">Kabupaten</label>
                                <select class="form-control form-control" id="district_id" name="district_id" required>
                                    <option value = "">Pilih Kabupaten</option>
                                </select>
                            </div>
                            <div class="form-group form-group-default">
                                <label for="sub_district_id">Kecamatan</label>
                                <select class="form-control form-control" id="sub_district_id" name="sub_district_id"
                                    required>
                                    <option value = "">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="form-group form-group-default">
                                <label for="address">Alamat</label>
                                <input class="form-control" id="address" type="text" name="address"
                                    placeholder="masukan alamat" required />
                            </div>
                        </div>
                        <div class="text-right mt-3 mb-3">
                            <button class="btn btn-success" type="submit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        $('#gender,#province_id,#district_id,#sub_district_id').select2({
            theme: "bootstrap"
        });

        $(function() {
            getData()
        })

        $("#formCountInformation").submit(function(e) {
            e.preventDefault()

            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("name", $("#name").val());
            formData.append("username", $("#username").val());
            formData.append("phone_number", $("#phone_number").val());
            formData.append("password", $("#password").val());
            formData.append("bank_type", $("#bank_type").val());
            formData.append("bank_account", $("#bank_account").val());
            formData.append("image", document.getElementById("image").files[0]);
            formData.append("province_id", parseInt($("#province_id").val()));
            formData.append("district_id", parseInt($("#district_id").val()));
            formData.append("sub_district_id", parseInt($("#sub_district_id").val()));
            formData.append("address", $("#address").val());

            update(formData);
            return false;
        });

        function getData() {
            $.ajax({
                url: "{{ route('reseller.detail-account') }}",
                dataType: "json",
                success: function(data) {
                    let d = data.data;
                    $("#totalComission").html(convertToRupiah(d.commission));
                    $("#debtLimit").html(convertToRupiah(d.debt_limit));
                    $("#totalDebt").html(convertToRupiah(d.total_debt));
                    $("#id").val(d.id);
                    $("#name").val(d.name);
                    $("#username").val(d.username);
                    $("#code").val(d.code);
                    $("#phone_number").val(d.phone_number);
                    $("#bank_type").val(d.bank_type);
                    $("#bank_account").val(d.bank_account);

                    $("#address").val(d.address);

                    if (d.image) {
                        $('#imageProfile img').attr('src', d.image);
                    }

                    if (d.province_id && d.district_id && d.sub_district_id) {
                        getProvinces(true, d.province_id);
                        getDistricts(d.province_id, true, d.district_id);
                        getSubDistricts(d.district_id, true, d.sub_district_id);
                    } else {
                        getProvinces();
                    }
                },
                error: function(err) {
                    console.log("error :", err)
                }

            })
        }

        function update(data) {
            $.ajax({
                url: "{{ route('reseller.update-account') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    setTimeout(()=>{
                        window.location.reload()
                    }, 2000);
                },
                error: function(err) {
                    console.log("error :", err)
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message)
                }
            })
        }

        function getProvinces(onDetail = false, province_id = null) {
            $.ajax({
                url: `/api/dropdown/location/provinces`,
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
                url: `/api/dropdown/location/districts/${province_id}`,
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
                url: `/api/dropdown/location/sub-districts/${district_id}`,
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
