@extends('layouts.app')

@section('title', 'Transaction Status')
@section('page-title', 'Transaction Status')

@section('content')

<div class="container">

    <div class="row g-4">

        <!-- Search Form -->
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header position-relative">
                    <h5 class="mb-0">Check Transactions Status</h5>
                    <img src="{{ asset('assets/image/icon_logo.svg') }}" alt="logo" style="position:absolute; top:10px; right:10px; width:60px; height:auto;">
                </div>

                <div class="card-body">
                    <form id="transactionForm">
                        <div class="row g-3 align-items-end">

                            <div class="col-5 col-md-5">
                                <label for="txnid" class="form-label">Payment Ref ID</label>
                                <input type="text" id="txnid" class="form-control" placeholder="Payment Reference ID">
                            </div>

                            <div class="col-2 col-md-2 text-center">
                                <span class="d-block fw-bold">OR</span>
                            </div>

                            <div class="col-5 col-md-5">
                                <label for="mobileNumber" class="form-label">Mobile Number</label>
                                <input type="number" id="mobileNumber" class="form-control" placeholder="Enter Mobile Number">
                            </div>

                            <div class="col-6 col-md-6">
                                <label for="fromDate" class="form-label">From Date</label>
                                <input type="date" id="fromDate" class="form-control">
                            </div>

                            <div class="col-6 col-md-6">
                                <label for="toDate" class="form-label">To Date</label>
                                <input type="date" id="toDate" class="form-control">
                            </div>

                            <div class="col-12 mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <button type="submit" class="btn buttonColor w-100">
                                            Check Status
                                        </button>
                                    </div>

                                    <div class="col-6">
                                        <button type="reset" class="btn btn-secondary w-100">
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Transaction Result -->
        <div class="col-12" id="resultArea">

        </div>

    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const form = document.getElementById('transactionForm');
        const mobileInput = document.getElementById('mobileNumber');
        const fromDateInput = document.getElementById('fromDate');
        const toDateInput = document.getElementById('toDate');
        const resultArea = document.getElementById('resultArea');

        // ===== Disable future dates =====
        let today = new Date().toISOString().split('T')[0];
        fromDateInput.setAttribute("max", today);
        toDateInput.setAttribute("max", today);

        // ===== From date change -> set min for To date =====
        fromDateInput.addEventListener("change", function() {
            toDateInput.setAttribute("min", this.value);
        });

        // ===== Form Submit =====
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            let txnid = document.getElementById('txnid').value.trim();
            let mobileNumber = mobileInput.value.trim();
            let fromDate = fromDateInput.value;
            let toDate = toDateInput.value;

            if (mobileNumber && !/^[6-9][0-9]{9}$/.test(mobileNumber)) {
                alert("Please enter valid 10 digit mobile number starting with 6, 7, 8 or 9");
                return;
            }

            if (fromDate && toDate && toDate < fromDate) {
                alert("To Date must be greater than or equal to From Date");
                return;
            }

            fetch("{{ route('transaction_status_check') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        txn_id: txnid,
                        mobile: mobileNumber,
                        from_date: fromDate,
                        to_date: toDate
                    })
                })
                .then(response => response.json())
                .then(response => {

                    if (response.success && response.data.length > 0) {

                        let rows = "";

                        response.data.forEach(txn => {

                            let statusClass = "text-warning";
                            if (txn.status?.toLowerCase() === "success") {
                                statusClass = "text-success";
                            } else if (txn.status?.toLowerCase() === "failed") {
                                statusClass = "text-danger";
                            }

                            let formattedDate = new Date(txn.created_at)
                                .toLocaleString();

                            rows += `
                        <tr>
                            <td>${txn.request_id ?? '-'}</td>
                            <td>${txn.payment_ref_id ?? '-'}</td>
                            <td>${txn.amount ?? '-'}</td>
                            <td>${formattedDate}</td>
                            <td>${txn.mobile_number ?? '-'}</td>
                            <td class="${statusClass}">
                                ${txn.status ?? '-'}
                            </td>
                        </tr>
                    `;
                        });

                        resultArea.innerHTML = `
                    <div class="card border shadow-sm mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Transaction Result</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Payment Ref ID</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Mobile</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${rows}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

                    } else {
                        resultArea.innerHTML =
                            `<div class="alert alert-danger">No Record Found</div>`;
                    }

                })
                .catch(error => {
                    console.error("Error:", error);
                    resultArea.innerHTML =
                        `<div class="alert alert-danger">Server Error</div>`;
                });

        });

        // ===== Reset Button Behaviour =====
        form.addEventListener("reset", function() {
            resultArea.innerHTML = "";
            toDateInput.removeAttribute("min");
        });

    });
</script>







@endsection