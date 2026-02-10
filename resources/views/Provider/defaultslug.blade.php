@extends('layouts.app')

@section('title', 'Default Slug')
@section('page-title', 'Default Slug')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0 text-dark fw-bold">Default Slug List</h5>
                <button class="btn buttonColor btn-sm" id="openCreateModal" data-bs-toggle="modal" data-bs-target="#slugModal">
                    <i class="bi bi-plus-lg"></i> Configure Default Slug
                </button>
            </div>
            <div class="card-body">
                <table id="defaultSlugTable" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>SERVICE</th>
                            <th>PROVIDER</th>
                            <th>CREATED AT</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="slugModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Set Default Provider Slug</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="slugForm">
                    @csrf
                    <input type="hidden" id="default_id" name="id" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Service</label>
                            <select id="modal_service_id" name="service_id" class="form-select searchable-select" required>
                                <option value="">-- Choose Service --</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->service_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Default Provider</label>
                            <select id="modal_provider_id" name="provider_id" class="form-select" disabled required>
                                <option value="">-- Select Service First --</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="saveBtn">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {
            $('.searchable-select').select2({
                dropdownParent: $('#slugModal'),
                width: '100%'
            });
            let table = $('#defaultSlugTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('fetch/default-slug') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                    },
                    error: function(xhr) {
                        console.error("DataTable Error: ", xhr.responseText);
                        Swal.fire('Error', 'Failed to fetch data from server.', 'error');
                    }
                },
                columns: [{
                        data: null,
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'service.service_name',
                        name: 'service.service_name',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'provider.provider_name',
                        name: 'provider.provider_name',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'id',
                        orderable: false,
                        render: function(id, type, row) {
                            return `
                                <button class="btn btn-sm buttonColor text-white editDefaultBtn" 
                                    data-id="${id}" data-service="${row.service_id}" data-provider="${row.provider_id}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>`;
                        }
                    }
                ]
            });

            $('#modal_service_id').on('change', function() {
                let serviceId = $(this).val();
                let providerSelect = $('#modal_provider_id');

                providerSelect.prop('disabled', true).html(
                    '<option value="">Loading providers...</option>');

                if (!serviceId) {
                    providerSelect.html('<option value="">-- Select Service First --</option>');
                    return;
                }

                $.ajax({
                    url: "{{ url('admin/fetch/providers-by-service') }}/" + serviceId,
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        providerSelect.empty().append(
                            '<option value="">-- Choose Provider --</option>');
                        if (res.status && res.data.length > 0) {
                            $.each(res.data, function(key, provider) {
                                providerSelect.append(
                                    `<option value="${provider.id}">${provider.provider_name}</option>`
                                );
                            });
                            providerSelect.prop('disabled', false);
                        } else {
                            providerSelect.html(
                                '<option value="">No Active Providers found</option>');
                        }
                    },
                    error: function() {
                        providerSelect.html(
                            '<option value="">Error fetching providers</option>');
                    }
                });
            });

            $('#openCreateModal').on('click', function() {
                $('#default_id').val('');
                $('#slugForm')[0].reset();
                $('#modal_service_id').val('').trigger('change');
                $('#modalTitle').text('Set Default Provider Slug');
                $('#saveBtn').text('Save Configuration');
            });
            $(document).on('click', '.editDefaultBtn', function() {
                let id = $(this).data('id');
                let serviceId = $(this).data('service');
                let providerId = $(this).data('provider');

                $('#default_id').val(id);
                $('#modalTitle').text('Edit Default Provider Configuration');
                $('#saveBtn').text('Update Configuration');
                $('#modal_service_id').val(serviceId).trigger('change');
                setTimeout(function() {
                    $('#modal_provider_id').val(providerId);
                }, 1000);

                $('#slugModal').modal('show');
            });
            $('#slugForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#default_id').val();
                let editUrl = "{{ route('edit-default-provider', ':id') }}".replace(':id', id);
                let postUrl = id ? editUrl : "{{ route('add-default-provider') }}";
                $.ajax({
                    url: postUrl,
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Success!', res.message, 'success');
                            $('#slugModal').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Something went wrong';
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            errorMsg = '';
                            $.each(errors, function(field, messages) {
                                errorMsg += messages.join("<br>") + "<br>";
                            });
                        } else {
                            errorMsg = xhr.responseJSON?.message || 'Server Error';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMsg,
                        });
                    }
                });
            });
            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This configuration will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ url('delete-default-provider') }}/" + id, {
                            _token: "{{ csrf_token() }}"
                        }, function(res) {
                            if (res.status) {
                                Swal.fire('Deleted!', 'Configuration has been removed.',
                                    'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', 'Could not delete the record.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
