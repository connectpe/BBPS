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
                            <option value="rejected">Rejected</option>
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
                        <th>User (Name / Email)</th>
                        <th>Requested Id</th>
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

<!-- Load Money Request Approve/Reject -->

<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title"><span id="requestId"></span>: (<span id="amount"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select id="updateStatus" class="form-select">
                        <option value="">--Select Status--</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <input type="hidden" id="id">

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

<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalPreviewImage" class="img-fluid rounded" alt="Preview">
            </div>
        </div>
    </div>
</div>

<script>


    $('#loadMoneyTable').on('change', '.statusDropdown', function () {
        const id = $(this).data('id');
        const status = $(this).val(); // get the selected status
        const requestId = $(this).data('requestid');
        const amount = $(this).data('amount');
        loadMoneyRequest(id, status, requestId, amount);
    });

    function loadMoneyRequest(id, status, requestId, amount = 0) {
        $('#id').val(id);
        $('#updateStatus').val(status);
        $('#requestId').text(requestId);
        $('#amount').text(`₹${amount}`);
        $('#updateModal').modal('show');
    }

    $(document).ready(function () {
        $('#saveUpdate').on('click', function () {
            const id = $('#id').val();
            const status = $('#updateStatus').val();
            const remark = $('#remark').val();
            let url = "{{ route('update_load_money_request') }}";

            // Ask confirmation first
            Swal.fire({
                icon: 'question',
                title: 'Are you sure?',
                text: 'Do you want to update this request?',
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
                            id: id,
                            status: status,
                            remark: remark
                        },
                        success: function (res) {
                            if (res.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: res.message || 'Request updated successfully!',
                                    showConfirmButton: true
                                });
                                $('#updateModal').modal('hide');

                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: res.message || 'Error',
                                    showConfirmButton: true
                                });
                            }

                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        },
                        error: function (xhr) {
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

<script>
    $(document).ready(function () {

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
                data: function (d) {
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
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },

            {
                data: null,
                render: function (row) {
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
                data: 'request_id',
                defaultContent: '----'
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
                render: function (data, type, row) {

                    if (type !== 'display') {
                        return data;
                    }

                    // Show text only for Closed & Resolved
                    if (data === 'approved') {
                        return `<span class="badge bg-success">Approved</span>`;
                    }

                    if (data === 'rejected') {
                        return `<span class="badge bg-danger">Rejected</span>`;
                    }

                    // Status options for dropdown
                    const statusOptions = [
                        'pending',
                        'approved',
                        'rejected',
                    ];

                    let dropdown = `<select class="form-select statusDropdown"
                            data-id="${row.id}"
                            data-status="${row.status}"
                            data-requestId="${row.request_id}"
                            data-amount="${row.amount}">`;

                    statusOptions.forEach(status => {
                        let selected = data === status ? 'selected' : '';
                        dropdown += `<option value="${status}" ${selected}>${status}</option>`;
                    });

                    dropdown += `</select>`;


                    return dropdown;
                },
                orderable: false,
                searchable: false
            },
            {
                data: 'updated_by',
                render: function (data, type, row) {
                    if (row.updated_by) {
                        return row.updated_by.name;
                    }
                    return '----';
                }
            },
            {
                data: 'request_time',
                render: function (data) {
                    return formatDateTime(data);
                }
            },
            {
                data: 'image_url',
                render: function (data) {
                    if (data) {
                        return `
                <div class="d-flex align-items-center gap-3">
                    
                    <i class="fas fa-eye view-image-btn fs-5" 
                       data-url="${data}" 
                       style="cursor: pointer; color: #000;" 
                       title="Quick View"></i>
                </div>`;
                    }
                    return '<span class="text-muted">No Image</span>';
                }
            },
            {
                data: 'remark',
                render: function (data) {
                    const safeText = data || '';
                    return `<i class="fas fa-eye cursor-pointer fs-5 viewModalBtn"
                                    data-title="Remark"
                                    data-content="${safeText.replace(/"/g, '&quot;')}">
                                </i>`;
                }
            },
            {
                data: 'created_at',
                render: function (data) {
                    return formatDateTime(data);
                }
            }
            ]
        });
        $('#applyFilter').on('click', function () {
            table.ajax.reload();
        });
        $('#resetFilter').on('click', function () {
            $('#filterUsers').val('').trigger('change');
            $('#filterAnyKey').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#filterStatus').val('');
            table.ajax.reload();
        });

    });

</script>

<script>
    $(document).on('click', '.view-image-btn', function () {
        let imageUrl = $(this).data('url');
        $('#modalPreviewImage').attr('src', imageUrl);
        var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        myModal.show();
    });
</script>

@endsection