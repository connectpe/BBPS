@extends('layouts.app')

@section('title', 'Load Money Request')
@section('page-title', 'Load Money Request')
@section('page-button')
<div class="d-flex justify-content-end mb-3">
        <button class="btn buttonColor" id="openRequestModal">
            <i class="bi bi-plus-circle me-1"></i> Request Load Money
        </button>
    </div>
@endsection
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

    {{-- model for raise request --}}

    <div class="modal fade" id="loadMoneyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Load Money Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="loadMoneyRequestForm" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="row g-3">

                             <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control" name="amount" id="reqAmount" required
                                    min="1" placeholder="Enter amount">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="reqStatus" required>
                                    <option value="pending" selected>Pending</option>
                                    <option value="approved">Approved</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">UTR No</label>
                                <input type="text" class="form-control" name="utr_no" id="reqUtr" required
                                    placeholder="Enter UTR no">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="image" id="reqImage"
                                    accept="image/*">
                                <small class="text-muted">Optional</small>
                                <div class="mt-2 d-none" id="imgPreviewWrap">
                                    <img id="imgPreview" src="" alt="preview"
                                        style="max-width: 100%; height: 120px; object-fit: cover; border-radius: 10px;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Remark</label>
                                <input type="text" class="form-control" name="remark" id="reqRemark"
                                    placeholder="Enter remark (optional)">
                            </div>

                            <div class="col-12">
                                <div class="alert alert-danger d-none" id="reqErrBox"></div>
                                <div class="alert alert-success d-none" id="reqOkBox"></div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn buttonColor" id="saveRequestBtn">Submit Request</button>
                    </div>
                </form>

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
                $('#filterAnyKey').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $('#filterStatus').val('');
                table.ajax.reload();
            });

        });
    </script>


    <script>
        const STORE_URL = "";
        $(document).ready(function() {
            $('#openRequestModal').on('click', function() {
                $('#reqErrBox').addClass('d-none').html('');
                $('#reqOkBox').addClass('d-none').html('');
                $('#loadMoneyRequestForm')[0].reset();
                $('#imgPreviewWrap').addClass('d-none');
                $('#imgPreview').attr('src', '');
                $('#loadMoneyModal').modal('show');
            });

            // Image preview
            $('#reqImage').on('change', function() {
                const file = this.files && this.files[0];
                if (!file) {
                    $('#imgPreviewWrap').addClass('d-none');
                    $('#imgPreview').attr('src', '');
                    return;
                }
                const url = URL.createObjectURL(file);
                $('#imgPreview').attr('src', url);
                $('#imgPreviewWrap').removeClass('d-none');
            });
            $('#loadMoneyRequestForm').on('submit', function(e) {
                e.preventDefault();

                $('#saveRequestBtn').prop('disabled', true).text('Saving...');
                $('#reqErrBox').addClass('d-none').html('');
                $('#reqOkBox').addClass('d-none').html('');

                let formData = new FormData(this);

                if (!STORE_URL) {
                    $('#saveRequestBtn').prop('disabled', false).text('Submit Request');
                    $('#reqOkBox').removeClass('d-none').html(
                        'Frontend ready ✅ (User Auth se aayega, backend URL not connected yet)');
                    return;
                }

                $.ajax({
                    url: STORE_URL,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#saveRequestBtn').prop('disabled', false).text('Submit Request');
                        $('#reqOkBox').removeClass('d-none').html(res.message ??
                            'Request saved successfully.');

                        setTimeout(() => {
                            $('#loadMoneyModal').modal('hide');
                            $('#loadMoneyTable').DataTable().ajax.reload(null, false);
                        }, 700);
                    },
                    error: function(xhr) {
                        $('#saveRequestBtn').prop('disabled', false).text('Submit Request');

                        let msg = 'Something went wrong.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                            .message;

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = '';
                            Object.values(xhr.responseJSON.errors).forEach(arr => {
                                msg += arr.join('<br>') + '<br>';
                            });
                        }

                        $('#reqErrBox').removeClass('d-none').html(msg);
                    }
                });
            });

        });
    </script>

@endsection
