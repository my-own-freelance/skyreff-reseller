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
                        <h5 class="text-uppercase title">List Reward</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah Data</button>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="rewardDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Gambar</th>
                                    <th class="all">Judul</th>
                                    <th class="all">Type</th>
                                    <th class="all">Stock</th>
                                    <th class="all">Durasi</th>
                                    <th class="all">Status</th>
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
                            <label for="title">Judul</label>
                            <input class="form-control" id="title" type="text" name="title"
                                placeholder="masukkan judul" required />
                        </div>
                        <div class="form-group">
                            <label for="qty">Qty</label>
                            <input class="form-control" id="qty" type="number" name="qty"
                                placeholder="masukkan jumlah reward" min='0' required />
                        </div>
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control form-control" id="is_active" name="is_active" required>
                                <option value= "">Pilih Status</option>
                                <option value="Y">Publish</option>
                                <option value="N">Draft</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type">Tipe</label>
                            <select class="form-control form-control" id="type" name="type" required>
                                <option value="">Pilih Tipe</option>
                                <option value="G">Global</option>
                                <option value="V">VIP</option>
                            </select>
                        </div>
                        <div id="fResellerList" class="form-group" style="display: none">
                            <label for="reseller_list">Reseller <span class="text-muted">(pilih yg bisa claim
                                    reward)</span></label>
                            <div class="select2-input">
                                <select id="reseller_list" name="reseller_list[]" class="form-control" multiple="multiple">
                                    <option value="">Pilih Reseller</option>
                                    @foreach ($resellers as $reseller)
                                        <option value = "{{ $reseller->id }}">{{ $reseller->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="datetime-local" id="start_date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Tanggal Akhir</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="image">Gambar</label>
                            <input class="form-control" id="image" type="file" name="image"
                                placeholder="upload gambar" />
                            <small class="text-danger">Max ukuran 2MB</small>
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
@endsection
@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>
        let dTable = null;

        $('#reseller_list').select2({
            theme: "bootstrap"
        });

        $(function() {
            dataTable();
        })

        function dataTable() {
            const url = "{{ route('reward.datatable') }}";
            dTable = $("#rewardDataTable").DataTable({
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
                    data: "type"
                }, {
                    data: "stock"
                }, {
                    data: "duration"
                }, {
                    data: "is_active"
                }],
                pageLength: 10,
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
        }


        function addData() {
            $("#formEditable").attr('data-action', 'add').fadeIn(200, function() {
                $("#reset").click();
                $("#image").attr("required", true);
                $("#title").focus();
                $('#fResellerList').slideUp()
            });
            $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12");
                $("#reset").click();
            })
        }

        function getData(id) {
            $.ajax({
                url: "{{ route('reward.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
                        let d = res.data;
                        $("#id").val(d.id);
                        $("#title").val(d.title);
                        $("#qty").val(d.qty);
                        $("#is_active").val(d.is_active).change();
                        $("#type").val(d.type).change();
                        if (d.type == 'V') {
                            $('#fResellerList').fadeIn()
                            $("#reseller_list").val(JSON.parse(d.reseller_list)).change();
                        }
                        $("#start_date").val(d.start_date);
                        $("#end_date").val(d.end_date);
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

        $("#formEditable form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("title", $("#title").val());
            formData.append('qty', $("#qty").val());
            formData.append("is_active", $("#is_active").val());
            formData.append('type', $("#type").val());
            formData.append('start_date', $("#start_date").val());
            formData.append('end_date', $("#end_date").val());
            if ($("#type").val() == "V") {
                formData.append("reseller_list", JSON.stringify($("#reseller_list").val()));
            }

            formData.append("image", document.getElementById("image").files[0]);
            console.log("reseller :", $("#reseller_list").val());
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
                url: action == "update" ? "{{ route('reward.update') }}" : "{{ route('reward.create') }}",
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
                    url: "{{ route('reward.destroy') }}",
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
                url: "{{ route('reward.change-status') }}",
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

        $('#type').change(function() {
            let type = $(this).val();

            if (type == 'V') {
                $('#fResellerList').fadeIn()
            } else {
                $('#fResellerList').slideUp()
            }
        })
    </script>
@endpush
