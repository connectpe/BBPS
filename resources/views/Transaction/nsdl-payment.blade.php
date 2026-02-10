@extends('layouts.app')

@section('title', 'NSDL Payments')
@section('page-title', 'NSDL Payments')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">NSDL Payments List</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="nsdlTxnTable" class="table table-bordered table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Mobile No</th>
                            <th>UTR</th>
                            <th>Status</th>
                            <th>Order ID</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {

            $('#nsdlTxnTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: "{{ url('fetch/nsdl-payment/0/all') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: "user.name",
                        defaultContent: "-"
                    },
                    {
                        data: "amount",
                        defaultContent: "-"
                    },
                    {
                        data: "transaction_id",
                        defaultContent: "-"
                    },
                    {
                        data: "mobile_no",
                        defaultContent: "-"
                    },
                    {
                        data: "utr",
                        defaultContent: "-"
                    },
                    {
                        data: "status",
                        render: function(data) {
                            if (!data) return '-';

                            let status = data.toLowerCase();

                            if (status === 'success') {
                                return `<span class="badge bg-success text-uppercase">${data}</span>`;
                            }
                            if (status === 'pending' || status === 'initiated') {
                                return `<span class="badge bg-warning text-dark text-uppercase">${data}</span>`;
                            }
                            if (status === 'failed') {
                                return `<span class="badge bg-danger text-uppercase">${data}</span>`;
                            }

                            return `<span class="badge bg-secondary text-uppercase">${data}</span>`;
                        }
                    },
                    {
                        data: "order_id",
                        defaultContent: "-"
                    },
                    {
                        data: "created_at",
                        name: 'created_at'
                    }
                ],
                order: [
                    [8, "desc"]
                ]
            });

        });
    </script>

@endsection
