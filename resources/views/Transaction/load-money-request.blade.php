@extends('layouts.app')

@section('title', 'Load Money Request')
@section('page-title', 'Load Money Request')

@section('content')

    <div class="accordion mb-3" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFilter">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                    Filter
                </button>
            </h2>

            <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter"
                data-bs-parent="#filterAccordion">
                <div class="accordion-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">User</label>
                            <select class="form-select form-select2" id="filterUsers">
                                <option value="">Select User</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Any Key</label>
                            <input type="text" class="form-control" id="filterAnyKey" placeholder="Enter Search Key">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" id="filterDateFrom">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" id="filterDateTo">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select form-select2" id="filterStatus">
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn buttonColor" id="applyFilter">Search</button>
                            <button class="btn btn-secondary" id="resetFilter">Reset</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="loadMoneyTable" class="table table-striped table-bordered w-100 align-middle">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Requested Id</th>
                            <th>User (Name / Email)</th>
                            <th>Amount</th>
                            <th>UTR No</th>
                            <th>Status</th>
                            <th>Updated By</th>
                            <th>Request Time</th>
                            <th>Image</th>
                            <th>Remark</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            let table = $('#loadMoneyTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Excel',
                        className: 'btn buttonColor btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        className: 'btn buttonColor btn-sm'
                    }
                ],

                ajax: {
                    url: "{{ url('fetch/load-money-requests') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.user_id = $('#filterUsers').val();
                        d.any_key = $('#filterAnyKey').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                        d.status = $('#filterStatus').val();
                    }
                },

                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'requested_id',
                        defaultContent: '----'
                    },

                    {
                        data: null,
                        render: function(row) {
                            let url = "{{ route('view_user', ':id') }}".replace(':id', row.user_id);
                            const userName = row?.user?.name || '----';
                            const email = row?.user?.email || '----';

                            return '<a href="' + url +
                                '" class="text-primary fw-semibold text-decoration-none">' +
                                userName + '<br/>[' + email + ']' +
                                '</a>';
                        }
                    },
                    {
                        data: 'amount',
                        defaultContent: '----'
                    },
                    {
                        data: 'utr_no',
                        defaultContent: '----'
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (!data) return '----';

                            let status = (data + '').toLowerCase();

                            if (status === 'approved') {
                                return `<span class="badge bg-success">Approved</span>`;
                            }

                            if (status === 'pending') {
                                return `<span class="badge bg-danger">Pending</span>`;
                            }

                            return `<span class="badge bg-secondary">${data}</span>`;
                        }
                    },
                    {
                        data: 'updated_by',
                        render: function(data, type, row) {
                            if (row.updated_by) {
                                return row.updated_by.name;
                            }
                            return '----';
                        }
                    },
                    {
                        data: 'request_time',
                         render: function(data) {
                            return formatDateTime(data);
                        }
                    },
                    {
                        data: 'image_url',
                        render: function(data) {
                            if (data) return `<a href="${data}" target="_blank">View</a>`;
                            return '----';
                        }
                    },
                     {
                        data: 'remark',
                        defaultContent: '----'
                     },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return formatDateTime(data);
                        }
                    }
                ]
            });
            $('#applyFilter').on('click', function() {
                table.ajax.reload();
            });
            $('#resetFilter').on('click', function() {
                $('#filterUsers').val('').trigger('change');
                $('#filterAnyKey').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $('#filterStatus').val('');
                table.ajax.reload();
            });

        });
    </script>

@endsection
