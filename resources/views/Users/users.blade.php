@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter Users
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="filterName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="filterName" placeholder="Enter name">
                    </div>
                    <div class="col-md-3">
                        <label for="filterEmail" class="form-label">Email</label>
                        <input type="text" class="form-control" id="filterEmail" placeholder="Enter email">
                    </div>
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">All</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <!-- Buttons aligned with input fields -->
                        <button class="btn btn-primary " id="applyFilter"> Filter</button>
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
                <table id="usersTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <!-- <th>Organization Name</th> -->
                            <th>Contact Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Gender</th>
                            <th>Aadhaar No</th>
                            <th>Pan No</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        var table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('users_ajax') }}",
                data: function(d) {
                    d.name = $('#filterName').val();
                    d.email = $('#filterEmail').val();
                    d.status = $('#filterStatus').val();
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
                    className: 'btn btn-primary btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn btn-primary btn-sm'
                }
            ],
            language: {
                searchPlaceholder: "Search users..."
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'contact_name'
                },
                {
                    data: 'email'
                },
                {
                    data: 'mobile'
                },
                {
                    data: 'gender'
                },
                {
                    data: 'aadhaar'
                },
                {
                    data: 'pan'
                },
                {
                    data: 'status',
                    render: function(data) {
                        return data == 'Active' ?
                            '<span class="fw-bold text-success">ACTIVE</span>' :
                            '<span class="fw-bold text-danger">INACTIVE</span>';
                    },
                    orderable: false,
                    searchable: false
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
            $('#filterEmail').val('');
            $('#filterStatus').val('');
            table.ajax.reload();
        });
    });
</script>

@endsection