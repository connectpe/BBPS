@extends('layouts.app')

@section('title', 'Bank Account')
@section('page-title', 'Bank Account')

@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Bank Account Verification</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="bankVerificationTable" class="table table-bordered table-striped w-100">
                <thead class="bg-light text-uppercase" style="font-size: 11px;">
                    <tr>
                        <th>Verified At</th>
                        <th>Verification ID</th>
                        <th>Bank A/c No.</th>
                        <th>IFSC</th>
                        <th>Name Provided</th>
                        <th>Name at Bank</th>
                        <th>Name Match</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- @endsection

@push('scripts') --}}
<script>
$(document).ready(function () {

    $('#bankVerificationTable').DataTable({
        processing: true,
        serverSide: false, // Dummy data ke liye false

        data: [
            {
                verified_at: "2026-05-05 10:30:00",
                verification_id: "VER123456",
                account_number: "XXXX1234",
                ifsc: "SBIN0001234",
                name_provided: "Nilam Yadav",
                name_at_bank: "Nilam Yadav",
                name_match: true,
                status: "valid"
            },
            {
                verified_at: "2026-05-04 14:15:00",
                verification_id: "VER789012",
                account_number: "XXXX5678",
                ifsc: "HDFC0005678",
                name_provided: "N. Yadav",
                name_at_bank: "Nilam Yadav",
                name_match: false,
                status: "invalid"
            }
        ],

        columns: [

            {
                data: 'verified_at',
                render: function (data) {
                    if (!data) return '---';
                    let d = new Date(data);
                    return d.toLocaleDateString('en-GB', {
                        day: '2-digit', month: 'short', year: 'numeric'
                    }) + '<br>' + d.toLocaleTimeString();
                }
            },

            { data: 'verification_id', defaultContent: '---' },

            { data: 'account_number', defaultContent: '---' },

            { data: 'ifsc', defaultContent: '---' },

            { data: 'name_provided', defaultContent: '---' },

            { data: 'name_at_bank', defaultContent: '---' },

            {
                data: 'name_match',
                render: function (data) {
                    return data ? 
                        `<span class="badge bg-success">Matched</span>` :
                        `<span class="badge bg-danger">Not Matched</span>`;
                }
            },

            {
                data: 'status',
                render: function (data) {
                    return data === 'valid'
                        ? `<span class="text-success fw-bold">Valid</span>`
                        : `<span class="text-danger fw-bold">Invalid</span>`;
                }
            }

        ]
    });

});
</script>
{{-- @endpush
 --}}
 @endsection