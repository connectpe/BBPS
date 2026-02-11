@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-2">
                        <label for="filterDateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>

                    <div class="col-md-2">
                        <label for="filterDateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn buttonColor " id="applyFilter"> Filter</button>
                        <button class="btn btn-secondary" id="resetFilter">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Organization Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Assigned at</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        var table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{url('fetch')}}/support-user-list/0",
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
            buttons: [
                // {
                //     extend: 'excelHtml5',
                //     text: 'Excel',
                //     className: 'btn buttonColor btn-sm'

                // },
                // {
                //     extend: 'pdfHtml5',
                //     text: 'PDF',
                //     className: 'btn buttonColor btn-sm'
                // }
            ],
            language: {
                searchPlaceholder: "Search users..."
            },

            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.settings._iDisplayStart + meta.row + 1;
                    }
                },
                {
                    data: null,
                    render: function(row, type) {
                        let url = "{{ route('view_user', ['id' => ':id']) }}".replace(':id', row.user_id);
                        const businessName = row?.user?.business?.business_name || '----';
                        const userName = row?.user?.name || '----';
                        return `
                                <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                                    ${userName ?? '----'} <br/>
                                    [${businessName ?? '----'}]
                                </a>
                            `;
                    }
                },
                {
                    data: 'user.email'
                },
                {
                    data: 'user.mobile',
                    render: function(data) {
                        return data || '----';
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return formatDateTime(data)
                    }
                }
            ]
        });

        // Apply filter
        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        // Reset filter
        $('#resetFilter').on('click', function() {
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();
        });
    });
</script>

@endsection