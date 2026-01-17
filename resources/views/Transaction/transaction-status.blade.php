@extends('layouts.app')

@section('title', 'Transaction Status')
@section('page-title', 'Transaction Status')

@section('content')

<div class="container">

    <div class="row g-4">

        <!-- Search Form -->
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Check Transactions Status</h5>
                </div>
                <div class="card-body">
                    <form id="transactionForm">
                        <div class="row g-3 align-items-end">

                            <!-- Row 1: Txn ID / Service | OR | Mobile Number -->
                            <div class="col-5 col-md-5">
                                <label for="services" class="form-label">Bharat-Connect Txn ID</label>
                                <input type="text" id="services" class="form-control" placeholder="Bharat-Connect Txn ID">
                            </div>

                            <div class="col-2 col-md-2 text-center">
                                <span class="d-block fw-bold">OR</span>
                            </div>

                            <div class="col-5 col-md-5">
                                <label for="mobileNumber" class="form-label">Mobile Number</label>
                                <input type="number" id="mobileNumber" class="form-control" placeholder="Enter Mobile Number" min="6">
                            </div>

                            <!-- Row 2: From Date | To Date -->
                            <div class="col-6 col-md-6">
                                <label for="fromDate" class="form-label">From Date</label>
                                <input type="date" id="fromDate" class="form-control" placeholder="From Date">
                            </div>

                            <div class="col-6 col-md-6">
                                <label for="toDate" class="form-label">To Date</label>
                                <input type="date" id="toDate" class="form-control" placeholder="To Date">
                            </div>

                            <!-- Row 3: Search Button -->
                            <div class="col-12 col-md-12">
                                <button type="submit" class="btn text-white w-100" style="background-color:#6b83ec;">
                                    Check Status
                                </button>
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
    // Handle form submit
    $('#transactionForm').on('submit', function(e) {
        e.preventDefault();

        let html = `
        <div class="card border shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Transaction Result</h5>
                </div>
                <div class="card-body" id="resultArea">
           <div class="table-responsive">
             <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Agent ID</th>
                        <th>Biller ID</th>
                        <th>Amount</th>
                        <th>Time</th>
                        <th>Bharat-Connect Txn ID</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>${'MocktACeq7X4uL'}</td>
                        <td>${'OTME0005XXZ49'}</td>
                        <td>${'1000'}</td>
                        <td>${'2025-02-17 14:49:28+05:30'}</td>
                        <td>${'MocktACeq7X4uL'}</td>
                        <td class="text-success">SUCCESS</td>
                    </tr>
                </tbody>
            </table>
            </div>
         </div>
            </div>
        `;

        $('#resultArea').html(html);
    });
</script>

@endsection