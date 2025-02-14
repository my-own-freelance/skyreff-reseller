@extends('layouts.dashboard')
@section('title', $title)
@section('content')
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">Stok Akrab</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="tableAkrab">
                            <thead>
                                <tr>
                                    <th class="all">No</th>
                                    <th class="all">Produk</th>
                                    <th class="all">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let dTable = null;

        $(function() {
            getData();
        })

        function getData() {
            $.ajax({
                url: "{{ route('akrab.list') }}",
                method: 'GET',
                dataType: "json",
                beforeSend: () => {
                    console.log('Loading...')
                },
                success: (msg) => {
                    renderTable(msg.data);
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }


        function renderTable(data) {
            let tableElement = $("#tableAkrab");
            let tBody = ""
            data.forEach((d, idx) => {
                tBody += `<tr>`
                tBody += `<td>${idx + 1}</td>`
                tBody += `<td>${d.name}</td>`
                tBody += `<td>${d.stock}</td>`
                tBody += `</tr>`
            })

            if (data.length <= 0) {
                tBody += `<td colspan="3" class="text-center"><small>Tidak ada data akrab</small></td>`
            }
            tableElement.find("tbody").html(tBody)
        }

        function refreshData() {
            renderTable([]);
            getData()
        }
    </script>
@endpush
