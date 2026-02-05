@extends('layouts.app')

@section('title', 'Complaint Categories')
@section('page-title', 'Categories')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Complaint Categories List</h6>
            <button class="btn btn-sm buttonColor shadow-sm" data-bs-toggle="modal" data-bs-target="#categoryModal"
                onclick="resetForm()">
                <i class="bi bi-plus-circle me-1"></i> Add New Category
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="categoryTable" class="table table-striped border w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Category Name</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Complaint Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="categoryForm">
                    @csrf
                    <input type="hidden" name="category_id" id="category_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="category_name" id="category_name"
                                placeholder="e.g. Technical Issue" required>
                            <small class="text-danger error-category_name"></small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="modalSubmitBtn" class="btn buttonColor">Save Category</button>
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
            var table = $('#categoryTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ url('fetch/complaint-category/0/all') }}",
                    type: 'POST',
                    error: function(xhr) {
                        console.log("DT Error:", xhr.status, xhr.responseText);
                    }
                },
                columns: [{
                        data: 'id',
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'category_name',
                        defaultContent: '-'
                    },

                    {
                        data: 'status',
                        orderable: false,
                        searchable: false,
                        render: (data, type, row) => `
                            <div class="form-check form-switch">
                                <input class="form-check-input status-toggle" type="checkbox"
                                    data-id="${row.id}" ${String(data) === "1" ? "checked" : ""}>
                            </div>
                        `
                    },

                    {
                        data: 'created_at',
                       name: 'created_at'
                    },

                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: (data, type, row) => `
                            <button class="btn btn-outline-primary btn-sm edit-category"
                                data-id="${row.id}" data-name="${row.category_name}">
                                <i class="fas fa-edit"></i>
                            </button>
                        `
                    }
                ]
            });

            $('#categoryForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-category_name').text('');

                let id = $('#category_id').val();
                let targetUrl = id ?
                    "{{ url('admin/update-complaint-category') }}/" + id :
                    "{{ route('add_complaint_category') }}";

                $.ajax({
                    url: targetUrl,
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            $('#categoryModal').modal('hide');
                            Swal.fire('Success', res.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            let errors = xhr.responseJSON.errors;
                            if (errors.category_name) $('.error-category_name').text(errors
                                .category_name[0]);
                        } else {
                            Swal.fire('Error', 'Server Error', 'error');
                        }
                    }
                });
            });

            $(document).on('click', '.edit-category', function() {
                $('#category_id').val($(this).data('id'));
                $('#category_name').val($(this).data('name'));
                $('#modalTitle').text('Update Category');
                $('#categoryModal').modal('show');
            });


            $(document).on('change', '.status-toggle', function() {
                let $toggle = $(this);
                let id = $toggle.data('id');
                let newStatus = $toggle.is(':checked') ? 1 : 0;


                $toggle.prop('checked', !newStatus);

                Swal.fire({
                    title: 'Are you sure?',
                    text: newStatus === 1 ?
                        'Do you want to ACTIVATE this category?' :
                        'Do you want to DEACTIVATE this category?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, change it',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('status_complaint_category', ':id') }}".replace(
                            ':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            status: newStatus
                        },
                        success: function(res) {
                            if (res.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                $toggle.prop('checked', res.data.status == 1);
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message || 'Failed', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Server Error', 'error');
                        }
                    });
                });
            });

        });

        function resetForm() {
            $('#categoryForm')[0].reset();
            $('#category_id').val('');
            $('#modalTitle').text('Add Complaint Category');
            $('.error-category_name').text('');
        }
    </script>
@endsection
