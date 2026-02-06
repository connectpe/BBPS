@extends('layouts.app')

@section('title', 'API Log')
@section('page-title', 'API Log')

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

                    <div class="col-md-3">
                        <label for="filterName" class="form-label">User</label>
                        <select name="filterName" id="filterName" class="form-control">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From</label>
                        <input type="date" class="form-control" id="date_from">
                    </div>

                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To</label>
                        <input type="date" class="form-control" id="date_to">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn buttonColor " id="applyFilter"> Filter</button>
                        <button class="btn btn-secondary" id="resetFilter">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-md-10 col-lg-12">
    <div class="card shadow-sm">

        <div class="card-body pt-4">
            <!-- Table -->
            <div class="table-responsive">
                <table id="logTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Organization Name</th>
                            <th>Method</th>
                            <th>EndPoint</th>
                            <th>Request Body</th>
                            <th>Response Body</th>
                            <th>Status Code</th>
                            <th>IP</th>
                            <th>User Agent</th>
                            <th>Execution time</th>
                            <th>Date Time</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        var table = $('#logTable').DataTable({
            processing: true,
            serverSide: true,
            orderable: false,
            searchable: false,
            ajax: {
                url: "{{url('fetch')}}/api-logs/0",
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.user_id = $('#filterName').val();
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                }
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
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
            language: {
                searchPlaceholder: "Search Activity..."
            },

            columns: [{
                    data: 'id'
                },
                {
                    data: function(row) {
                         const userName = row.user?.name || '----';
                        const businessName = row.user?.business?.business_name || '----';
                        const url = "{{ route('view_user', ['id' => 'id']) }}".replace('id', row.user_id);
                        return `
                                <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                                    ${userName ?? '----'} <br/>
                                    [${businessName ?? '----'}]
                                </a>
                            `;
                    }
                },
                {
                    data: 'method'
                },
                {
                    data: 'endpoint'
                },
                {
                    data: function(row) {
                        return `<i class="fas fa-eye cursor-pointer viewModalBtn"
                        data-title="API Request Body"
                        data-content='${JSON.stringify(row.request_body)}'></i>`;
                    }
                },
                {
                    data: function(row) {
                        return `<i class="fas fa-eye cursor-pointer viewModalBtn"
                        data-title="API Response Body"
                        data-content='${JSON.stringify(row.response_body)}'></i>`;
                    }
                },
                {
                    data: 'status_code'
                },
                {
                    data: 'ip_address'
                },
                {
                    data: function(row) {
                        return `<i class="fas fa-eye cursor-pointer viewModalBtn"
                        data-title="User Agent"
                        data-content='${JSON.stringify(row.user_agent)}'></i>`;
                    }
                },
                {
                    data: 'execution_time'
                },
                {
                    data: function(row) {
                        return formatDateTime(row.created_at)
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
            $('#filterName').val('');
            $('#date_from').val('');
            $('#date_to').val('');
            table.ajax.reload();
        });
    });


    function changeStatusDropdown(selectElement, id) {
        const newStatus = selectElement.value;
        const prevStatus = selectElement.getAttribute('data-prev');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to change the status?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (!result.isConfirmed) {
                selectElement.value = prevStatus;
                return;
            }

            $.ajax({
                url: "{{ route('admin.user_status.change') }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: newStatus,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message ?? 'Status updated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    // update previous value after success
                    selectElement.setAttribute('data-prev', newStatus);
                },
                error: function(xhr) {
                    // rollback on error
                    selectElement.value = prevStatus;

                    let message = 'Something went wrong!';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                        message = xhr.responseJSON.errors[firstKey][0];
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });
    }
</script>

@endsection