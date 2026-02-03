@extends('layouts.app')
@section('title', 'Scheme Management')
@section('page-title', 'Scheme Management')
@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm mb-5">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0 text-dark font-weight-bold">List of Created Schemes</h5>
                <button class="btn btn-primary btn-sm btn-add-new" data-bs-toggle="modal" data-bs-target="#schemeModal">
                    <i class="fas fa-plus"></i> Add New Scheme
                </button>
            </div>
            <div class="card-body">
                <table id="schemeTable" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>SCHEME NAME</th>
                            <th>STATUS</th>
                            <th>CREATED AT</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schemes as $key => $scheme)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="text-primary scheme-name-text">{{ $scheme->scheme_name }}</td>
                                <td>
                                    @if ($scheme->is_active == '1')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $scheme->created_at }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-scheme-btn"
                                        data-id="{{ $scheme->id }}">
                                         <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0 text-dark font-weight-bold">Scheme and User Relations</h5>
                <button class="btn btn-primary btn-sm btn-assign-new" data-bs-toggle="modal"
                    data-bs-target="#assignUserModal">
                    Assign Scheme to User
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">User:</label>
                        <select class="form-control form-select shadow-none searchable-select" id="filter_user">
                            <option value="">-- Select user --</option>
                            @foreach ($assignedUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Scheme Name:</label>
                        <select class="form-control form-select shadow-none searchable-select" id="filter_scheme">
                            <option value="">-- Select --</option>
                            @foreach ($assignedSchemes as $s)
                                <option value="{{ $s->id }}">{{ $s->scheme_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary me-2 px-4 shadow-sm" id="searchBtn">Search</button>
                        <button class="btn btn-warning text-white px-4 shadow-sm" onclick="location.reload()">Reset</button>
                    </div> --}}
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary me-2 px-4 shadow-sm" id="searchBtn">Search</button>

                        <button class="btn btn-warning text-white px-4 shadow-sm"
                            onclick="window.location.reload()">Reset</button>
                    </div>
                </div>

                <table id="relationTable" class="table table-bordered table-striped w-100">
                    <thead class="bg-light">
                        <tr>
                            <th>S.N.</th>
                            <th>USER NAME</th>
                            <th>EMAIL</th>
                            <th>SCHEME NAME</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($relations as $index => $rel)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $rel->user->name ?? 'N/A' }}</td>
                                <td>{{ $rel->user->email ?? 'N/A' }}</td>
                                <td>{{ $rel->scheme->scheme_name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary edit-assigned-btn"
                                        data-id="{{ $rel->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-assigned-btn"
                                        data-id="{{ $rel->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="schemeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title" id="modalTitle">Add & Update Scheme Rules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="schemeForm">
                    @csrf
                    <input type="hidden" name="scheme_id" id="scheme_id">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="fw-bold">Scheme Name:</label>
                                <input type="text" name="scheme_name" id="scheme_name" class="form-control shadow-none"
                                    placeholder="Enter scheme name" required>
                            </div>

                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center" id="rulesTable">
                                <thead class="bg-light text-uppercase" style="font-size: 11px;">
                                    <tr>
                                        <th style="min-width: 140px;">SERVICE</th>
                                        <th>PRODUCT</th>
                                        <th>FEE TYPE</th>
                                        <th>STATUS</th>
                                        <th>START VALUE</th>
                                        <th>END VALUE</th>
                                        <th>FEE</th>
                                        <th>MIN FEE</th>
                                        <th>MAX FEE</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <button type="button" id="addMoreRules" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-plus"></i> Add More Rules
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4" id="submitBtn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title" id="assignModalTitle">Assign Scheme to User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignUserForm">
                    @csrf
                    <input type="hidden" name="config_id" id="config_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold small">Select User *</label>
                                <select name="user_id" id="user_search" class="form-control" required>
                                    <option value="">-- Select User --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold small">Select Scheme *</label>
                                <select name="scheme_id" id="scheme_search" class="form-control" required>
                                    <option value="">-- Select Scheme --</option>
                                    @foreach ($schemes as $scheme)
                                        <option value="{{ $scheme->id }}">{{ $scheme->scheme_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4" id="assignSubmitBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });

            $('.searchable-select').select2();
            $('#assignUserModal').on('shown.bs.modal', function() {
                $('#user_search, #scheme_search').select2({
                    dropdownParent: $('#assignUserModal'),
                    placeholder: "-- Search --",
                    allowClear: true
                });
            });

            $('#schemeTable, #relationTable').DataTable({
                dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                    "<'row'<'col-12'tr>>" + "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-success btn-sm'
                }]
            });

            const globalServices = @json($globalServices);

            function getRowHtml(data = {}) {
                let options = '<option value="">--Select--</option>';
                globalServices.forEach(s => {
                    let selected = (data.service_id == s.id) ? 'selected' : '';
                    options += `<option value="${s.id}" ${selected}>${s.service_name}</option>`;
                });
                return `<tr class="rule-row">
                    <input type="hidden" class="row-rule-id" value="${data.id || ''}">
                    <td><select class="form-control form-control-sm row-service" required>${options}</select></td>
                    <td><input type="text" class="form-control form-control-sm" value="--" readonly></td>
                    <td><select class="form-control form-control-sm row-type"><option value="Fixed" ${data.type == 'Fixed' ? 'selected' : ''}>Fixed</option><option value="Percentage" ${data.type == 'Percentage' ? 'selected' : ''}>Percentage</option></select></td>
                    <td><select class="form-control form-control-sm row-status"><option value="1" ${data.is_active == '1' ? 'selected' : ''}>Active</option><option value="0" ${data.is_active == '0' ? 'selected' : ''}>Inactive</option></select></td>
                    <td><input type="number" step="any" class="form-control form-control-sm row-start" value="${data.start_value || ''}" required></td>
                    <td><input type="number" step="any" class="form-control form-control-sm row-end" value="${data.end_value || ''}" required></td>
                    <td><input type="number" step="any" class="form-control form-control-sm row-fee" value="${data.fee || ''}" required></td>
                    <td><input type="number" step="any" class="form-control form-control-sm row-min" value="${data.min_fee || ''}"></td>
                    <td><input type="number" step="any" class="form-control form-control-sm row-max" value="${data.max_fee || ''}"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-times"></i></button></td>
                </tr>`;
            }

            $('.btn-add-new').click(function() {
                $('#modalTitle').text('Add New Scheme Rules');
                $('#scheme_id').val('');
                $('#schemeForm')[0].reset();
                $('#rulesTable tbody').empty();
            });

            $(document).on('click', '.edit-scheme-btn', function() {
                let id = $(this).data('id');
                $('#modalTitle').text('Update Scheme Rules');
                $('#schemeForm')[0].reset();
                $('#rulesTable tbody').empty();
                $.ajax({
                    url: "{{ url('edit-scheme') }}/" + id,
                    type: "GET",
                    success: function(res) {
                        if (res.status) {
                            $('#scheme_id').val(res.scheme.id);
                            $('#scheme_name').val(res.scheme.scheme_name);
                            $('#scheme_status').val(res.scheme.is_active);
                            res.scheme.rules.forEach(rule => {
                                $('#rulesTable tbody').append(getRowHtml(rule));
                            });
                            $('#schemeModal').modal('show');
                        }
                    }
                });
            });

            $('#addMoreRules').click(function() {
                $('#rulesTable tbody').append(getRowHtml());
            });
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
            $('#schemeForm').on('submit', function(e) {
                e.preventDefault();

                let rules = [];
                $('.rule-row').each(function() {
                    rules.push({
                        rule_id: $(this).find('.row-rule-id').val(),
                        service_id: $(this).find('.row-service').val(),
                        start_value: $(this).find('.row-start').val(),
                        end_value: $(this).find('.row-end').val(),
                        type: $(this).find('.row-type').val(),
                        fee: $(this).find('.row-fee').val(),
                        min_fee: $(this).find('.row-min').val(),
                        max_fee: $(this).find('.row-max').val(),
                        is_active: $(this).find('.row-status').val()
                    });
                });

                let schemeId = $('#scheme_id').val();
                let baseUrl = "{{ url('update-scheme-rule') }}";
                let url = schemeId ? baseUrl + "/" + schemeId : "{{ route('add_scheme_rule') }}";

                $.ajax({
                    url: url,
                    type: "POST", 
                    data: {
                        _token: "{{ csrf_token() }}", 
                        scheme_name: $('#scheme_name').val(),
                        scheme_status: $('#scheme_status').val(),
                        rules: rules
                    },
                    beforeSend: function() {
                        $('#submitBtn').prop('disabled', true).text('Saving...');
                    },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            $('#submitBtn').prop('disabled', false).text('Save Changes');
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false).text('Save Changes');
                        console.log(xhr.responseText);
                        Swal.fire('Failed', 'Something went wrong on the server.', 'error');
                    }
                });
            });
            $('.btn-assign-new').click(function() {
                $('#assignModalTitle').text('Assign Scheme to User');
                $('#config_id').val('');
                $('#assignUserForm')[0].reset();
                $('#user_search, #scheme_search').val(null).trigger('change');
            });

            $(document).on('click', '.edit-assigned-btn', function() {
                let id = $(this).data('id');
                $('#assignModalTitle').text('Update Assigned Scheme');
                $.ajax({
                    url: "{{ url('edit-assigned-scheme') }}/" + id, 
                    type: "GET",
                    success: function(res) {
                        if (res.status) {
                            $('#config_id').val(res.data.id);
                            $('#user_search').val(res.data.user_id).trigger('change');
                            $('#scheme_search').val(res.data.scheme_id).trigger('change');
                            $('#assignUserModal').modal('show');
                        }
                    }
                });
            });

            $('#assignUserForm').on('submit', function(e) {
                e.preventDefault();
                let configId = $('#config_id').val();
                let url = configId ? "{{ url('update-user-assigned-scheme') }}/" + configId :
                    "{{ route('assign_scheme') }}";

                $.ajax({
                    url: url,
                    type: "POST",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#assignSubmitBtn').prop('disabled', true).text('Updating...');
                    },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        }
                    },
                    error: function(err) {
                        $('#assignSubmitBtn').prop('disabled', false).text('Submit');
                        Swal.fire('Error!', err.responseJSON.message || 'Validation failed.',
                            'error');
                    }
                });
            });
            
            $('#searchBtn').on('click', function() {
                let userText = $('#filter_user option:selected').text().trim();
                let schemeText = $('#filter_scheme option:selected').text().trim();

                let table = $('#relationTable').DataTable();
                if ($('#filter_user').val() == "") userText = "";
                if ($('#filter_scheme').val() == "") schemeText = "";
                table.column(1).search(userText);
                table.column(3).search(schemeText);

                table.draw(); 
            });

            $(document).on('click', '.delete-assigned-btn', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this user relation!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('delete-assigned-scheme') }}/" + id,
                            type: "GET",
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: res.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
