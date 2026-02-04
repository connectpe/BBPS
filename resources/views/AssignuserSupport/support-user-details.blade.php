@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Support User List</h4>
            <button type="button" class="btn buttonColor" data-toggle="modal" data-target="#addSupportModal"
                data-bs-toggle="modal" data-bs-target="#addSupportModal">
                <i class="fa fa-plus"></i> Add Support
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="supportTableServerSide" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Created At</th>
                            <th>ACtion</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addSupportModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="addSupportForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Support User</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="errorBox" class="alert alert-danger" style="display:none;"></div>
                        <div class="form-group mb-2"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                        <div class="form-group mb-2"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
                        <div class="form-group mb-2"><label>Mobile Number</label><input type="text" name="mobile" class="form-control" maxlength="10" required></div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group mb-2"><label>Password</label><input type="password" name="password" class="form-control" required></div></div>
                            <div class="col-md-6"><div class="form-group mb-2"><label>Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn buttonColor" id="btnSubmit">Save Support User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSupportModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editSupportForm">
                    @csrf
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Support User</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="editErrorBox" class="alert alert-danger" style="display:none;"></div>
                        <div class="form-group mb-2"><label>Full Name</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                        <div class="form-group mb-2"><label>Email Address</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                        <div class="form-group mb-2"><label>Mobile Number</label><input type="text" name="mobile" id="edit_mobile" class="form-control" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn buttonColor" id="editBtnSubmit">Update Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#supportTableServerSide').DataTable({
                processing: true,
                serverSide: true,
                dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                    "<'row'<'col-12'tr>>" + "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
                buttons: [{ extend: 'excelHtml5', text: 'Excel', className: 'btn buttonColor btn-sm' }],
                ajax: {
                    url: "{{ url('fetch/support-user-list-server') }}",
                    type: 'POST',
                    data: function(d) { d._token = "{{ csrf_token() }}"; },
                },
                columns: [
                    { data: null, orderable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'created_at', render: d => formatDateTime(d) },
                    { data: null, orderable: false, render: r => `
                        <button class="btn btn-sm btn-primary editSupport" data-id="${r.id}"><i class="fa fa-edit"></i></button>`
                    }
                ]
            });

            $(document).on('click', '.editSupport', function() {
                let id = $(this).data('id');
                $('#editErrorBox').hide();
                $.get("{{ url('admin/get-s-member') }}/" + id, function(res) {
                    if(res.status) {
                        $('#edit_user_id').val(res.data.id);
                        $('#edit_name').val(res.data.name);
                        $('#edit_email').val(res.data.email);
                        $('#edit_mobile').val(res.data.mobile);
                        $('#editSupportModal').modal('show');
                    }
                });
            });

            $('#editSupportForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#edit_user_id').val();
                let $btn = $('#editBtnSubmit');
                $btn.prop('disabled', true).text('Updating...');
                $.ajax({
                    url: "{{ url('admin/edit-s-member') }}/" + id,
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        $btn.prop('disabled', false).text('Update Member');
                        if (res.status) {
                            Swal.fire({ icon: 'success', title: 'Updated!', text: res.message, timer: 1500, showConfirmButton: false });
                            $('#editSupportModal').modal('hide');
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).text('Update Member');
                        if (xhr.status === 422) {
                            $('#editErrorBox').html(Object.values(xhr.responseJSON.errors).flat().join('<br>')).show();
                        }
                    }
                });
            });

            $('#addSupportForm').on('submit', function(e) {
                e.preventDefault();
                let $btn = $('#btnSubmit');
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                $.ajax({
                    url: "{{ route('add.support.member') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        $btn.prop('disabled', false).text('Save Support User');
                        if (res.status) {
                            Swal.fire({ icon: 'success', title: 'Success!', text: res.message, timer: 1500, showConfirmButton: false });
                            $('#addSupportModal').modal('hide');
                            $('#addSupportForm')[0].reset();
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).text('Save Support User');
                        if (xhr.status === 422) {
                            $('#errorBox').html(Object.values(xhr.responseJSON.errors).flat().join('<br>')).show();
                        }
                    }
                });
            });
        });
    </script>
@endsection