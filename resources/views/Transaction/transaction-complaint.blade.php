@extends('layouts.app')

@section('title', 'Transaction Complaint')
@section('page-title', 'Transaction Complaint')

@section('content')

<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <button type="button" class="btn buttonColor" data-bs-toggle="modal" data-bs-target="#serviceModal">
            <i class="bi bi-plus fs-6 me-1"></i> Service
        </button>
    </div>
</div>

<div class="row g-4">

    {{-- Complaints Table --}}
    <div class="col-12">
        <div class="card border shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Registered Complaints</h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="complaintsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reference No</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Resolved At</th>
                                <th>Admin Notes</th>
                                <th>Attachment</th>
                                <th>Priority</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Register Complaints Modal -->

<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Register Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="complaintForm" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Service Name<span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" required>
                                <option value="">-- Select Service --</option>
                                @foreach ($services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->service_name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none" id="err_service_id"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select" required>
                                @foreach ($priorities as $p)
                                <option value="{{ $p }}" {{ $p == 'normal' ? 'selected' : '' }}>
                                    {{ ucfirst($p) }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none" id="err_priority"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Category<span class="text-danger">*</span></label>
                            <select name="category" class="form-select">
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $value)
                                <option value="{{ $value->id }}">{{ $value->category_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none" id="err_category"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Attachment </label>
                            <input type="file" name="attachment" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-danger d-none" id="err_attachment"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description<span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Write complaint..." required min="20"></textarea>
                            <small class="text-danger d-none" id="err_description"></small>
                        </div>


                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn buttonColor">
                                Register
                            </button>
                            <button type="button" class="btn btn-secondary">
                                Cancel
                            </button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function resetErrors() {
        $('small[id^="err_"]').addClass('d-none').text('');
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderRows(complaints) {
        if (!complaints || complaints.length === 0) {
            return `<tr><td colspan="9" class="text-center text-muted">No complaints found.</td></tr>`;
        }

        let rows = '';
        complaints.forEach(c => {
            const resolvedAt = c.resolved_at ? c.resolved_at : '-';
            const adminNotes = c.admin_notes ? c.admin_notes : '-';
            const attachment = c.attachment_path ?
                `<a href="/storage/${escapeHtml(c.attachment_path)}" target="_blank">View</a>` :
                '-';

            rows += `
                <tr>
                    <td>${c.id}</td>
                    <td>${escapeHtml(c.reference_number)}</td>
                    <td>${escapeHtml(c.service_name)}</td>
                    <td><span class="badge bg-secondary text-uppercase">${escapeHtml(c.status)}</span></td>
                    <td>${escapeHtml(resolvedAt)}</td>
                    <td style="min-width:200px;">${escapeHtml(adminNotes)}</td>
                    <td>${attachment}</td>
                    <td class="text-uppercase">${escapeHtml(c.priority)}</td>
                    <td>${escapeHtml(c.created_at ?? '-')}</td>
                </tr>
            `;
        });

        return rows;
    }

    $('#complaintForm').on('submit', function(e) {
        e.preventDefault();
        resetErrors();

        const form = document.getElementById('complaintForm');
        const formData = new FormData(form);

        $.ajax({
            url: "{{ route('complaints.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {
                form.reset();
                $('#complaintsTbody').html(renderRows(res.complaints));

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message || 'Complaint registered successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
            },

            error: function(xhr) {
                let msg = 'Something went wrong!';
                if (xhr.status === 422) msg = 'Validation error! Please check fields.';

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg
                });

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;

                    if (errors.service_name) $('#err_service_name').removeClass('d-none').text(
                        errors.service_name[0]);
                    if (errors.description) $('#err_description').removeClass('d-none').text(errors
                        .description[0]);
                    if (errors.priority) $('#err_priority').removeClass('d-none').text(errors
                        .priority[0]);
                    if (errors.category) $('#err_category').removeClass('d-none').text(errors
                        .category[0]);
                    if (errors.attachment) $('#err_attachment').removeClass('d-none').text(errors
                        .attachment[0]);
                }
            }
        });
    });
</script>

@endsection