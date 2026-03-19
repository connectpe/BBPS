@extends('layouts.app')

@section('title', 'Business Categories')
@section('page-title', 'Business Categories')

@section('page-button')
    <div class="d-flex justify-content-end mb-2">
        <button type="button" class="btn btn-sm buttonColor shadow-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
            <i class="fa fa-plus me-1"></i> Add Category
        </button>
    </div>
@endsection

@section('content')

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="categoryTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="display:none;">ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="businessCategoryForm">
                    @csrf
                    <input type="hidden" name="category_id" id="category_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalTitle">Add Business Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Enter category name">
                            <small class="text-danger name_error"></small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn buttonColor" id="saveCategoryBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        let table;
        function resetForm() {
            $('#businessCategoryForm')[0].reset();
            $('#category_id').val('');
            $('#categoryModalTitle').text('Add Business Category');
            $('#saveCategoryBtn').text('Save').prop('disabled', false);
            $('.name_error').text('');
            $('#name').removeClass('is-invalid');
        }
        $(document).ready(function() {
            table = $('#categoryTable').DataTable({
                processing: true,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                ajax: {
                    url: "{{ url('fetch/business-categories/0/all') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'id',
                        visible: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'slug'
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            let checked = data == 1 ? 'checked' : '';
                            return `
                                <div class="form-check form-switch">
                                    <input class="form-check-input statusToggle"
                                        type="checkbox"
                                        data-id="${row.id}"
                                        ${checked}>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            return formatDateTime(data);
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button type="button" class="btn btn-sm buttonColor editBtn" 
                                    data-id="${row.id}" 
                                    data-name="${row.name}">
                                    <i class="fa fa-edit"></i>
                                </button>
                            `;
                        }
                    }
                ]
            });

            $('#businessCategoryForm').on('submit', function(e) {
                e.preventDefault();
                $('.name_error').text('');
                $('#name').removeClass('is-invalid');
                let categoryId = $('#category_id').val();
                let url = categoryId ? "{{ route('business_category.update') }}" :
                    "{{ route('business_category.store') }}";
                let btnText = categoryId ? 'Updating...' : 'Saving...';
                $('#saveCategoryBtn').prop('disabled', true).text(btnText);
                $.ajax({
                    url: url,
                    type: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        $('#saveCategoryBtn').prop('disabled', false).text('Save');
                        if (response.status) {
                            $('#businessCategoryForm')[0].reset();
                            $('#category_id').val('');
                            let modalEl = document.getElementById('categoryModal');
                            let modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                            $('#categoryModalTitle').text('Add Business Category');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Something went wrong.'
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#saveCategoryBtn').prop('disabled', false).text('Save');
                        $('.name_error').text('');
                        $('#name').removeClass('is-invalid');
                        if (xhr.status === 422) {
                            let response = xhr.responseJSON;
                            if (response?.errors?.name) {
                                $('#name').addClass('is-invalid');
                                $('.name_error').text(response.errors.name[0]);
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: response?.message ||
                                    'Please correct the highlighted field and try again.'
                            });

                        } else if (xhr.status === 419) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Session Expired',
                                text: 'Your session has expired. Please refresh the page and try again.'
                            });

                        } else if (xhr.status === 404) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Not Found',
                                text: 'The requested record or endpoint could not be found.'
                            });

                        } else if (xhr.status === 500) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: xhr.responseJSON?.message ||
                                    'An unexpected server error occurred. Please try again.'
                            });

                        } else if (xhr.responseJSON?.message) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON.message
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }

                        console.error('AJAX Error:', xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                resetForm();

                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#category_id').val(id);
                $('#name').val(name);
                $('#categoryModalTitle').text('Edit Business Category');
                $('#saveCategoryBtn').text('Update');

                let modal = new bootstrap.Modal(document.getElementById('categoryModal'));
                modal.show();
            });

            $(document).on('change', '.statusToggle', function() {
                let toggle = $(this);
                let id = toggle.data('id');
                let status = toggle.is(':checked') ? 1 : 0;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to change status?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('business_category.status') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id,
                                status: status
                            },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Updated',
                                        text: res.message,
                                        timer: 1000,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function() {toggle.prop('checked', !status);}
                        });
                    } else {toggle.prop('checked', !status);}
                });
            });
        });
    </script>
@endsection
