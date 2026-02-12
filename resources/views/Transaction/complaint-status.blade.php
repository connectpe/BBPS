@extends('layouts.app')

@section('title', 'Complaint Status')
@section('page-title', 'Complaint Status')

@section('content')

<div class="container">
    <div class="row g-4">

        <!-- Search Form -->
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header position-relative">
                    <h5 class="mb-0">Check Complaint Status</h5>
                    <img src="{{ asset('assets/image/icon_logo.svg') }}" alt="logo" style="position:absolute; top:10px; right:10px; width:60px; height:auto;">
                </div>


                <div class="card-body">
                    <form id="transactionForm">
                        @csrf
                        <div class="row g-3 align-items-end">

                            {{-- ONLY Reference No --}}
                            <div class="col-6 col-md-6">
                                <label class="form-label">Complaint Ticket No</label>
                                <input type="text"
                                    id="ticket_number"
                                    name="ticket_number"
                                    class="form-control"
                                    placeholder="Enter Complaint Ticket No"
                                    required>
                                <small class="text-danger d-none" id="err_ticket"></small>
                            </div>

                            <div class="col-12 col-md-4">
                                <button type="submit" class="btn buttonColor">
                                    Check
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
        $('#err_ticket').addClass('d-none').text('');
    }

    function badgeClassByStatus(st) {
        if (st === 'Closed') {
            return 'danger';
        } else if (st === 'In Progress') {
            return 'warning';
        } else if (st === 'Open') {
            return 'primary';
        } else {
            return 'secondary';
        }

    }

    $('#transactionForm').on('submit', function(e) {
        e.preventDefault();
        resetErrors();
        $('#resultArea').html('');

        const ticket = $('#ticket_number').val();

        $.ajax({
            url: "{{ route('complaint.status.check') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ticket_number: ticket
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
                                    <th>Ticket No</th>
                                    <th>Service</th>
                                    <th>Resolved At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>${d.ticket_number}</td>
                                    <td>${d.service_name}</td>
                                    <td>${d.resolved_at}</td>
                                    <td>
                                        <span class="badge bg-${bClass}">
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
                    $('#err_ticket')
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