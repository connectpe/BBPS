@extends('layouts.app')

@section('title', 'Agreement')
@section('page-title', 'Agreement')

@section('page-button')
    @if (Auth::user()->role_id == 1)
        <div class="row align-items-center mb-2">
            <div class="col-auto ms-auto">
                <button type="button" class="btn buttonColor btn-sm px-4 shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#agreementModal">
                    <i class="fas fa-plus me-1"></i> Add Agreement
                </button>
            </div>
        </div>
    @endif
@endsection

@section('content')




    <div class="row mt-4" id="agreementList">

        @forelse($agreements as $agreement)
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100 d-flex flex-column">
                    <div class="card-body text-center d-flex flex-column justify-content-between">

                        @php
                            $ext = pathinfo($agreement->file_path, PATHINFO_EXTENSION);
                        @endphp
                        <div style="height:200px; display:flex; align-items:center; justify-content:center;">

                            @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                                <img src="{{ asset('storage/' . $agreement->file_path) }}"
                                    style="max-height:100%; max-width:100%; object-fit:cover; border-radius:8px;">
                            @elseif($ext == 'pdf')
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size:60px;"></i>
                                    <p class="py-1">PDF Document</p>
                                </div>
                            @endif

                        </div>
                        <h6 class="mt-3 mb-0 text-truncate">{{ $agreement->file_name }}</h6>

                    </div>
                    <div class="card-footer text-center bg-white mt-auto">
                        <a href="{{ asset('storage/' . $agreement->file_path) }}" target="_blank"
                            class="btn btn-sm buttonColor me-1">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a href="{{ asset('storage/' . $agreement->file_path) }}" download class="btn btn-sm btn-success">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>

                </div>
            </div>

        @empty
            <div class="col-12 text-center text-muted">
                No agreements uploaded yet.
            </div>
        @endforelse

    </div>



    <div class="modal fade" id="agreementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form id="agreementForm" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Add Agreement or Declaration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Agreement Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Enter agreement name">
                            <small class="text-danger name_error"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Agreement (Image / PDF)</label>
                            <input type="file" name="file" id="file" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-danger file_error"></small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn buttonColor" id="saveAgreementBtn">
                            Save
                        </button>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {

            $('#agreementForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $('.file_error').text('');
                $('#saveAgreementBtn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('agreement.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(response) {
                        $('#saveAgreementBtn').prop('disabled', false).text('Save');

                        if (response.status == true) {
                            $('#agreementForm')[0].reset();

                            let modalEl = document.getElementById('agreementModal');
                            let modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) {
                                modal.hide();
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },

                    error: function(xhr) {
                        $('#saveAgreementBtn').prop('disabled', false).text('Save');
                        if (xhr.status == 422) {
                            let errors = xhr.responseJSON.errors;
                            $('.name_error').text('');
                            $('.file_error').text('');
                            let errorMsg = '';
                            if (errors.name) {
                                $('.name_error').text(errors.name[0]);
                                errorMsg = errors.name[0];
                            }
                            if (errors.file) {
                                $('.file_error').text(errors.file[0]);
                                errorMsg = errorMsg ? errorMsg + '\n' + errors.file[0] : errors
                                    .file[0];
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMsg
                            });
                        } else {
                            let message = 'Something went wrong.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                message = xhr.responseText;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });
                        }
                    }
                });
            });

        });
    </script>
@endsection
