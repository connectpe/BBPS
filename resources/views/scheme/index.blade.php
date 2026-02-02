@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Select2 Bootstrap style matching */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        padding-top: 4px;
    }
    .select2-container {
        width: 100% !important;
    }
</style>

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
                                <button class="btn btn-sm btn-outline-primary edit-scheme-btn" data-id="{{ $scheme->id }}">
                                    <i class="fas fa-list"></i>
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
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                Assign Scheme to User
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">User:</label>
                    <select class="form-control form-select shadow-none searchable-select">
                        <option value="">-- Select user --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Scheme Name:</label>
                    <select class="form-control form-select shadow-none searchable-select">
                        <option value="">-- Select --</option>
                        @foreach ($schemes as $scheme)
                            <option value="{{ $scheme->id }}">{{ $scheme->scheme_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary me-2 px-4 shadow-sm">Search</button>
                    <button class="btn btn-warning text-white px-4 shadow-sm">Reset</button>
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
                    {{-- Loop relations here --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="schemeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
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
                            <input type="text" name="scheme_name" id="scheme_name" class="form-control shadow-none" placeholder="Enter scheme name" required>
                        </div>
                        {{-- <div class="col-md-4">
                            <label class="fw-bold">Scheme Status:</label>
                            <select name="scheme_status" id="scheme_status" class="form-control form-select shadow-none">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div> --}}
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
                    <div class="d-flex justify-content-center">
                        <button type="button" id="addMoreRules" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-plus"></i> Add More Rules
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
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
            <div class="modal-header">
                <h5 class="modal-title">Assign Scheme to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small">Select User *</label>
                            <select name="user_id" id="user_search" class="form-control" required>
                                <option value="">-- Select User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small">Select Scheme *</label>
                            <select name="scheme_id" id="scheme_search" class="form-control" required>
                                <option value="">-- Select Scheme --</option>
                                @foreach($schemes as $scheme)
                                    <option value="{{ $scheme->id }}">{{ $scheme->scheme_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark btn-sm px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="assignSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() } });

        // Initialize Select2 for main page filters
        $('.searchable-select').select2();

        // Initialize Select2 for Modal with dropdownParent to fix modal focus issue
        $('#assignUserModal').on('shown.bs.modal', function () {
            $('#user_search, #scheme_search').select2({
                dropdownParent: $('#assignUserModal'),
                placeholder: "-- Search --",
                allowClear: true
            });
        });

        $('#schemeTable, #relationTable').DataTable({
            dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" + "<'row'<'col-12'tr>>" + "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
            buttons: [{extend: 'excelHtml5', text: 'Excel', className: 'btn btn-success btn-sm'}]
        });

        const globalServices = @json($globalServices);

        function getRowHtml(data = {}) {
            let options = '<option value="">--Select--</option>';
            globalServices.forEach(s => {
                let selected = (data.service_id == s.id) ? 'selected' : '';
                options += `<option value="${s.id}" ${selected}>${s.service_name}</option>`;
            });

            return `
            <tr class="rule-row">
                <input type="hidden" class="row-rule-id" value="${data.id || ''}">
                <td><select class="form-control form-control-sm row-service" required>${options}</select></td>
                <td><input type="text" class="form-control form-control-sm" value="--" readonly></td>
                <td><select class="form-control form-control-sm row-type"><option value="Fixed" ${data.type == 'Fixed' ? 'selected' : ''}>Fixed</option><option value="Percentage" ${data.type == 'Percentage' ? 'selected' : ''}>Percentage</option></select></td>
                <td><select class="form-control form-control-sm row-status"><option value="1" ${data.is_active == '1' ? 'selected' : ''}>Active</option><option value="0" ${data.is_active == '0' ? 'selected' : ''}>Inactive</option></select></td>
                <td><input type="number" step="any" class="form-control form-control-sm row-start" value="${data.start_value || ''}" required></td>
                <td><input type="number" step="any" class="form-control form-control-sm row-end" value="${data.end_value || ''}" required></td>
                <td><input type="number" step="any" class="form-control form-control-sm row-fee" value="${data.fee || ''}" required></td>
                <td><input type="number" step="any" class="form-control form-control-sm row-min" value="${data.min_fee || ''}" required></td>
                <td><input type="number" step="any" class="form-control form-control-sm row-max" value="${data.max_fee || ''}" required></td>
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
                    if(res.status) {
                        $('#scheme_id').val(res.scheme.id);
                        $('#scheme_name').val(res.scheme.scheme_name);
                        $('#scheme_status').val(res.scheme.is_active);
                        res.scheme.rules.forEach(rule => { $('#rulesTable tbody').append(getRowHtml(rule)); });
                        $('#schemeModal').modal('show');
                    }
                }
            });
        });

        $('#addMoreRules').click(function() { $('#rulesTable tbody').append(getRowHtml()); });
        $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); });

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
            let url = schemeId ? "{{ url('update-scheme-rule') }}/" + schemeId : "{{ route('add_scheme_rule') }}";
            $.ajax({
                url: url,
                type: "POST",
                data: { scheme_name: $('#scheme_name').val(), scheme_status: $('#scheme_status').val(), rules: rules },
                success: function(res) {
                    if(res.status) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: res.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    }
                }
            });
        });

        $('#assignUserForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('assign_user_scheme') }}",
                type: "POST",
                data: $(this).serialize(),
                beforeSend: function() { $('#assignSubmitBtn').prop('disabled', true).text('Assigning...'); },
                success: function(res) {
                    if(res.status) {
                        Swal.fire({ icon: 'success', title: 'Assigned!', text: res.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    }
                },
                error: function() {
                    $('#assignSubmitBtn').prop('disabled', false).text('Submit');
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        });
    });
</script>
@endsection