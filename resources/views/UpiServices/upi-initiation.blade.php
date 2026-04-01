@extends('layouts.app')

@section('title', 'Upi Initiation')
@section('page-title', 'Upi Initiation')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseFilter">
                Filter
            </button>
        </h2>

        <div id="collapseFilter" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-2">
                        <label class="form-label">User</label>
                        <input type="text" id="filterUser" class="form-control" placeholder="User name">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Any Key</label>
                        <input type="text" id="filterKeyword" class="form-control" placeholder="UTR / UPI ID">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn buttonColor w-100" id="applyFilter">Filter</button>
                        <button class="btn btn-secondary w-100" id="resetFilter">Reset</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- TABLE -->
<div class="card shadow-sm">
    <div class="card-body pt-4">
        <div class="table-responsive">
            <table id="upiTable"  class="table table-striped table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>UPI ID</th>
                        <th>UTR</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will come from backend / ajax later -->
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {

    // Empty DataTable Init (no data)
    $('#upiTable').DataTable({
        processing: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        ordering: true,
        searching: true
    });

    // Filter buttons (structure only - no logic)
    $('#applyFilter').on('click', function() {
        console.log('Apply filter clicked');
    });

    $('#resetFilter').on('click', function() {
        $('#filterUser').val('');
        $('#filterKeyword').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');

        console.log('Filters reset');
    });

});
</script>

@endsection