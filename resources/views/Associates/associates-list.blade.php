@extends('layouts.app')

@section('title', 'Associated Partner')
@section('page-title', 'Associated Partner')

@php
use App\Facades\FileUpload;
@endphp

@section('page-button')
<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <button type="button" class="btn buttonColor btn-sm px-4 shadow-sm" onclick="openAssociateModal()">
            <i class="fas fa-plus me-1"></i> Add Associate
        </button>
    </div>
</div>
@endsection

@section('content')



<div class="row mt-3 g-3" id="agreementList">

    @forelse($associates ?? [] as $associate)

    <div class="col-xl-3 col-lg-4 col-md-6">

        <div class="card border shadow-sm h-100">

            <!-- Image -->
            <div class="p-2 text-center">
                <img src="{{ FileUpload::getFilePath($associate->logo) }}"
                    class="img-fluid rounded associate-img cursor-pointer"
                    onclick="showImage('{{ FileUpload::getFilePath($associate->logo) }}','Associates Image')"
                    style="height:95px; object-fit:cover;">
            </div>

            <!-- Body -->
            <div class="card-body py-2 text-center">

                <h6 class="mb-1 small fw-semibold text-truncate">
                    {{ $associate->name }}
                </h6>

                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control form-control-sm text-truncate"
                        value="{{ $associate->referell_url }}" readonly>

                    <button class="btn btn-light border" onclick="copyReferral(this, '{{ $associate->referell_url }}')">

                        <i class="fa-regular fa-copy copy-icon"></i>
                    </button>
                </div>

            </div>

            <!-- Footer -->
            <div class="card-footer bg-white border-0 px-2 pb-2">

                <div class="d-flex gap-1">

                    <a href="javascript:void(0)" onclick="openAssociateModal('{{ $associate->id }}')"
                        class="btn btn-light btn-sm border w-100">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>

                </div>

            </div>

        </div>
    </div>
    @empty
    <div class="col-12 text-center text-muted py-4">
        No Partner Associated
    </div>
    @endforelse

</div>

<!-- ================= MODAL ================= -->
<div class="modal fade" id="agreementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="associateForm" enctype="multipart/form-data">

                @csrf
                <input type="hidden" id="edit_id" name="id">

                <div class="modal-header">
                    <h5 class="modal-title">Associate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Name<small class="text-danger">*</small> </label>
                        <input type="text" name="associates_name" id="associates_name" class="form-control"
                            placeholder="Enter Associate Name">
                    </div>

                    <div class="mb-3">
                        <label>Referral URL<small class="text-danger">*</small> </label>
                        <input type="url" name="referrel_url" id="referrel_url" class="form-control"
                            placeholder="Enter Referral URL">
                    </div>

                    <div class="mb-3">
                        <label>Priority<small class="text-danger">*</small> </label>
                        <input type="number" name="priority" id="priority" class="form-control"
                            placeholder="Enter Priority">
                    </div>

                    <div class="mb-3">
                        <label>Logo<small class="text-danger">*</small> </label>
                        <input type="file" name="associates_logo" id="associates_logo" class="form-control">
                        <div class="mt-2" id="logoPreviewWrapper" style="display:none;">
                            <img id="logoPreview" src=""
                                style="height:80px; width:auto; border-radius:6px; border:1px solid #ddd; padding:3px;">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn buttonColor" id="addAssociateButton"
                        onclick="submitAssociateForm()">
                        Submit
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

    function copyReferral(btn, url) {

        navigator.clipboard.writeText(url).then(function () {
            let icon = btn.querySelector('.copy-icon');
            icon.classList.remove('fa-copy');
            icon.classList.add('fa-circle-check', 'text-success');
            btn.disabled = true;

            setTimeout(() => {
                icon.classList.remove('fa-circle-check', 'text-success');
                icon.classList.add('fa-copy');
                btn.disabled = false;
            }, 1500);

        }).catch(function () {
            alert('Copy failed');
        });
    }

    function openAssociateModal(id = null) {

        $('#associateForm')[0].reset();
        $('#edit_id').val('');
        $('#logoPreviewWrapper').hide();
        $('#logoPreview').attr('src', '');


        $('#addAssociateButton').text('Submit');

        if (id) {

            $.ajax({
                url: "{{route('edit_associates')}}",
                type: "GET",
                data: { id: id },

                success: function (res) {

                    if (res.status) {

                        $('#associates_name').val(res.data.name);
                        $('#referrel_url').val(res.data.referell_url);
                        $('#priority').val(res.data.priority);

                        $('#edit_id').val(res.data.id);

                        if (res.data.logo) {
                            $('#logoPreview').attr('src', res.data.logo);
                            $('#logoPreviewWrapper').show();
                        }
                        $('#addAssociateButton').text('Update');

                        $('#agreementModal').modal('show');

                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });

        } else {
            $('#agreementModal').modal('show');
        }
    }


    function submitAssociateForm() {

        var form = $('#associateForm')[0];
        var formData = new FormData(form);

        $('#addAssociateButton').prop('disabled', true).text('Saving...');

        $.ajax({
            url: "{{ route('add_associates') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            success: function (res) {

                $('#addAssociateButton').prop('disabled', false).text('Submit');

                if (res.status) {

                    $('#associateForm')[0].reset();
                    $('#edit_id').val('');
                    $('#agreementModal').modal('hide');

                    Swal.fire('Success', res.message, 'success');

                    setTimeout(() => location.reload(), 1200);

                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },

            error: function (xhr) {

                $('#addAssociateButton').prop('disabled', false).text('Submit');

                let firstError = xhr.responseJSON?.errors ?
                    Object.values(xhr.responseJSON.errors)[0][0] :
                    'Something went wrong';

                Swal.fire('Validation Error', firstError, 'error');
            }
        });
    }

</script>

@endsection