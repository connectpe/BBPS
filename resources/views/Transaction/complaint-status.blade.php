@extends('layouts.app')

@section('title', 'Complaint Status')
@section('page-title', 'Complaint Status')

@section('content')

<div class="container">
    <div class="row g-4">

        <!-- Search Form -->
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Check Complaint Status</h5>
                </div>

                <div class="card-body">
                    <form id="transactionForm">
                        @csrf
                        <div class="row g-3 align-items-end">

                            {{-- ONLY Reference No --}}
                            <div class="col-12 col-md-8">
                                <label class="form-label">Complaint Reference No</label>
                                <input type="text"
                                       id="reference_number"
                                       name="reference_number"
                                       class="form-control"
                                       placeholder="Enter Complaint Reference No"
                                       required>
                                <small class="text-danger d-none" id="err_reference"></small>
                            </div>

                            <div class="col-12 col-md-4">
                                <button type="submit" class="btn buttonColor w-100">
                                    Check Status
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Result -->
        <div class="col-12" id="resultArea"></div>

    </div>
</div>

<script>
function resetErrors() {
    $('#err_reference').addClass('d-none').text('');
}

function badgeClassByStatus(st) {
    st = (st || '').toLowerCase();
    if (st === 'resolved') return 'bg-success';
    if (st === 'in_progress') return 'bg-info';
    if (st === 'closed') return 'bg-secondary';
    return 'bg-warning'; // open
}

$('#transactionForm').on('submit', function(e) {
    e.preventDefault();
    resetErrors();
    $('#resultArea').html('');

    const reference = $('#reference_number').val();

    $.ajax({
        url: "{{ route('complaint.status.check') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            reference_number: reference
        },
        success: function(res) {

            const d = res.data;
            const bClass = badgeClassByStatus(d.complaint_status);

            let html = `
            <div class="card border shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Complaint Status</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Complaint Reference No</th>
                                    <th>Service</th>
                                    <th>Resolved At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>${d.reference_number}</td>
                                    <td>${d.service_name}</td>
                                    <td>${d.resolved_at}</td>
                                    <td>
                                        <span class="badge ${bClass}">
                                            ${d.complaint_status}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;

            $('#resultArea').html(html);
        },
        error: function(xhr) {

            if (xhr.status === 422 && xhr.responseJSON?.errors?.reference_number) {
                $('#err_reference')
                    .removeClass('d-none')
                    .text(xhr.responseJSON.errors.reference_number[0]);
                return;
            }

            let msg = 'Complaint not found.';
            if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;

            $('#resultArea').html(
                `<div class="alert alert-danger">${msg}</div>`
            );
        }
    });
});
</script>

@endsection
