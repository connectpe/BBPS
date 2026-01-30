@extends('layouts.app')

@section('title', 'Providers')
@section('page-title', 'Providers')

@section('content')

<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <button type="button" class="btn buttonColor" data-bs-toggle="modal" data-bs-target="#providerModal">
            <i class="bi bi-plus fs-6 me-1"></i> Provider
        </button>
    </div>
</div>


<div class="col-12 col-md-10 col-lg-12">
    <div class="card shadow-sm">

        <div class="card-body pt-4">
            <div class="table-responsive">
                <table id="providerTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Provider Name</th>
                            <th>Provider Slug</th>
                            <th>Is_active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="providerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="providerForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label for="service_id">Service<span class="text-danger">*</span> </label>
                            <select name="service_id" id="service_id" class="form-control">
                                <option value="">--Select Service--</option>
                                @foreach($globalServices as $service)
                                <option value="{{$service->id}}" {{$service->id == old('service_id') ? 'selected' : ''}}>{{$service->service_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="provider_name">Provider Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="provider_name" id="provider_name"
                                value="{{ old('provider_name') }}" placeholder="Provider Name">
                        </div>
                    </div>
                </div>

                <input type="hidden" id="edit_service_id">
                <input type="hidden" id="form_type" value="add">


                <div class="modal-footer">
                    <button type="submit" class="btn buttonColor">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {

        var table = $('#providerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('fetch') }}/global-service/0",
                type: 'POST',
                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
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
                searchPlaceholder: "Search Providers..."
            },

            columns: [{
                    data: 'id'
                },
                {
                    data: 'service_name'
                },
                {
                    data: 'slug',
                },
                {
                    data: 'service_type'
                },
                {
                    data: 'is_active',
                    render: function(data, type, row) {
                        let checked = data == '1' ? 'checked' : ''; // toggle state
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer" type="checkbox" ${checked}
                                    onchange="changeService('${row.id}', 'is_active','Service')">
                            </div>
                        `;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
            <button class="btn btn-sm btn-primary"
                onclick="openEditService(${row.id}, '${row.service_name}')">
                <i class="fa fa-edit"></i>
            </button>
        `;
                    },
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // $('#applyFilterproviderTable').on('click', function() {
        //     table.ajax.reload();
        // });

        // $('#resetFilterproviderTable').on('click', function() {
        //     $('#filterName').val('');
        //     $('#filterEmail').val('');
        //     $('#filterStatus').val('');
        //     table.ajax.reload();
        // });
    });

    function changeService(id, type, text = 'This Record') {
        Swal.fire({
            title: 'Are you sure to change status of ' + text + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.service_toggle') }}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        service_id: id,
                        type: type
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message ?? 'Status updated successfully',
                            timer: 2000,
                            showConfirmButton: true
                        });
                    },
                    error: function(xhr) {
                        let title = 'Error';
                        let message = 'Something went wrong!';

                        if (xhr.status === 422) {
                            title = 'Validation Error';

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                message = xhr.responseJSON.errors[firstKey][0];
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: title,
                            html: message,
                            timer: 2000,
                            showConfirmButton: true
                        });
                    }

                });
            }
        });
    }
</script>


<script>
    $(document).ready(function() {

        $('#providerForm').on('submit', function(e) {
            e.preventDefault();

            let serviceId = $('#service_id').val();
            let providerName = $('#provider_name').val();
            let formType = $('#form_type').val();

            let url = "{{ route('add_provider') }}";

            if (formType === 'edit') {
                url = "{{ url('admin/service/edit') }}/" + serviceId;
            }

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    serviceId: serviceId,
                    providerName: providerName
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#providerModal').modal('hide');
                        $('#providerForm')[0].reset();
                        $('#form_type').val('add');
                        $('#edit_service_id').val('');
                        $('#providerTable').DataTable().ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    let message = 'Something went wrong';

                    // Validation errors (Laravel 422)
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        const firstErrorKey = Object.keys(errors)[0];

                        if (firstErrorKey) {
                            message = errors[firstErrorKey][0];
                        }
                    }
                    // Other backend errors
                    else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire('Error', message, 'error');
                }

            });
        });

    });

    function openEditService(id, name) {
        $('#service_name').val(name);
        $('#edit_service_id').val(id);
        $('#form_type').val('edit');
        $('#providerModalTitle').text('Edit Service');
        $('#providerModal').modal('show');
    }
</script>
<script>
    $('#providerModal').on('hidden.bs.modal', function() {
        $('#providerForm')[0].reset();
        $('#form_type').val('add');
        $('#edit_service_id').val('');
        $('.modal-title').text('Create Service');
    });
</script>

@endsection