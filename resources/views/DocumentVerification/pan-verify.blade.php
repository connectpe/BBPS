@extends('layouts.app')

@section('title', 'Pan Verification')
@section('page-title', 'PAN Verification')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">PAN Verification</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="panVerificationTable" class="table table-bordered table-striped w-100">
                    <thead class="bg-light text-uppercase" style="font-size: 11px;">
                        <tr>
                            <th>Verified At</th>
                            <th>PAN Ref. ID</th>
                            <th>PAN</th>
                            <th>Name Provided</th>
                            <th>Registered Name</th>
                            <th>Type</th>
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

            $('#panVerificationTable').DataTable({
                processing: true,
                serverSide: false,

                data: [{
                        verified_at: "2026-05-04 17:20:00",
                        pan_ref_id: "16819601",
                        pan: "AAKCG1107D",
                        name_provided: "-",
                        registered_name: "GROSCOPE TECHNOLOGIES PRIVATE LIMITED",
                        type: "Company",
                        status: "valid"
                    },
                    {
                        verified_at: "2026-05-04 16:21:00",
                        pan_ref_id: "168178423",
                        pan: "UARPS9960J",
                        name_provided: "Nilam Yadav",
                        registered_name: "NILAM YADAV",
                        type: "Individual",
                        status: "valid"
                    }
                ],

                columns: [{
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
                        data: 'pan_ref_id',
                        defaultContent: '---'
                    },

                    {
                        data: 'pan',
                        defaultContent: '---'
                    },

                    {
                        data: 'name_provided',
                        defaultContent: '---'
                    },

                    {
                        data: 'registered_name',
                        render: function(data) {
                            if (!data) return '---';

                            return data.length > 18 ?
                                data.substring(0, 18) + '...' :
                                data;
                        }
                    },

                    {
                        data: 'type',
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
