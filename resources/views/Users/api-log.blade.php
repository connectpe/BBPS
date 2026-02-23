@extends('layouts.app')

@section('title', 'API Log')
@section('page-title', 'API Log')

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
                        <label for="filterName" class="form-label">User</label>
                        <select name="filterName" id="filterName" class="form-control form-select2">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}} ({{ $value->email }})</option>
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
                            <th>Location Details</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Api response Modal -->
<!-- API Response Modal -->
<div class="modal fade" id="apiResponseModal" tabindex="-1" aria-labelledby="apiResponseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apiResponseModalLabel">Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"
                style="white-space: pre-wrap; font-family: monospace; max-height: 70vh; overflow-y: auto;">
                <!-- API response will appear here -->
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {

        var table = $('#logTable').DataTable({
            processing: true,
            serverSide: true,
            orderable: false,
            searchable: false,
            ajax: {
                url: "{{url('fetch')}}/api-logs/0",
                type: 'POST',
                data: function (d) {
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
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            {
                data: function (row) {
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
                data: 'method',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'endpoint',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: function (row) {
                    const content = typeof row.request_body === 'object' ? JSON.stringify(row.request_body, null, 4) : row.request_body;
                    return `<i class="fas fa-eye cursor-pointer viewContent"
                                    data-title="API Response Body"
                                    data-content='${content.replace(/'/g, "&#39;")}'>
                                </i>`;
                }
            },
            {
                data: function (row) {
                    const content = typeof row.response_body === 'object' ? JSON.stringify(row.response_body, null, 4) : row.response_body;
                    return `<i class="fas fa-eye cursor-pointer viewContent"
                                    data-title="API Response Body"
                                    data-content='${content.replace(/'/g, "&#39;")}'>
                                </i>`;
                }
            },
            {
                data: 'status_code',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'ip_address',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: function (row) {
                    const content = typeof row.user_agent === 'object' ? JSON.stringify(row.user_agent, null, 4) : row.user_agent;
                    return `<i class="fas fa-eye cursor-pointer viewContent"
                                    data-title=""
                                    data-content='${content.replace(/'/g, "&#39;")}'>
                                </i>`;
                }
            },
            {
                data: 'execution_time',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: function (row) {
                    return formatDateTime(row.created_at)
                }
            },
            {
                data: function (row) {
                    const content = typeof row.location_details === 'object' ? JSON.stringify(row.location_details, null, 4) : row.location_details;
                    return `<i class="fas fa-eye cursor-pointer viewContent"
                                    data-title="API Response Body"
                                    data-content='${content.replace(/'/g, "&#39;")}'>
                                </i>`;
                }
            },
            ]
        });
        $('#date_from').on('change', function () {
            let from = $(this).val();
            $('#date_to').attr('min', from);
            if ($('#date_to').val() && $('#date_to').val() < from) {
                $('#date_to').val('');
            }
        });


        // Apply filter
        $('#applyFilter').on('click', function () {
            table.ajax.reload();
        });

        // Reset filter
        $('#resetFilter').on('click', function () {
            $('#filterName').val('').trigger('change');
            $('#date_from').val('');
            $('#date_to').val('');
            $('#filterDateTo').val('').removeAttr('min');
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
                success: function (response) {
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
                error: function (xhr) {
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

    function showApiResponse(response) {
        const $modal = $('#apiResponseModal');

        if (!$modal.length) return;

        // If response is an object/array, pretty-print JSON
        let text = response;
        if (typeof response === 'object') {
            text = JSON.stringify(response, null, 4);
        }

        $modal.find('.modal-body').text(text); // use .text() to render safely
        $modal.modal('show');
    }

    $(document).on('click', '.viewContent', function () {
        const content = $(this).data('content');
        showApiResponse(content);
    });
</script>

@endsection