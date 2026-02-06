@extends('layouts.app')

@section('title', 'Complaint Report')
@section('page-title', 'Complaint Report')


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
                        <label for="filterUser" class="form-label">User</label>
                        <select name="filterUser" id="filterUser" class="form-control">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="ticketId" class="form-label">Ticket Number</label>
                        <input type="text" class="form-control" id="ticketId" placeholder="Ex: #47618678714">
                    </div>

                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">--Select Status--</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-center">
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
                <table id="complaintTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Organization Name</th>
                            <th>Ticket Number</th>
                            <th>Service</th>
                            <th>Complaint Category</th>
                            <th>Priority</th>
                            <th>Remark</th>
                            <th>Resolved At</th>
                            <th>Attatched File</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- Update Modal --}}
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">Update Complaint: <span id="ticket_number"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select id="updateStatus" class="form-select">
                        <option value="">--Select Status--</option>
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <input type="hidden" id="complaintId">

                <div class="mb-3">
                    <label class="form-label">Remark</label>
                    <textarea id="remark" class="form-control" rows="3" placeholder="Enter Remark"></textarea>
                </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn buttonColor" id="saveUpdate">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateComplaint(id, status, ticketNumber, remark) {
        $('#complaintId').val(id);
        $('#updateStatus').val(status);
        $('#remark').val(remark);
        $('#ticket_number').text(ticketNumber);
        $('#updateModal').modal('show');
    }

    $(document).ready(function() {
        $('#saveUpdate').on('click', function() {
            const id = $('#complaintId').val();
            const status = $('#updateStatus').val();
            const remark = $('#remark').val();
            let url = "{{ route('update_complaint_report', ['id' => ':id']) }}";
            url = url.replace(':id', id);

            // Ask confirmation first
            Swal.fire({
                icon: 'question',
                title: 'Are you sure?',
                text: 'Do you want to update this complaint?',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            status: status,
                            remark: remark
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message || 'Complaint updated successfully!',
                                timer: 1800,
                                showConfirmButton: false
                            });
                            $('#updateModal').modal('hide');

                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        },
                        error: function(xhr) {
                            let message = 'Something went wrong!';
                            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                                const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                message = xhr.responseJSON.errors[firstKey][0];
                            } else if (xhr.responseJSON?.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message,
                                showConfirmButton: true
                            });
                        }
                    });
                }
            });
        });
    });
</script>

<!-- DataTable  -->

<script>
    $(document).ready(function() {

        var table = $('#complaintTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {

                url: "{{url('fetch')}}/transaction-complaint/0",
                type: 'POST',

                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.ticket_number = $('#ticketId').val();
                    d.status = $('#filterStatus').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                    d.user_id = $("#filterUser").val();
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
                searchPlaceholder: "Search Complaints..."
            },
            columns: [{
                    data: 'id'
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
                    data: 'ticket_number'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return row?.service?.service_name || '----';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return row?.category?.category_name || '----';
                    }
                },
                {
                    data: 'priority',
                    render: function(data, row, type) {
                        const status = data == 'Low' ? 'success' : (data == 'High' ? 'danger' : 'warning');
                        return `<span class="text-${status} fw-bold">${data}</span>`
                    }
                },
                {
                    data: 'remark',
                    render: function(data) {
                        const safeText = data || '';
                        return `<i class="fas fa-eye cursor-pointer viewModalBtn"
                                    data-title="Remark"
                                    data-content="${safeText.replace(/"/g, '&quot;')}">
                                </i>`;
                    }
                },
                {
                    data: 'resolved_at',
                    render: function(data) {
                        return formatDateTime(data)
                    }
                },
                {
                    data: 'attachment_file',
                    render: function(data, type) {
                        if (type !== 'display' || !data) {
                            return '----';
                        }
                        const src = "{{ asset('storage/') }}/" + data;
                        return `
                            <i class="fas fa-eye cursor-pointer"
                            onclick="showImage('${src}', 'Attachment File')">
                            </i>
                        `;
                    }
                },
                {
                    data: 'status',
                    render: function(data, type, row) {

                        if (type !== 'display') {
                            return data;
                        }

                        // Show text only for Closed & Resolved
                        if (data === 'Closed') {
                            return `<span class="text-danger fw-semibold">${data}</span>`;
                        }

                        if (data === 'Resolved') {
                            return `<span class="text-success fw-semibold">${data}</span>`;
                        }

                        // Status options for dropdown
                        const statusOptions = [
                            'Open',
                            'In Progress',
                            'Closed',
                            'Resolved'
                        ];

                        const remarkEscaped = escapeHtml(row.remark || '');

                        let dropdown = `<select class="form-select form-select-sm"
                            data-id="${row.id}"
                            data-status="${escapeHtml(row.status)}"
                            data-ticket="${escapeHtml(row.ticket_number)}"
                            data-remark="${remarkEscaped}">`;

                        statusOptions.forEach(status => {
                            let selected = data === status ? 'selected' : '';
                            dropdown += `<option value="${escapeHtml(status)}" ${selected}>${escapeHtml(status)}</option>`;
                        });

                        dropdown += `</select>`;


                        return dropdown;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'description',
                    render: function(data) {
                        const safeText = data || '';
                        return `<i class="fas fa-eye cursor-pointer viewModalBtn"
                                    data-title="Description"
                                    data-content="${safeText.replace(/"/g, '&quot;')}">
                                </i>`;
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return formatDateTime(data);
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
            $('#ticketId').val('');
            $('#filterStatus').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $("#filterUser").val('')
            table.ajax.reload();
        });

    });


    $('#complaintTable').on('change', 'select.form-select-sm', function() {
        const id = $(this).data('id');
        const status = $(this).val(); // get the selected status
        const ticket = $(this).data('ticket');
        const remark = $(this).data('remark');

        updateComplaint(id, status, ticket, remark);
    });


    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '&#10;'); // replace newlines
    }
</script>

@endsection