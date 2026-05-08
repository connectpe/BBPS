@extends('layouts.app')

@section('title', 'GSTIN Verification')
@section('page-title', 'GSTIN Verification')

@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">GSTIN Verification</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="gstinVerificationTable" class="table table-bordered table-striped w-100">
                <thead class="bg-light text-uppercase" style="font-size: 11px;">
                    <tr>
                        <th>Verified At</th>
                        <th>GSTIN Ref. ID</th>
                        <th>GSTIN</th>
                        <th>Name of Business</th>
                        <th>Legal Name of Business</th>
                        <th>Tax Payer Type</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#gstinVerificationTable').DataTable({
            processing: true,
            serverSide: false,

            data: [{
                verified_at: "2026-05-04 17:15:00",
                gstin_ref_id: "2271781",
                gstin: "23AANCR7014H1ZE",
                business_name: "-",
                legal_business_name: "RAFI FINTECH PRIVATE LIMITED",
                taxpayer_type: "Regular",
                status: "valid"
            }],

            columns: [

                {
                    data: 'verified_at',
                    render: function(data) {

                        if (!data) return '---';

                        let d = new Date(data);

                        return d.toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) + ', ' + d.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        });
                    }
                },

                {
                    data: 'gstin_ref_id',
                    defaultContent: '---'
                },

                {
                    data: 'gstin',
                    defaultContent: '---'
                },

                {
                    data: 'business_name',
                    defaultContent: '---'
                },

                {
                    data: 'legal_business_name',
                    render: function(data) {

                        if (!data) return '---';

                        return data.length > 28 ?
                            data.substring(0, 28) + '...' :
                            data;
                    }
                },

                {
                    data: 'taxpayer_type',
                    defaultContent: '---'
                },

                {
                    data: 'status',
                    render: function(data) {

                        return data === 'valid' ?
                            `<span class="text-success fw-bold">Valid</span>` :
                            `<span class="text-danger fw-bold">Invalid</span>`;
                    }
                }

            ]
        });

    });
</script>

@endsection