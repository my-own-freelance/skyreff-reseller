<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <h5 class="text-uppercase title">Reseller VIP</h5>
        </div>
        <div class="card-header-right">
            <button class="btn btn-mini btn-info mr-1" onclick="return refreshData('vip');">Refresh</button>
        </div>
        <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilterVip">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="fis_active">Filter Status</label>
                        <select class="form-control" id="fis_active" name="is_active">
                            <option value="">All</option>
                            <option value="Y">Aktif</option>
                            <option value="N">Disable</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="pt-3">
                        <button class="mt-4 btn btn-sm btn-success mr-3" type="button"
                            onclick="filterData('vip')">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-block">
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered nowrap dataTable" id="vipDataTable">
                <thead>
                    <tr>
                        <th class="all">#</th>
                        <th class="all">Name</th>
                        <th class="all">Username</th>
                        <th class="all">Kode</th>
                        <th class="all">Phone</th>
                        <th class="">Saldo</th>
                        <th class="">Limit Piutang</th>
                        <th class="">Total Piutang</th>
                        <th class="">Komisi</th>
                        <th class="all">Status</th>
                        <th class="">Alamat</th>
                        <th class="">Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="11" class="text-center"><small>Tidak Ada Data</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
