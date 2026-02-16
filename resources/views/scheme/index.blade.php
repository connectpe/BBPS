@extends('layouts.app')
@section('title', 'Scheme Management')
@section('page-title', 'Scheme Management')
@section('page-button')
<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <button class="btn buttonColor btn-sm btn-add-new" data-bs-toggle="modal" data-bs-target="#schemeModal">
            <i class="fa fa-plus"></i> Add New Scheme
        </button>
    </div>
</div>
@endsection
@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <div class="table-responsive">
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0 text-dark font-weight-bold">Scheme and User Relations</h5>
            <button class="btn buttonColor btn-sm btn-assign-new" data-bs-toggle="modal"
                data-bs-target="#assignUserModal">
                <i class="fa fa-plus"></i> Assign Scheme to User
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-4 g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">User:</label>
                    <select class="form-control form-select shadow-none form-select2" id="filter_user">
                        <option value="">-- Select user --</option>
                        @foreach ($assignedUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Scheme Name:</label>
                    <select class="form-control form-select shadow-none form-select2" id="filter_scheme">
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
                    <button class="btn buttonColor me-2 px-4 shadow-sm" id="searchBtn">Search</button>

                    <button class="btn btn-secondary text-white px-4 shadow-sm"
                        onclick="window.location.reload()">Reset</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="relationTable" class="table table-bordered table-striped w-100">
                    <thead class="bg-light">
                        <tr>
                            <th>S.N.</th>
                            <th>ORGANIZATION NAME</th>
                            <th>SCHEME NAME</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>
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
                        <button type="button" id="addMoreRules" class="btn buttonColor btn-sm mt-2">
                            <i class="fas fa-plus"></i> Add More Rules
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn buttonColor btn-sm px-4" id="submitBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="assignUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title" id="assignModalTitle">Assign Scheme to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignUserForm">
                @csrf
                <input type="hidden" name="config_id" id="config_id">
                <div class="modal-body">
                    <div class="row mb-3 g-3">
                        <div class="col-md-12">
                            <label class="fw-bold small">Select User *</label>
                            <select name="user_id" id="user_search" class="form-control form-select2" required>
                                <option value="">-- Select User --</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                    ({{ $user->business->business_name ?? 'Business Not Added' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="fw-bold small">Select Scheme *</label>
                            <select name="scheme_id" id="scheme_search" class="form-control form-select2" required>
                                <option value="">-- Select Scheme --</option>
                                @foreach ($schemes as $scheme)
                                <option value="{{ $scheme->id }}">{{ $scheme->scheme_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm px-4"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn buttonColor btn-sm px-4" id="assignSubmitBtn">Submit</button>
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


        let schemeTable = $('#schemeTable').DataTable({
            processing: true,
            serverSide: true,
            dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                "<'row'<'col-12'tr>>" + "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
            buttons: [{
                extend: 'excelHtml5',
                text: 'Excel',
                className: 'btn buttonColor btn-sm'
            }],
            ajax: {
                url: "{{ url('fetch/schemes') }}",
                type: "POST"
            },
            columns: [{
                    data: null,
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    data: 'scheme_name',
                    name: 'scheme_name',
                    className: 'text-primary'
                },
                {
                    data: 'is_active',
                    render: data => data == '1' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'id',
                    orderable: false,
                    render: id =>
                        `<button class="btn btn-sm btn-outline-primary edit-scheme-btn" data-id="${id}"><i class="fas fa-edit"></i></button>`
                }
            ]
        });

        let relationTable = $('#relationTable').DataTable({
            processing: true,
            serverSide: true,
            dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                "<'row'<'col-12'tr>>" + "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
            buttons: [{
                extend: 'excelHtml5',
                text: 'Excel',
                className: 'btn buttonColor btn-sm'
            }],
            ajax: {
                url: "{{ url('fetch/scheme-relations') }}",
                type: "POST",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.user_id = $('#filter_user').val();
                    d.scheme_id = $('#filter_scheme').val();
                },
                error: function(xhr) {
                    console.log("Error details:", xhr.responseText);
                }
            },
            columns: [{
                    data: 'id',
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    data: null,
                    defaultContent: 'N/A',
                    render: function(data, type, row) {
                        let url = "{{ route('view_user', ['id' => 'id']) }}".replace('id', row
                            .user_id);
                        const userName = row?.user?.name;
                        const businessName = row?.user?.business?.business_name;

                        return `
                                <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                                    ${userName ?? '----'} <br/>
                                    [${businessName ?? '----'}]
                                </a>
                            `;
                    }
                },
                {
                    data: 'scheme.scheme_name',
                    name: 'scheme.scheme_name',
                    defaultContent: 'N/A'
                },
                {
                    data: 'id',
                    className: 'text-center',
                    orderable: false,
                    render: id =>
                        `
                            <button class="btn btn-sm btn-outline-primary edit-assigned-btn" data-id="${id}"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger delete-assigned-btn" data-id="${id}"><i class="fas fa-trash"></i></button>`
                }
            ]
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
                url: "{{ route('edit_scheme', ['id' => ':id']) }}".replace(':id', id),
                type: "GET",
                success: function(res) {
                    if (res.status) {
                        $('#scheme_id').val(res.scheme.id);
                        $('#scheme_name').val(res.scheme.scheme_name);
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
            let url = schemeId ? "{{ route('update_scheme_rule', ['id' => ':id']) }}".replace(':id',
                schemeId) : "{{ route('add_scheme_rule') }}";


            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    scheme_name: $('#scheme_name').val(),
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
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        $('#submitBtn').prop('disabled', false).text('Save Changes');
                    }
                },
                error: function(xhr) {
                    $('#submitBtn').prop('disabled', false).text('Save Changes');
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorList = '';
                        $.each(errors, function(key, value) {
                            errorList += '<li>' + value[0] + '</li>';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: '<ul style="text-align: left;">' + errorList +
                                '</ul>',
                        });
                    } else {
                        Swal.fire('Error', 'Something went wrong on the server.', 'error');
                    }
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

            let editUrl = "{{ route('edit_assign_scheme', ['id' => ':id']) }}".replace(':id', id);

            $.ajax({
                url: editUrl,
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

            let updateRouteTemplate = "{{ route('update_user_assigned_scheme', ['id' => ':id']) }}";
            let url = configId ?
                updateRouteTemplate.replace(':id', configId) :
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
                            })
                            .then(() => location.reload());
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
            relationTable.ajax.reload();
        });

        $(document).on('click', '.delete-assigned-btn', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {

                    let deleteUrl = "{{ route('delete_assign_scheme', ['id' => ':id']) }}"
                        .replace(':id', id);

                    $.ajax({
                        url: deleteUrl,
                        type: "GET",
                        success: function(res) {
                            if (res.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            }
                        }
                    });
                }
            });
        });

    });
</script>
@endsection