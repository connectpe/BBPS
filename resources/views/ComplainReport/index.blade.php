@extends('layouts.app')

@section('title', 'Complaint Report')
@section('page-title', 'Complaint Report')
<style>
    #complaintTable { width: auto !important; table-layout: fixed;}
    #complaintTable th,#complaintTable td {word-break: break-word;}
    #complaintTable th{font-size: 12px !important;}
    #complaintTable td{font-size: 13px !important;}
    .table-scroll-wrapper {overflow-y: auto;overflow-x: auto;border-radius: 0.25rem; }
   #complaintTable thead th {position: sticky;top: 0;background-color: #f8f9fa;z-index: 10;}
</style>


@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 px-0">
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
                                <div class="row align-items-end">

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
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select id="filterStatus" class="form-select">
                                            <option value="">All</option>
                                            <option value="open">Open</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="resolved">Resolved</option>
                                            <option value="closed">Closed</option>
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

                <div class="card shadow-sm">
                    <div class="card-body pt-4">
                        <div class="table-scroll-wrapper">
                            <table id="complaintTable" class="table table-striped table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 10px !important;">#</th>
                                        <th>Reference No</th>
                                        <th>User Name</th>
                                        <th>Service Name</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                        <th>Admin Notes</th>
                                        <th>Attachment</th>
                                        <th>Created Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($complaints as $i => $c)
                                        <tr data-id="{{ $c->id }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td class="ref">{{ $c->reference_number }}</td>
                                            <td class="user">{{ $c->user->name ?? '-' }}</td>
                                            <td class="service">{{ $c->service_name }}</td>
                                            <td class="category">
                                                {{ ucfirst($c->category ?? '-') }}
                                            </td>


                                            <td class="priority">
                                                @php
                                                    $p = $c->priority;
                                                    $pClass = 'bg-warning';
                                                    if ($p === 'high') {
                                                        $pClass = 'bg-danger';
                                                    } elseif ($p === 'urgent') {
                                                        $pClass = 'bg-dark';
                                                    } elseif ($p === 'low') {
                                                        $pClass = 'bg-success';
                                                    }
                                                @endphp
                                                <span
                                                    class="badge {{ $pClass }}">{{ strtoupper($c->priority) }}</span>
                                            </td>

                                            <td class="status">
                                                @php
                                                    $st = $c->status;
                                                    $stClass = 'bg-warning';
                                                    if ($st === 'resolved') {
                                                        $stClass = 'bg-success';
                                                    } elseif ($st === 'in_progress') {
                                                        $stClass = 'bg-info';
                                                    } elseif ($st === 'closed') {
                                                        $stClass = 'bg-secondary';
                                                    }
                                                @endphp
                                                <span class="badge {{ $stClass }}">{{ strtoupper($c->status) }}</span>
                                            </td>

                                            <td class="text-center">
                                                <a href="javascript:void(0)" class="text-dark view-description"
                                                    data-description="{{ $c->description }}">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>
                                            </td>

                                            <td class="notes">{{ $c->admin_notes ?? '-' }}</td>

                                            <td class="text-center attachment">
                                                @if ($c->attachment_path)
                                                    <a href="{{ asset('storage/' . $c->attachment_path) }}"
                                                        target="_blank">
                                                        View
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td class="created">
                                                {{ $c->created_at ? $c->created_at->format('d-m-Y H:i') : '-' }}
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm buttonColor edit-complaint"
                                                    data-id="{{ $c->id }}" data-status="{{ $c->status }}"
                                                    data-notes="{{ $c->admin_notes }}"
                                                    data-ref="{{ $c->reference_number }}">
                                                    Update
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

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

            function resetUpdateErrors() {
                $('#err_updateStatus, #err_updateNotes').addClass('d-none').text('');
            }

            let table = $('#complaintTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
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
                }
            });

            // Filters
            $('#applyFilter').on('click', function() {
                const dateFrom = $('#filterDateFrom').val();
                const dateTo = $('#filterDateTo').val();

                table.column(1).search($('#filterReference').val());
                table.column(2).search($('#filterUser').val());
                table.column(5).search($('#filterPriority').val());
                table.column(6).search($('#filterStatus').val());

                // Date range filter using custom filter
                $.fn.dataTable.ext.search.pop(); 
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    let dateCell = data[10]; 
                    
                    if (!dateCell || dateCell === '-') return true;
                    let dateParts = dateCell.split(' ')[0].split('-');
                    let cellDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);

                    let fromDate = dateFrom ? new Date(dateFrom) : null;
                    let toDate = dateTo ? new Date(dateTo) : null;

                    if (fromDate && cellDate < fromDate) return false;
                    if (toDate) {
                        toDate.setHours(23, 59, 59, 999);
                        if (cellDate > toDate) return false;
                    }

                    return true;
                });

                table.draw();
            });


            $('#resetFilter').on('click', function() {
                $('#filterReference, #filterUser').val('');
                $('#filterPriority, #filterStatus').val('');
                $('#filterDateFrom, #filterDateTo').val('');
                $.fn.dataTable.ext.search.pop(); // Remove date filter
                table.search('').columns().search('').draw();
            });

            // Description modal
            $(document).on('click', '.view-description', function() {
                let description = $(this).data('description') || '';
                $('#descriptionText').text(description);
                $('#descriptionModal').modal('show');
            });

            // Open update modal
            $(document).on('click', '.edit-complaint', function() {
                resetUpdateErrors();

                const id = $(this).data('id');
                const status = $(this).data('status');
                const notes = $(this).data('notes') ?? '';
                const ref = $(this).data('ref');

                $('#complaintId').val(id);
                $('#updateStatus').val(status);
                $('#updateNotes').val(notes);
                $('#updateRef').text(ref);

                $('#updateModal').modal('show');
            });

            // Save update (AJAX) + SweetAlert
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

                        const row = $('tr[data-id="' + id + '"]');

                        // status badge class
                        let stClass = 'bg-warning';
                        if (status === 'resolved') stClass = 'bg-success';
                        else if (status === 'in_progress') stClass = 'bg-info';
                        else if (status === 'closed') stClass = 'bg-secondary';

                        row.find('td.status').html(
                            `<span class="badge ${stClass}">${status.toUpperCase()}</span>`);
                        row.find('td.notes').text(notes ? notes : '-');

                        // Update button data so next time modal shows latest
                        row.find('.edit-complaint').data('status', status);
                        row.find('.edit-complaint').data('notes', notes);

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: res.message || 'Complaint updated successfully!',
                            timer: 1800,
                            showConfirmButton: false
                        });

                        $('#updateModal').modal('hide');
                    },
                    error: function(xhr) {
                        let msg = 'Something went wrong!';
                        if (xhr.status === 422) msg = 'Validation error! Please check inputs.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.status) $('#err_updateStatus').removeClass('d-none')
                                .text(errors.status[0]);
                            if (errors.admin_notes) $('#err_updateNotes').removeClass('d-none')
                                .text(errors.admin_notes[0]);
                        }
                    }
                });
            });

        });
    </script>

@endsection
