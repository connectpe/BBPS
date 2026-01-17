@extends('layouts.app')

@section('title', 'Utility Services')
@section('page-title', 'Utility Services')

@section('content')

<style>
    .card-height {
        height: 400px;
    }
</style>

<div class="row g-4 mb-3">

    @php
    $services = [
    ['name'=>'Electricity', 'icon'=>'bi-lightning-charge'],
    ['name'=>'Water', 'icon'=>'bi-droplet'],
    ['name'=>'Gas', 'icon'=>'bi-fire'],
    ['name'=>'Health Insurance', 'icon'=>'bi-shield-check'],
    ['name'=>'Hospital', 'icon'=>'bi-hospital'],
    ['name'=>'Hospital and Pathology', 'icon'=>'bi-heart-pulse'],
    ['name'=>'Housing Society', 'icon'=>'bi-building'],
    ['name'=>'Municipal Services', 'icon'=>'bi-gear'],
    ['name'=>'Municipal Taxes', 'icon'=>'bi-currency-dollar'],
    ['name'=>'Education Fees', 'icon'=>'bi-mortarboard'],
    ['name'=>'Donation', 'icon'=>'bi-hand-heart'],
    ['name'=>'Clubs and Associations', 'icon'=>'bi-people'],
    ['name'=>'Rental', 'icon'=>'bi-building-check'],
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
                        <a href="javascript:void(0)" class="text-decoration-none text-dark d-flex flex-column align-items-center utility-service-btn"
                            data-service="{{ $service['name'] }}">
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


<div class="row mt-3">

    <div class="row">

        <div class="col-md-6">
            <div class="card shadow-sm card-height h-100">
                <div class="card-header">
                    <h5 class="mb-0">Request Form</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <form class="row g-3 flex-grow-1">
                        <!-- Biller Name -->
                        <div class="col-12">
                            <label for="billerName" class="form-label">Biller Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billerName" placeholder="Biller Name">
                        </div>

                        <!-- Contract Account Number -->
                        <div class="col-12">
                            <label for="account_no" class="form-label">Contract Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_no" placeholder="Contract Account Number">
                        </div>

                        <!-- TPIN -->
                        <div class="col-12">
                            <label for="TPIN" class="form-label">TPIN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="TPIN" placeholder="Enter TPIN">
                        </div>

                        <!-- Button -->
                        <div class="col-12">
                            <button type="button" class="btn w-100 font-semibold text-white rounded hover:opacity-90 transition"
                                style="background-color:#6b83ec;">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">

            @php
            $categories = [
            'Adani Electricity',
            'BSES Rajdhani Power Limited',
            'Torrent Power Limited',
            ]
            @endphp

            <div class="card shadow-sm card-height h-100">
                <div class="card-header">
                    <h5 class="mb-0">Basic List Group</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <form class="row g-3 flex-grow-1">

                        <div class="col-12">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category">
                                <option selected>--Select Category--</option>
                                <option value="support">Support</option>
                                <option value="feedback">Feedback</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="search_categroy" class="form-label">Search Category</label>
                            <input type="text" class="form-control" id="search_categroy" placeholder="Search Category">
                        </div>

                        <div class="col-12">
                            <label for="select_category" class="form-label">Select Category:</label>
                            <div class="border border-1 p-2 rounded"
                                style="max-height: 100px; overflow-y: auto;">
                                @foreach($categories as $category)
                                <div class="border border-1 p-2 mb-2 rounded">
                                    {{ $category }}
                                </div>
                                @endforeach
                            </div>
                        </div>


                        <!-- Buttons -->
                        <div class="col-12 mt-2 d-flex gap-2">
                            <button type="button" class="btn w-100 font-semibold text-white rounded hover:opacity-90 transition"
                                style="background-color:#6b83ec;">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>


</div>

<!-- Modal  -->

<!-- Utility Modal -->
<div class="modal fade" id="utilityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="utilityModalTitle">Utility Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="utilityModalBody">
                <!-- Step content loads here -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary d-none" id="utilityBackBtn">Back</button>
                <button type="button" class="btn btn-primary" id="utilityNextBtn">Next</button>
            </div>

        </div>
    </div>
</div>


<script>
    /* ===============================
   UTILITY SERVICE CONFIG
================================ */
    const utilityConfig = {
        "Electricity": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Water": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Gas": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Health Insurance": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Hospital": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Hospital and Pathology": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Housing Society": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Municipal Services": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Municipal Taxes": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Education Fees": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Donation": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Clubs and Associations": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
        "Rental": {
            steps: ["INPUT", "FETCH", "PAY", "RESULT"]
        },
    };

    /* ===============================
       STATE
    ================================ */
    let utilityCurrentService = '';
    let utilityStepIndex = 0;


    $(document).ready(function() {

        $('.utility-service-btn').on('click', function() {
            utilityCurrentService = $(this).data('service');
            utilityStepIndex = 0;

            // Reset buttons
            $('#utilityNextBtn').removeClass('d-none').text('Next');
            $('#utilityBackBtn').addClass('d-none');

            $('#utilityModalTitle').text(utilityCurrentService);
            loadUtilityStep();

            $('#utilityModal').modal('show');
        });

        // Next Button
        $('#utilityNextBtn').on('click', function() {
            const steps = utilityConfig[utilityCurrentService].steps;
            if (utilityStepIndex < steps.length - 1) {
                utilityStepIndex++;
                loadUtilityStep();
            } else {
                alert('Call Payment API');
            }
        });

        // Back Button
        $('#utilityBackBtn').on('click', function() {
            if (utilityStepIndex > 0) utilityStepIndex--;
            loadUtilityStep();
        });

    });

    function loadUtilityStep() {
        const steps = utilityConfig[utilityCurrentService].steps;
        const step = steps[utilityStepIndex];

        // Show/hide Back button
        $('#utilityBackBtn').toggleClass('d-none', utilityStepIndex === 0);

        // Reset Next button
        $('#utilityNextBtn').removeClass('d-none');

        if (step === "INPUT") {
            $('#utilityNextBtn').text('Fetch Details');
            $('#utilityModalBody').html(`
            <div class="mb-3">
                <label>Customer / Biller ID</label>
                <input class="form-control" id="utilityCustomerId" placeholder="Enter Customer ID">
            </div>
        `);
            return;
        }

        if (step === "FETCH") {
            $('#utilityModalBody').html(loader("Fetching details..."));
            setTimeout(() => {
                utilityStepIndex++;
                loadUtilityStep();
            }, 1200);
            return;
        }

        if (step === "PAY") {
            $('#utilityNextBtn').text('Pay Now');
            $('#utilityModalBody').html(`
            <div class="mb-3">
                <label>Payable Amount</label>
                <input class="form-control" value="₹1200" readonly>
            </div>
            <div class="mb-3">
                <label>Payment Method</label>
                <select class="form-select">
                    <option>UPI</option>
                    <option>Wallet</option>
                    <option>Debit Card</option>
                    <option>Net Banking</option>
                </select>
            </div>
        `);
            return;
        }

        if (step === "RESULT") {
            $('#utilityNextBtn').addClass('d-none');
            $('#utilityModalBody').html(`
            <div class="text-center text-success">
                <i class="bi bi-check-circle fs-1"></i>
                <p class="mt-2">Transaction Successful</p>
            </div>
        `);
        }
    }

    /* ===============================
       HELPER LOADER
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