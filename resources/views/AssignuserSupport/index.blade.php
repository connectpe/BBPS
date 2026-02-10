@extends('layouts.app')

@section('title', 'Support Assignment')
@section('page-title', 'Support')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="accordion mb-3" id="filterAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseFilter">
                            Filter
                        </button>
                    </h2>
                    <div id="collapseFilter" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="row align-items-end g-2">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">User Name</label>
                                    <select id="filterUser" class="form-select form-select2">
                                        <option value="">-- All Assigned Users --</option>
                                        @foreach ($assignedUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Support Name</label>
                                    <select id="filterSupport" class="form-select form-select2">
                                        <option value="">-- All Assigned Support --</option>
                                        @foreach ($assignedSupports as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="col-md-6 d-flex gap-2 mt-2">
                                    <button class="btn buttonColor btn-sm px-3" id="applyFilter">Apply Filter</button>
                                    <button class="btn btn-secondary btn-sm px-3" id="resetFilter">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mb-5">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
            <h5 class="mb-0 text-dark font-weight-bold">User Assigned to Support</h5>
            <button class="btn buttonColor btn-sm px-4 shadow-sm" id="btnAddNew">
                <i class="fas fa-plus me-1"></i> Assign Support
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="supportAssignmentTable" class="table table-bordered table-striped w-100">
                    <thead class="bg-light text-uppercase" style="font-size: 11px;">
                        <tr>
                            <th>S.N.</th>
                            <th>USER NAME</th>
                            <th>ASSIGNED TO (SUPPORT NAME)</th>
                            <th>ASSIGNED AT</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignSupportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Assign Support</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignSupportForm">
                @csrf
                <input type="hidden" name="assignment_id" id="assignment_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold mb-0">Select Users (Multiple) *</label>
                        </div>
                        <select name="user_id[]" id="user_id" class="form-control form-select2"
                            multiple="multiple" required>
                            @foreach ($users as $user)
                            @php $isAssigned = in_array($user->id, $alreadyAssignedIds); @endphp
                            <option value="{{ $user->id }}"
                                data-assigned="{{ $isAssigned ? 'true' : 'false' }}">
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Assign To Support</label>
                        <select name="assined_to" id="assined_to" class="form-select form-select2 shadow-none" required>
                            <option value="" selected disabled>-- Select Support --</option>
                            @foreach ($supportStaffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn buttonColor btn-sm px-4" id="submitBtn">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function formatUserOption(state) {
            if (!state.id) return state.text;
            var isAssigned = $(state.element).data('assigned');
            var editingUserId = window.currentEditingUserId || null;

            if ((isAssigned === true || isAssigned === "true") && state.id != editingUserId) {
                return $('<div class="d-flex justify-content-between align-items-center"><span>' + state.text +
                    '</span><span class="text-success small fw-bold">Assigned <i class="fas fa-check"></i></span></div>'
                );
            }
            return state.text;
        }

       
        $('#btnAddNew').click(function() {
            window.currentEditingUserId = null;
            $('#assignment_id').val('');
            $('#assignSupportForm')[0].reset();

            $('#user_id option').each(function() {
                if ($(this).data('assigned') == true || $(this).data('assigned') == "true") {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });

            $('#user_id').val(null).trigger('change');
            $('#modalTitle').text('Assign Support');
            $('#assignSupportModal').modal('show');
        });

        let supportTable = $('#supportAssignmentTable').DataTable({
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
                url: "{{ url('fetch/support-assignments') }}",
                type: "POST",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.user_id = $('#filterUser').val();
                    d.assined_to = $('#filterSupport').val();
                }
            },
            columns: [{
                    data: null,
                    render: (data, type, row, meta) => meta.row + 1
                },
                {
                    data: null,
                    defaultContent: 'N/A',
                    render: function(data, type, row) {
                        let url = "{{ route('view_user', ['id' => 'id']) }}".replace('id', row.user_id);
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
                    data: 'assigned_support.name',
                    name: 'assigned_support.name',
                    defaultContent: 'N/A'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'id',
                    className: 'text-center',
                    render: id =>
                        `
                        <button class="btn btn-outline-primary edit-btn" data-id="${id}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-xs btn-outline-danger delete-btn" data-id="${id}"><i class="fas fa-trash"></i></button>`
                }
            ]
        });

        $('#applyFilter').on('click', function(e) {
            e.preventDefault();
            supportTable.ajax.reload();
        });
        $('#resetFilter').on('click', function(e) {
            e.preventDefault();
            $('#filterUser').val('').trigger('change');
            $('#filterSupport').val('').trigger('change');
            supportTable.ajax.reload();
        });




        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.ajax({
                url: "{{ url('edit-support-assignment') }}/" + id,
                type: "GET",
                success: function(res) {
                    if (res.status) {
                        $('#assignment_id').val(res.data.id);
                        window.currentEditingUserId = res.data.user_id;
                        $('#user_id option').each(function() {
                            if ($(this).val() == res.data.user_id) {
                                $(this).prop('disabled', false);
                            } else if ($(this).data('assigned') == true || $(this)
                                .data('assigned') == "true") {
                                $(this).prop('disabled', true);
                            }
                        });

                        $('#user_id').val([res.data.user_id]).trigger('change');
                        $('#assined_to').val(res.data.assined_to);

                        $('#modalTitle').text('Update Assignment');
                        $('#assignSupportModal').modal('show');
                    }
                }
            });
        });

        $('#assignSupportForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('save_support_assignment') }}",
                type: "POST",
                data: $(this).serialize(),
                beforeSend: function() {
                    $('#submitBtn').prop('disabled', true).text('Saving...');
                },
                success: function(res) {
                    if (res.status) {
                        Swal.fire('Success!', res.message, 'success');
                        $('#assignSupportModal').modal('hide');
                        $('#assignSupportForm')[0].reset();
                        $('#user_id').val(null).trigger('change');
                        supportTable.ajax.reload();
                        location.reload();
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                },
                error: function(err) {
                    Swal.fire('Error!', err.responseJSON.message || "Something went wrong",
                        'error');
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Save Assignment');
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('delete-support-assignment') }}/" + id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            supportTable.ajax.reload();
                            Swal.fire('Deleted!', res.message, 'success');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection