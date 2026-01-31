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
                    <div class="col-md-2">
                        <label for="filterName" class="form-label">User</label>
                        <select name="filterName" id="filterName" class="form-control">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="filterEmail" placeholder="Enter Email">
                    </div>
                   
                    <div class="col-md-2">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">All</option>
                            <option value="0">Initiated</option>
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </select>
                    </div>
                     <div class="col-md-2">
                        <label for="filterDateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>

                    <div class="col-md-2">
                        <label for="filterDateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <!-- Buttons aligned with input fields -->
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
                <table id="usersTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Organization Name</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>PanNO.</th>
                            <th>Aadhar NO.</th>
                            <th>Created at</th>
                            <th>Status</th>
                            <th>Root</th>
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

                url: "{{url('fetch')}}/users/0",
                type: 'POST',

                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.id = $('#filterName').val();
                    d.email = $('#filterEmail').val();
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
                searchPlaceholder: "Search users..."
            },

            columns: [{
                    data: 'id'
                },
                {
                    data: 'business.business_name',
                    render: function(data, type, row) {
                        let url = "{{route('view_user',['id' => 'id'])}}".replace('id', row.id);
                        return `
                        <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                            ${data ?? '----'}
                        </a>
                    `;
                    }
                },
                {
                    data: 'name',
                    render: function(data, type, row) {
                        let url = "{{route('view_user',['id' => 'id'])}}".replace('id', row.id);
                        return `
                        <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                            ${data}
                        </a>
                    `;
                    }
                },
                {
                    data: 'email'
                },
                {
                    data: 'business.pan_number'
                },
                {
                    data: 'business.aadhar_number'
                },
                {
                    data: 'created_at',
                    render:function(data){
                        return formatDateTime(data)
                    }
                },
                {
                    data: 'status',
                    render: function(data, type, row) {

                        const statusOptions = {
                            0: 'INITIATED',
                            1: 'ACTIVE',
                            2: 'INACTIVE',
                            3: 'PENDING',
                            4: 'SUSPENDED'
                        };

                        let dropdown = `<select class="form-select form-select-sm" onchange="changeStatusDropdown(this, ${row.id})" onfocus="this.setAttribute('data-prev', this.value)">`;

                        for (const [value, label] of Object.entries(statusOptions)) {
                            let selected = data == value ? 'selected' : '';
                            dropdown += `<option value="${value}" ${selected}>${label}</option>`;
                        }

                        dropdown += `</select>`;
                        return dropdown;
                    },
                    orderable: false,
                    searchable: false

                },
                {
                    data: null,
                    render: function(data, type, row) {

                        const statusOptions = {
                            0:'Mobikwik',
                            1:'Paysprint',
                            2:'Test'
                            
                        };

                        let dropdown = `<select class="form-select form-select-sm" onchange="changeRootDropdown(this, ${row.id})" onfocus="this.setAttribute('data-prev', this.value)">`;

                        for (const [value, label] of Object.entries(statusOptions)) {
                            let selected = data == value ? 'selected' : '';
                            dropdown += `<option value="${value}" ${selected}>${label}</option>`;
                        }

                        dropdown += `</select>`;
                        return dropdown;
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
                success: function(response) {
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
                error: function(xhr) {
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
</script>

@endsection