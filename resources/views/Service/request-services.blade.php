@extends('layouts.app')

@section('title', 'Request Services')
@section('page-title', 'Request Services')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                Filter Service Requests
            </button>
        </h2>

        <div id="collapseFilter" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <input type="text" id="filterUser" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Service</label>
                        <input type="text" id="filterService" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="button" class="btn buttonColor" id="applyFilter">Filter</button>
                        <button type="button" class="btn btn-secondary" id="resetFilter">Reset</button>
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
                <table id="serviceRequestTable"
                    class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($requests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>{{ $request->user->name ?? 'N/A' }}</td>
                                <td>{{ $request->service->service_name ?? 'N/A' }}</td>

                                <td>
                                    <span class="badge
                                        {{ $request->status == 'pending' ? 'bg-warning' : ($request->status == 'approved' ? 'bg-success' : 'bg-danger') }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>

                                <td>{{ $request->created_at?->format('d-m-Y H:i') }}</td>


                                <td>
                                    @if (auth()->user()->role_id == 1)
                                        @if ($request->status == 'pending')
                                            <button type="button"
                                                class="btn btn-sm btn-primary approve-btn"
                                                data-id="{{ $request->id }}"
                                                data-status="pending">
                                                Requested
                                            </button>
                                        @elseif ($request->status == 'approved')
                                            <button type="button"
                                                class="btn btn-sm btn-success approve-btn"
                                                data-id="{{ $request->id }}"
                                                data-status="approved">
                                                Activated
                                            </button>
                                        @else
                                            <span class="text-muted">No Action</span>

                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<form id="approveForm" method="POST" style="display:none;">
    @csrf
</form>

<script>
$(document).ready(function () {

    let table = $('#serviceRequestTable').DataTable({
        pageLength: 10,
        responsive: true,
        language: {
            searchPlaceholder: "Search service requests..."
        }
    });

    $('#applyFilter').on('click', function () {
        table
            .column(1).search($('#filterUser').val())
            .column(2).search($('#filterService').val())
            .column(3).search($('#filterStatus').val())
            .draw();
    });

    $('#resetFilter').on('click', function () {
        $('#filterUser').val('');
        $('#filterService').val('');
        $('#filterStatus').val('');
        table.search('').columns().search('').draw();
    });

    $(document).on('click', '.approve-btn', function () {

        let id = $(this).data('id');
        let status = $(this).data('status');

        let title = status === 'pending'
            ? 'Activate Service?'
            : 'Cancel Activation?';

        let text = status === 'pending'
            ? 'This service will be activated.'
            : 'This service will be deactivated.';

        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#approveForm')
                    .attr('action', `/service-request/${id}/approve`)
                    .submit();
            }
        });
    });

});
</script>

@endsection
