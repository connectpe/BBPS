@extends('layouts.app')

@section('title', 'Banking Services')
@section('page-title', 'Banking Services')

@section('content')

<style>
    .card-height {
        height: 400px;
    }
</style>

<div class="row g-4 mb-3">

    <div class="col-12">
        <div class="banner position-relative overflow-hidden rounded" style="height: 250px;">
            <img src="{{ asset('assets/image/banking.jpg') }}"
                alt="Banking Services Banner"
                class="w-100 h-100 object-fit-cover">

            <!-- Optional overlay text -->
            <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
                <h2 class="fw-bold">Secure Banking Services</h2>
                <p class="mb-0">Manage your finances easily and safely</p>
            </div>
        </div>
    </div>

    @php
    $services = [
    ['name'=>'AEPS', 'icon'=>'bi-credit-card'],
    ['name'=>'Insurance', 'icon'=>'bi-shield-check'],
    ['name'=>'Life Insurance', 'icon'=>'bi-shield-lock'],
    ['name'=>'Loan Repayment', 'icon'=>'bi-cash-stack'],
    ['name'=>'Recurring Deposit', 'icon'=>'bi-calendar2-check'],
    ['name'=>'NPS', 'icon'=>'bi-graph-up'],
    ['name'=>'Digital Wallet', 'icon'=>'bi-wallet2'] ?? null, // if you had it before
    ['name'=>'CreditCardPay', 'icon'=>'bi-credit-card'],
    ];

    $colors = ['#f94144','#f3722c','#f8961e','#f9c74f','#90be6d','#43aa8b','#577590','#277da1','#9d4edd','#ff6d00','#1982c4','#6a4c93'];
    @endphp

    <div class="col-md-12">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="row g-3">
                    @foreach($services as $service)
                    @php
                    $randColor = $colors[array_rand($colors)];
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 text-center mb-3">
                        <a href="javascript:void(0)" class="text-decoration-none text-dark d-flex flex-column align-items-center service-btn" data-service="{{ $service['name'] }}">
                            <div class="rounded-circle d-flex justify-content-center align-items-center border border-light"
                                style="width:60px; height:60px; font-size:1.5rem; background-color: #e1e8ef; color: {{ $randColor }};">
                                <i class="bi {{ $service['icon'] }}"></i>
                            </div>
                            <span class="small fw-semibold text-center mt-2">{{ $service['name'] }}</span>
                        </a>
                    </div>

                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal  -->

<div class="modal fade" id="bankingModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Banking</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="modalBody"></div>

            <div class="modal-footer">
                <button class="btn btn-secondary d-none" id="backBtn">Back</button>
                <button class="btn btn-primary" id="nextBtn">Next</button>
            </div>

        </div>
    </div>
</div>

<script>
    /* ===============================
   BANKING SERVICE CONFIG
================================ */
    const bankingConfig = {

        "AEPS": {
            steps: ["SELECT", "AADHAAR", "AUTH", "RESULT"],
            input: () => `
            <div class="mb-3">
                <label>AEPS Service</label>
                <select class="form-select" id="aepsType">
                    <option>Balance Enquiry</option>
                    <option>Cash Withdrawal</option>
                    <option>Mini Statement</option>
                </select>
            </div>
        `
        },

        "Insurance": {
            steps: ["INPUT", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Policy Number</label>
                <input class="form-control">
            </div>
        `
        },

        "Life Insurance": {
            steps: ["INPUT", "FETCH", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Policy Number</label>
                <input class="form-control">
            </div>
        `
        },

        "Loan Repayment": {
            steps: ["INPUT", "FETCH", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Loan Account Number</label>
                <input class="form-control">
            </div>
        `
        },

        "Recurring Deposit": {
            steps: ["INPUT", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>RD Account Number</label>
                <input class="form-control">
            </div>
        `
        },

        "NPS": {
            steps: ["INPUT", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>PRAN Number</label>
                <input class="form-control">
            </div>
        `
        },

        "Digital Wallet": {
            steps: ["INPUT", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Wallet Mobile Number</label>
                <input class="form-control">
            </div>
        `
        },

        "CreditCardPay": {
            steps: ["INPUT", "FETCH", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Credit Card Number</label>
                <input class="form-control">
            </div>
        `
        }
    };
    /* ===============================
       STATE
    ================================ */
    let currentService = '';
    let stepIndex = 0;

    /* ===============================
       INIT
    ================================ */
    $(document).ready(function() {

        $('.service-btn').on('click', function() {
            currentService = $(this).data('service');
            stepIndex = 0;

            // RESET BUTTONS WHEN OPENING A SERVICE
            $('#nextBtn').removeClass('d-none').text('Next');
            $('#backBtn').addClass('d-none');

            $('#modalTitle').text(currentService);
            loadStep();
            $('#bankingModal').modal('show');
        });

    });

    /* ===============================
       STEP ENGINE
    ================================ */
    function loadStep() {

        const steps = bankingConfig[currentService].steps;
        const step = steps[stepIndex];

        // SHOW/HIDE BACK BUTTON
        $('#backBtn').toggleClass('d-none', stepIndex === 0);

        // RESET NEXT BUTTON BY DEFAULT
        $('#nextBtn').removeClass('d-none');

        if (step === "INPUT" || step === "SELECT" || step === "AADHAAR") {
            $('#nextBtn').text('Next');
            $('#modalBody').html(bankingConfig[currentService].input());
            return;
        }

        if (step === "AUTH") {
            $('#modalBody').html(loader("Capturing biometric..."));
            setTimeout(() => {
                stepIndex++;
                loadStep();
            }, 1500);
            return;
        }

        if (step === "FETCH") {
            $('#modalBody').html(loader("Fetching details..."));
            setTimeout(() => {
                stepIndex++;
                loadStep();
            }, 1200);
            return;
        }

        if (step === "PAY") {
            $('#nextBtn').text('Pay Now');
            $('#modalBody').html(`
            <div class="mb-3">
                <label>Amount</label>
                <input class="form-control" value="₹500" readonly>
            </div>
            <select class="form-select">
                <option>UPI</option>
                <option>Wallet</option>
                <option>Net Banking</option>
            </select>
        `);
            return;
        }

        if (step === "RESULT") {
            // hide Next button only using class, so it can be restored later
            $('#nextBtn').addClass('d-none');
            $('#modalBody').html(`
            <div class="text-center text-success">
                <i class="bi bi-check-circle fs-1"></i>
                <p class="mt-2">Transaction Successful</p>
            </div>
        `);
        }
    }

    /* ===============================
       BUTTONS
    ================================ */
    $('#nextBtn').on('click', function() {
        const steps = bankingConfig[currentService].steps;
        if (stepIndex < steps.length - 1) {
            stepIndex++;
            loadStep();
        } else {
            alert('Call Banking API');
        }
    });

    $('#backBtn').on('click', function() {
        if (stepIndex > 0) stepIndex--;
        loadStep();
    });

    /* ===============================
       HELPERS
    ================================ */
    function loader(text) {
        return `
        <div class="text-center my-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">${text}</p>
        </div>
    `;
    }
</script>


@endsection