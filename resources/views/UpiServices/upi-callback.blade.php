@extends('layouts.app')

@section('title', 'Upi Callback')
@section('page-title', 'Upi Callback')

@section('content')

    <!-- FILTER -->
    <div class="accordion mb-3" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter">
                    Filter
                </button>
            </h2>

            <div id="collapseFilter" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-2">
                            <label>Any Key</label>
                            <input type="text" id="filterKeyword" class="form-control" placeholder="Txn ID / UTR">
                        </div>

                        <div class="col-md-2">
                            <label>Status</label>
                            <input type="text" id="filterStatus" class="form-control" placeholder="Status">
                        </div>

                        <div class="col-md-2">
                            <label>From Date</label>
                            <input type="date" id="filterDateFrom" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>To Date</label>
                            <input type="date" id="filterDateTo" class="form-control">
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn buttonColor w-100" id="applyFilter">Filter</button>
                            <button class="btn btn-secondary w-100" id="resetFilter">Reset</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="callbackTable" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Txn ID</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>UTR</th>
                            <th>Root</th>
                            <th>Response</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>


    <div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Response Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <pre id="responseData" style="max-height:400px; overflow:auto;"></pre>
            </div>

        </div>
    </div>
</div>
    <script>
        $(document).ready(function() {

            var table = $('#callbackTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('fetch/upi-callback') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.any_key = $('#filterKeyword').val();
                        d.status = $('#filterStatus').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'txn_id'
                    },
                    {
                        data: 'txn_order_id'
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'utr'
                    },
                    {
                        data: 'root',
                        render: function(data) {
                            return '<span class="badge bg-success">' + (data ?? '-') + '</span>';
                        }
                    },
                    {
                        data: 'response',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            let formatted = data ? JSON.stringify(data, null, 2) : '-';

                            return ` <button class="btn btn-sm text-dark view-response" data-response='${encodeURIComponent(formatted)}'> <i class="fas fa-eye"></i> </button>`;
                        }
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'message'
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return formatDateTime(data);
                        }
                    }
                ],
                order: [
                    [0, 'DESC']
                ]
            });

            // APPLY FILTER
            $('#applyFilter').click(function() {
                table.draw();
            });

            // RESET FILTER
            $('#resetFilter').click(function() {
                $('#filterKeyword').val('');
                $('#filterStatus').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.draw();
            });

        });
    </script>

    <script>
        $(document).on('click', '.view-response', function () {
    let response = decodeURIComponent($(this).data('response'));

    $('#responseData').text(response);
    $('#responseModal').modal('show');
});
    </script>

@endsection
