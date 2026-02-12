@extends('layouts.app')

@section('title', 'Transaction Complaint')
@section('page-title', 'Transaction Complaint')


@section('page-button')
<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <button type="button" class="btn buttonColor text-nowrap" data-bs-toggle="modal" data-bs-target="#serviceModal">
            <i class="bi bi-plus fs-6 me-1"></i> Complaint
        </button>
    </div>
</div>
@endsection

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
                        <label for="ticketId" class="form-label">Ticket Number</label>
                        <input type="text" class="form-control" id="ticketId" placeholder="Ex: #47618678714">
                    </div>

                    <div class="col-md-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="mobile" placeholder="Mobile Number">
                    </div>

                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select form-select2" id="filterStatus">
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
                            <th>Ticket Number</th>
                            <th>Service</th>
                            <th>Complaint Category</th>
                            <th>Reference No.</th>
                            <th>Date</th>
                            <th>Mobile</th>
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


<!-- Register Complaints Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- <div class="modal-header">
                <h5 class="modal-title">Register Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div> -->

            <div class="modal-header position-relative overflow-visible">
                <h5 class="modal-title" id="modalTitle">
                    Register Complaint
                </h5>

                <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}"
                    alt=""
                    class="position-absolute"
                    style="top: 10px; right: 50px; width: 70px; z-index: 1060;">

                <button type="button"
                    class="btn-close position-absolute bg-light"
                    data-bs-dismiss="modal"
                    style="top: -15px; right: -15px; z-index: 1061;">
                </button>
            </div>


            <div class="modal-body">
                <form id="complaintForm" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-5">
                            <label class="form-label">TXN Reference Id.</label>
                            <input type="text" name="reference_id" id="reference_id" class="form-control" placeholder="TXN Reference Id">
                            <small class="text-danger d-none" id="err_reference_id"></small>
                        </div>

                        <div class="col-md-2 text-center">
                            <label class="form-label">OR</label>
                            <!-- <input type="hidden" class="form-control"> -->
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Mobile</label>
                            <input type="number" name="mobile" id="mobile" class="form-control" placeholder="Enter Mobile">
                            <small class="text-danger d-none" id="err_mobile"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">TXN Date</label>
                            <input type="date" name="txn_date" id="txn_date" class="form-control">
                            <small class="text-danger d-none" id="err_txn_date"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Service Name<span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select form-select2" required>
                                <option value="">-- Select Service --</option>
                                @foreach ($services as $service)
                                <option value="{{ $service?->service?->id }}">
                                    {{ $service->service?->service_name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none" id="err_service_id"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select form-select2" required>
                                @foreach ($priorities as $p)
                                <option value="{{ $p }}" {{ $p == 'normal' ? 'selected' : '' }}>
                                    {{ ucfirst($p) }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none" id="err_priority"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category<span class="text-danger">*</span></label>
                            <select name="category" class="form-select form-select2">
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $value)
                                <option value="{{ $value->id }}">{{ $value->category_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none" id="err_category"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Attachment </label>
                            <input type="file" name="attachment" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-danger d-none" id="err_attachment"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description<span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Write complaint..." required min="20"></textarea>
                            <small class="text-danger d-none" id="err_description"></small>
                        </div>


                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn buttonColor">
                                Register
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>

                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</div>



<script>
    function resetErrors() {
        $('small[id^="err_"]').addClass('d-none').text('');
    }

    $('#complaintForm').on('submit', function(e) {
        e.preventDefault();
        resetErrors();

        const form = document.getElementById('complaintForm');
        const formData = new FormData(form);

        $.ajax({
            url: "{{ route('complaints.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message || 'Complaint registered successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });

                setTimeout(() => {
                    location.reload();
                }, 2000);
            },

            error: function(xhr) {
                let msg = 'Something went wrong!';
                if (xhr.status === 422) msg = 'Validation error! Please check fields.';

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.service_name) $('#err_service_name').removeClass('d-none').text(
                        errors.service_name[0]);
                    if (errors.description) $('#err_description').removeClass('d-none').text(errors
                        .description[0]);
                    if (errors.priority) $('#err_priority').removeClass('d-none').text(errors
                        .priority[0]);
                    if (errors.category) $('#err_category').removeClass('d-none').text(errors
                        .category[0]);
                    if (errors.attachment) $('#err_attachment').removeClass('d-none').text(errors
                        .attachment[0]);
                }
            }
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
                    d.mobile_number = $('#mobile').val();
                    d.status = $('#filterStatus').val();
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
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.settings._iDisplayStart + meta.row + 1;
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
                    data: 'payment_ref_id',
                    render: function(data, type, row) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'transaction_date',
                    render: function(data) {
                        return formatDate(data)
                    }
                },
                {
                    data: 'mobile_number',
                    render: function(data, type, row) {
                        return data ? data : '-';
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
                    render: function(data, type) {
                        if (type !== 'display') {
                            return data;
                        }

                        let colorClass = 'secondary';

                        switch (data) {
                            case 'Open':
                                colorClass = 'success';
                                break;
                            case 'Closed':
                                colorClass = 'danger';
                                break;
                            case 'In Progress':
                                colorClass = 'warning';
                                break;
                        }

                        return `<span class="badge bg-${colorClass}">${data}</span>`;
                    }
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
            $('#mobile').val('');
            $('#filterStatus').val('').trigger('change');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();
        });


    });
</script>

@endsection