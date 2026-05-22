@extends('layouts.app')

@section('title', 'Complaint Status')
@section('page-title', 'Complaint Status')

@section('content')

<style>
    .status-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #ddd;
        background: #fff;
    }

    .title-text {
        font-size: 22px;
        font-weight: 700;
        color: #111;
        margin-bottom: 20px;
    }

    .input-line,
    .select-line {
        border: none;
        border-bottom: 1px solid #999;
        border-radius: 0;
        padding-left: 0;
        box-shadow: none !important;
        background-color: transparent;
    }

    .input-line:focus,
    .select-line:focus {
        border-bottom: 1px solid #4b4bb7;
    }

    .label-text {
        font-weight: 600;
        color: #444;
        margin-bottom: 4px;
    }

    .check-btn {
        background: #4b4bb7;
        color: #fff;
        padding: 8px 24px;
        border: none;
        border-radius: 4px;
    }

    .check-btn:hover {
        background: #3c3ca5;
    }

    .logo-img {
        position: absolute;
        right: 20px;
        top: 15px;
        width: 90px;
    }

    .result-table {
        width: 100%;
        max-width: 350px;
        margin-top: 15px;
    }

    .result-table td {
        border: 1px solid #999;
        padding: 8px 12px;
    }

    @media(max-width:768px){
        .logo-img{
            width: 90px;
            right: 10px;
            top: 10px;
        }
    }
</style>

<div class="container mt-4 px-0">
    <div class="card status-card p-4 position-relative">

        <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}"
             alt="logo"
             class="logo-img">

        {{-- <h4 class="title-text">Check Complaint Status = Tracking</h4> --}}

        <form id="transactionForm">
            @csrf
<div class="row"> <h4>Check Complaint Status = Tracking</h4> </div>
            <div class="row mt-4">
                <div class="col-md-4">

                    <!-- Complaint Type -->
                    <div class="mb-4">
                        <label class="label-text">Complaint Type</label>
                        <select id="complaint_category" name="complaint_category"
                            class="form-select select-line form-select2">
                            <option value="">Select Complaint Type</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Complaint Ticket -->
                    <div class="mb-4">
                        <label class="label-text">Enter complaint id</label>
                        <input type="text"
                               id="ticket_number"
                               name="ticket_number"
                               class="form-control input-line"
                               placeholder="Enter Complaint Ticket No">
                    </div>

                    <!-- Button -->
                    <div class="mb-4">
                        <button type="submit" class="check-btn buttonColor">Check Status</button>
                    </div>

                    <!-- Result -->
                    <div id="resultArea"></div>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function badgeClassByStatus(st) {
        if (st === 'Closed') return 'danger';
        if (st === 'In Progress') return 'warning';
        if (st === 'Open') return 'primary';
        return 'secondary';
    }

    $('#transactionForm').on('submit', function(e) {
        e.preventDefault();
        $('#resultArea').html('');

        $.ajax({
            url: "{{ route('complaint.status.check') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ticket_number: $('#ticket_number').val(),
                complaint_category: $('#complaint_category').val()
            },
            success: function(res) {

                const d = res.data;

                let html = `
                    <div class="mb-2 fw-semibold">Complaint Status</div>
                    <div class="mb-3">${d.complaint_status ?? '-'}</div>

                    <table class="result-table">
                        <tr>
                            <td><strong>Complaint ID</strong></td>
                            <td>${d.ticket_number ?? '-'}</td>
                        </tr>
                        <tr>
                            <td><strong>Complaint Status</strong></td>
                            <td>${d.complaint_status ?? '-'}</td>
                        </tr>
                    </table>
                `;

                $('#resultArea').html(html);
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message ?? 'Complaint not found.';
                $('#resultArea').html(
                    `<div class="text-danger mt-2">${msg}</div>`
                );
            }
        });
    });
</script>

@endsection