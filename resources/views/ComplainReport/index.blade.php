@extends('layouts.app')

@section('title', 'Complaint Report')
@section('page-title', 'Complaint Report')

<style>
    .main-wrapper {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
        width: 100%;
    }

    .table-scroll-wrapper {
        width: 100%;
        overflow-x: auto !important;
        background: #fff;
        margin-top: 10px;
    }

    #complaintTable {
        width: 100% !important;
        margin: 0 !important;
        min-width: 1300px;
    }

    #complaintTable th {
        background-color: #f8f9fc !important;
        white-space: nowrap;
        font-weight: 600;
        border-bottom: 2px solid #e3e6f0 !important;
    }

    #complaintTable td {
        white-space: nowrap;
        padding: 12px 15px !important;
        vertical-align: middle;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 4px;
    }

    .view-description {
        color: #4e73df;
        cursor: pointer;
        font-size: 1.1rem;
    }
</style>

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="accordion mb-3" id="filterAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFilter">
                                <i class="fa-solid fa-filter me-2"></i>Filter Complaints
                            </button>
                        </h2>

                        <div id="collapseFilter" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row align-items-end g-2">

                                    <div class="col-md-3">
                                        <label class="form-label">Reference No</label>
                                        <input type="text" id="filterReference" class="form-control"
                                            placeholder="Enter reference...">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">User Name</label>
                                        <input type="text" id="filterUser" class="form-control"
                                            placeholder="Enter username...">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Priority</label>
                                        <select id="filterPriority" class="form-select">
                                            <option value="">All</option>
                                            <option value="low">Low</option>
                                            <option value="normal">Normal</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select id="filterStatus" class="form-select">
                                            <option value="">All</option>
                                            @foreach ($statuses as $st)
                                                <option value="{{ $st }}">{{ strtoupper($st) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">From Date</label>
                                        <input type="date" id="filterDateFrom" class="form-control">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">To Date</label>
                                        <input type="date" id="filterDateTo" class="form-control">
                                    </div>

                                    <div class="col-md-12 d-flex gap-2 mt-2">
                                        <button class="btn buttonColor" id="applyFilter">
                                            <i class="fa-solid fa-search me-1"></i>Apply Filter
                                        </button>
                                        <button class="btn btn-secondary" id="resetFilter">
                                            <i class="fa-solid fa-redo me-1"></i>Reset
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-scroll-wrapper">
                            <table id="complaintTable" class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Reference No</th>
                                        <th>User Name</th>
                                        <th>Service</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th class="text-center">Desc</th>
                                        <th>Admin Notes</th>
                                        <th class="text-center">File</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Description Modal --}}
    <div class="modal fade" id="descriptionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">Complaint Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="descriptionText" class="mb-0"></p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Modal --}}
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">Update Complaint: <span id="updateRef"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="complaintId">

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="updateStatus" class="form-select">
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}">{{ strtoupper($st) }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger d-none" id="err_updateStatus"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea id="updateNotes" class="form-control" rows="3" placeholder="Write admin notes..."></textarea>
                        <small class="text-danger d-none" id="err_updateNotes"></small>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn buttonColor" id="saveUpdate">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            function formatDateTime(dt) {
                if (!dt) return '-';
                const d = new Date(dt);
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const yyyy = d.getFullYear();
                const hh = String(d.getHours()).padStart(2, '0');
                const min = String(d.getMinutes()).padStart(2, '0');
                return `${dd}-${mm}-${yyyy} ${hh}:${min}`;
            }

            function resetUpdateErrors() {
                $('#err_updateStatus, #err_updateNotes').addClass('d-none').text('');
            }

            let table = $('#complaintTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('complain.report.fetch') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');

                        d.reference_number = $('#filterReference').val();
                        d.user_name = $('#filterUser').val();
                        d.priority = $('#filterPriority').val();
                        d.status = $('#filterStatus').val();

                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                    }
                },

                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                searchable: true,
                responsive: false,

                dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-3'<'col-sm-6'i><'col-sm-6 text-end'p>>",

                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
                        className: 'btn buttonColor btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                        className: 'btn buttonColor btn-sm'
                    }
                ],

                language: {
                    searchPlaceholder: "Search complaints..."
                },

                columns: [{
                        data: 'id',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'reference_number'
                    },
                    {
                        data: 'user_name'
                    },
                    {
                        data: 'service_name'
                    },
                    {
                        data: 'category',
                        render: (d) => d ? (d.charAt(0).toUpperCase() + d.slice(1)) : '-'
                    },

                    {
                        data: 'priority',
                        render: function(d) {
                            let cls = 'bg-warning';
                            if (d === 'high') cls = 'bg-danger';
                            else if (d === 'urgent') cls = 'bg-dark';
                            else if (d === 'low') cls = 'bg-success';
                            return `<span class="badge ${cls}">${(d || '-').toUpperCase()}</span>`;
                        }
                    },

                    {
                        data: 'status',
                        render: function(d) {
                            let cls = 'bg-warning';
                            if (d === 'resolved') cls = 'bg-success';
                            else if (d === 'in_progress') cls = 'bg-info';
                            else if (d === 'closed') cls = 'bg-secondary';
                            return `<span class="badge ${cls}">${(d || '-').toUpperCase()}</span>`;
                        }
                    },

                    {
                        data: 'description',
                        render: function(d) {
                            return `
                    <a href="javascript:void(0)" class="text-dark view-description" data-description="${(d ?? '').replace(/"/g, '&quot;')}">
                        <i class="fa-regular fa-eye"></i>
                    </a>
                `;
                        },
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },

                    {
                        data: 'admin_notes',
                        render: (d) => d ?? '-'
                    },

                    {
                        data: 'attachment_url',
                        render: function(d) {
                            if (!d) return '-';
                            return `<a href="${d}" target="_blank">View</a>`;
                        },
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },

                    {
                        data: 'created_at',
                        render: function(d) {
                            return formatDateTime(d);
                        }
                    },

                    {
                        data: 'id',
                        render: function(id, type, row) {
                            return `
                    <button type="button"
                        class="btn btn-sm buttonColor edit-complaint"
                        data-id="${id}"
                        data-status="${row.status}"
                        data-notes="${(row.admin_notes ?? '').replace(/"/g,'&quot;')}"
                        data-ref="${row.reference_number}">
                        Update
                    </button>
                `;
                        },
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ]
            });


            $('#applyFilter').on('click', function() {
                table.ajax.reload();
            });
            $('#resetFilter').on('click', function() {
                $('#filterReference, #filterUser').val('');
                $('#filterPriority, #filterStatus').val('');
                $('#filterDateFrom, #filterDateTo').val('');
                table.ajax.reload();
            });


            $(document).on('click', '.view-description', function() {
                let description = $(this).data('description') || '';
                $('#descriptionText').text(description);
                $('#descriptionModal').modal('show');
            });


            $(document).on('click', '.edit-complaint', function() {
                resetUpdateErrors();

                $('#complaintId').val($(this).data('id'));
                $('#updateStatus').val($(this).data('status'));
                $('#updateNotes').val($(this).data('notes') ?? '');
                $('#updateRef').text($(this).data('ref'));

                $('#updateModal').modal('show');
            });

            $('#saveUpdate').on('click', function() {
                resetUpdateErrors();

                const id = $('#complaintId').val();
                const status = $('#updateStatus').val();
                const notes = $('#updateNotes').val();

                $.ajax({
                    url: "{{ url('/complain-report') }}/" + id + "/update",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: status,
                        admin_notes: notes
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
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let msg = 'Something went wrong!';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.status) $('#err_updateStatus').removeClass('d-none')
                                .text(errors.status[0]);
                            if (errors.admin_notes) $('#err_updateNotes').removeClass('d-none')
                                .text(errors.admin_notes[0]);
                            msg = 'Validation error!';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });

        });
    </script>

@endsection
