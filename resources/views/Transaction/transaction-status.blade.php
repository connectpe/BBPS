@extends('layouts.app')

@section('title', 'Transaction Status')
@section('page-title', 'Transaction Status')

@section('content')

<style>
    .transaction-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #ddd;
        background: #fff;
    }

    .section-title {
        font-size: 22px;
        font-weight: 700;
        color: #111;
        margin-bottom: 20px;
    }

    .input-line {
        border: none;
        border-bottom: 1px solid #999;
        border-radius: 0;
        padding-left: 0;
        box-shadow: none !important;
    }

    .input-line:focus {
        border-bottom: 1px solid #4b4bb7;
    }

    .label-title {
        font-weight: 600;
        color: #3d3d99;
        margin-bottom: 4px;
    }

    .or-text {
        font-weight: 700;
        margin: 15px 0;
        color: #555;
    }

    .search-btn {
        background: #4b4bb7;
        color: white;
        padding: 8px 28px;
        border-radius: 4px;
        border: none;
    }

    .search-btn:hover {
        background: #3d3da5;
    }

    .result-box {
        border: 1px solid #999;
        padding: 20px;
        min-height: 220px;
    }

    .result-row {
        display: flex;
        margin-bottom: 12px;
    }

    .result-label {
        width: 180px;
        font-weight: 600;
        color: #333;
    }

    .logo-img {
        position: absolute;
        right: 20px;
        top: 15px;
        width: 80px;
    }

    @media(max-width:768px){
        .logo-img{
            width: 90px;
            top: 10px;
            right: 10px;
        }

        .result-label{
            width: 140px;
        }
    }
</style>

<div class="container mt-4">
    <div class="card transaction-card p-4 position-relative">
        
        <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}" class="logo-img" alt="logo">

        <div class="row g-5">
            
            <!-- Left Side Form -->
            <div class="col-md-5">
                <h4 class="section-title">Check Transaction Status</h4>

                <form id="transactionForm">
                    
                    <div class="mb-3">
                        <label class="label-title">Payment Ref ID</label>
                        <input type="text" id="txnid" class="form-control input-line" placeholder="Payment Reference ID">
                    </div>

                    <div class="or-text">OR</div>

                    <div class="mb-3">
                        <label class="label-title">Mobile Number</label>
                        <input type="number" id="mobileNumber" class="form-control input-line" placeholder="Enter Mobile Number">
                    </div>

                    <div class="mb-3">
                        <label class="label-title">From Date</label>
                        <input type="date" id="fromDate" class="form-control input-line">
                    </div>

                    <div class="mb-4">
                        <label class="label-title">To Date</label>
                        <input type="date" id="toDate" class="form-control input-line">
                    </div>

                    <button type="submit" class="search-btn buttonColor">Search</button>
                </form>
            </div>

            <!-- Right Side Result -->
            <div class="col-md-7">
                <h4 class="section-title text-primary">Transaction List</h4>
                <div id="resultArea" class="result-box">
                    <div class="text-muted">No Record Found</div>
                </div>
            </div>

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

    let today = new Date().toISOString().split('T')[0];
    fromDateInput.setAttribute("max", today);
    toDateInput.setAttribute("max", today);

    fromDateInput.addEventListener("change", function() {
        toDateInput.setAttribute("min", this.value);
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        let txnid = document.getElementById('txnid').value.trim();
        let mobileNumber = mobileInput.value.trim();
        let fromDate = fromDateInput.value;
        let toDate = toDateInput.value;

        if (!txnid && !mobileNumber && !fromDate && !toDate) {
            notify("Please enter at least one field", "danger");
            return;
        }

        if (mobileNumber && !/^[6-9][0-9]{9}$/.test(mobileNumber)) {
            notify("Please enter valid mobile number", "danger");
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

                let txn = response.data[0];

                resultArea.innerHTML = `
                    <div class="result-row">
                        <div class="result-label">Request ID</div>
                        <div>${txn.request_id ?? '-'}</div>
                    </div>
                    <div class="result-row">
                        <div class="result-label">Payment Ref ID</div>
                        <div>${txn.payment_ref_id ?? '-'}</div>
                    </div>
                    <div class="result-row">
                        <div class="result-label">Amount</div>
                        <div>${txn.amount ?? '-'}</div>
                    </div>
                    <div class="result-row">
                        <div class="result-label">Time</div>
                        <div>${txn.created_at ?? '-'}</div>
                    </div>
                    <div class="result-row">
                        <div class="result-label">Mobile</div>
                        <div>${txn.mobile_number ?? '-'}</div>
                    </div>
                    <div class="result-row">
                        <div class="result-label">Status</div>
                        <div>${txn.status ?? '-'}</div>
                    </div>
                `;
            } else {
                resultArea.innerHTML = `<div class="text-danger">No Record Found</div>`;
            }
        })
        .catch(error => {
            resultArea.innerHTML = `<div class="text-danger">Server Error</div>`;
        });
    });

});
</script>

@endsection