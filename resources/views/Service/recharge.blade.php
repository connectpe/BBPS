@extends('layouts.app')

@section('title', 'Recharge Services')
@section('page-title', 'Recharge Services')

@section('content')

<style>
    .card-height {
        height: 400px;
    }
</style>

<div class="row g-4 mb-3">


    <div class="col-12">
        <div class="banner position-relative overflow-hidden rounded" style="height: 250px;">
            <img src="{{ asset('assets/image/recharge.jpg') }}"
                alt="Banking Services Banner"
                class="w-100 h-100 object-fit-cover">

            <!-- Optional overlay text -->
            <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
                <h2 class="fw-bold">Fast & Easy Recharge Services</h2>
                <p class="mb-0">Recharge your mobile, DTH, and pay bills instantly</p>
            </div>

        </div>
    </div>


    @php
    $services = [
    ['name'=>'Mobile Postpaid', 'icon'=>'bi-phone'],
    ['name'=>'Mobile Prepaid', 'icon'=>'bi-phone'],
    ['name'=>'Landline Postpaid', 'icon'=>'bi-telephone'],
    ['name'=>'Broadband Postpaid', 'icon'=>'bi-wifi'],
    ['name'=>'Cable TV', 'icon'=>'bi-tv'],
    ['name'=>'DTH', 'icon'=>'bi-building'],
    ['name'=>'Fastag', 'icon'=>'bi-signpost'],
    ['name'=>'Subscription', 'icon'=>'bi-journal-text'],
    ['name'=>'NCMC Recharge', 'icon'=>'bi-wallet2'],
    ['name'=>'Bill Pay', 'icon'=>'bi-receipt'] ?? null,
    ['name'=>'Scan Pay', 'icon'=>'bi-qr-code-scan'] ?? null,
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
                        <a href="javascript:void(0)"
                            class="text-decoration-none text-dark d-flex flex-column align-items-center service-btn"
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


<!-- <div class="row g-4 mt-3">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm card-height h-100">
                <div class="card-header">
                    <h5 class="mb-0">Request Form</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <form class="row g-3 flex-grow-1">
                       
                        <div class="col-12">
                            <label for="billerName" class="form-label">Biller Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billerName" placeholder="Biller Name">
                        </div>

                        
                        <div class="col-12">
                            <label for="account_no" class="form-label">Contract Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_no" placeholder="Contract Account Number">
                        </div>

                       
                        <div class="col-12">
                            <label for="TPIN" class="form-label">TPIN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="TPIN" placeholder="Enter TPIN">
                        </div>

                       
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
                            <label for="select_category" class="form-label">Select Category : </label>
                            @foreach($categories as $category)
                            <div class="border border-1 p-2 m-1 rounded">
                                {{ $category }}
                            </div>
                            @endforeach
                        </div>


                        
                        <div class="col-12 mt-auto d-flex gap-2">
                            <button type="submit" class="btn w-100 btn-success">Send Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div> -->


<!-- Recharge Modal -->
<div class="modal fade" id="rechargeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Recharge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="modalBody">
                <!-- Step content loads here -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary d-none" id="backBtn">Back</button>
                <button type="button" class="btn btn-primary" id="nextBtn">View Plans</button>
            </div>

        </div>
    </div>
</div>

@php



$rechargeOperators = [
['name' => 'Jio', 'icon' => 'bi-telephone-fill', 'op_id' => 140],
['name' => 'Airtel', 'icon' => 'bi-telephone-fill', 'op_id' => 1],
['name' => 'Vodafone Idea (Vi)', 'icon' => 'bi-telephone-fill', 'op_id' => 2],
['name' => 'BSNL (Vodafone Idea)', 'icon' => 'bi-telephone-fill', 'op_id' => 4],
['name' => 'BSNL', 'icon' => 'bi-telephone-fill', 'op_id' => 5],
['name' => 'MTNL (Vodafone Idea)', 'icon' => 'bi-telephone-fill', 'op_id' => 6],
['name' => 'MTNL', 'icon' => 'bi-telephone-fill', 'op_id' => 7],
['name' => 'Tata Docomo', 'icon' => 'bi-telephone-fill', 'op_id' => 8],
['name' => 'Aircel', 'icon' => 'bi-telephone-fill', 'op_id' => 9],
['name' => 'Idea Cellular', 'icon' => 'bi-telephone-fill', 'op_id' => 10],
];

$rechargeCircles = [
['name' => 'Andhra Pradesh & Telangana', 'code' => 'AP', 'circle_id' => 1],
['name' => 'Assam', 'code' => 'AS', 'circle_id' => 2],
['name' => 'Bihar & Jharkhand', 'code' => 'BR', 'circle_id' => 3],
['name' => 'Chennai', 'code' => 'CH', 'circle_id' => 4],
['name' => 'Delhi NCR', 'code' => 'DL', 'circle_id' => 5],
['name' => 'Gujarat', 'code' => 'GJ', 'circle_id' => 6],
['name' => 'Haryana', 'code' => 'HR', 'circle_id' => 7],
['name' => 'Himachal Pradesh', 'code' => 'HP', 'circle_id' => 8],
['name' => 'Jammu & Kashmir', 'code' => 'JK', 'circle_id' => 9],
['name' => 'Karnataka', 'code' => 'KA', 'circle_id' => 10],
['name' => 'Kerala', 'code' => 'KL', 'circle_id' => 11],
['name' => 'Kolkata', 'code' => 'KO', 'circle_id' => 12],
['name' => 'Madhya Pradesh & Chhattisgarh', 'code' => 'MP', 'circle_id' => 13],
['name' => 'Maharashtra & Goa', 'code' => 'MH', 'circle_id' => 14],
['name' => 'Mumbai', 'code' => 'MU', 'circle_id' => 15],
['name' => 'North East', 'code' => 'NE', 'circle_id' => 16],
['name' => 'Odisha', 'code' => 'OR', 'circle_id' => 17],
['name' => 'Punjab', 'code' => 'PB', 'circle_id' => 18],
['name' => 'Rajasthan', 'code' => 'RJ', 'circle_id' => 19],
['name' => 'Tamil Nadu', 'code' => 'TN', 'circle_id' => 20],
['name' => 'Uttar Pradesh (East)', 'code' => 'UE', 'circle_id' => 21],
['name' => 'Uttar Pradesh (West)', 'code' => 'UW', 'circle_id' => 22],
['name' => 'West Bengal', 'code' => 'WB', 'circle_id' => 23],
];


$rechargePlanTypes = [
['name' => 'Data', 'code' => 'DATA','plan_id'=>3],
['name' => 'Popular', 'code' => 'POPULAR','plan_id'=>1],
['name' => 'Unlimited', 'code' => 'UNLIMITED','plan_id'=>2],
['name' => 'Talktime', 'code' => 'TALKTIME','plan_id'=>4],
['name' => 'SMS', 'code' => 'SMS','plan_id'=>5],
['name' => 'Roaming', 'code' => 'ROAMING','plan_id'=>6],
];


@endphp


<script>
    function buildOperatorDropdown() {
        let options = `<option value="">Select Operator</option>`;

        window.rechargeOperators.forEach(op => {
            options += `<option value="${op.op_id}">${op.name}</option>`;
        });

        return `
        <div class="mb-3">
            <label>Operator</label>
            <select class="form-select" id="operator">
                ${options}
            </select>
        </div>
    `;
    }

    function buildCircleDropdown() {
        let options = `<option value="">Select Circle</option>`;

        window.rechargeCircles.forEach(circle => {
            options += `<option value="${circle.circle_id}">${circle.name}</option>`;
        });

        return `
        <div class="mb-3">
            <label>Circle</label>
            <select class="form-select" id="circle">
                ${options}
            </select>
        </div>
    `;
    }

    function buildPlanTypeDropdown() {
        let options = `<option value="">Select Plan Type</option>`;

        window.rechargePlanTypes.forEach(plan => {
            options += `<option value="${plan.plan_id}">${plan.name}</option>`;
        });

        return `
            <div class="mb-3">
                <label>Plan Type</label>
                <select class="form-select" id="planType">
                    ${options}
                </select>
            </div>
        `;
    }
</script>


<script>
    /* ===============================
   SERVICE CONFIG
================================ */
    const serviceConfig = {

        "Mobile Prepaid": {
            steps: ["INPUT", "FETCH_PLANS", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Mobile Number</label>
                <input class="form-control" id="mobile" name="mobile" type="text">
            </div>
            ${buildOperatorDropdown()}
            ${buildCircleDropdown()}
            ${buildPlanTypeDropdown()}
        `
        },

        "Mobile Postpaid": {
            steps: ["INPUT", "FETCH_BILL", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Mobile Number</label>
                <input class="form-control" id="accountInput">
            </div>
            ${buildOperatorDropdown()}
        `
        },

        "Landline Postpaid": {
            steps: ["INPUT", "FETCH_BILL", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Landline Number</label>
                <input class="form-control" id="accountInput">
            </div>
            ${buildOperatorDropdown()}
        `
        },

        "Broadband Postpaid": {
            steps: ["INPUT", "FETCH_BILL", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Broadband Account ID</label>
                <input class="form-control" id="accountInput">
            </div>
            ${buildOperatorDropdown()}
        `
        },

        "Cable TV": {
            steps: ["INPUT", "FETCH_BILL", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Customer ID</label>
                <input class="form-control" id="accountInput">
            </div>
            ${buildOperatorDropdown()}
        `
        },

        "DTH": {
            steps: ["INPUT", "FETCH_BILL", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Subscriber ID</label>
                <input class="form-control" id="accountInput">
            </div>
            ${buildOperatorDropdown()}
        `
        },

        "Fastag": {
            steps: ["INPUT", "FETCH_BILL", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Vehicle / FASTag Number</label>
                <input class="form-control" id="accountInput">
            </div>
        `
        },

        "Subscription": {
            steps: ["INPUT", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Subscription ID</label>
                <input class="form-control" id="accountInput">
            </div>
        `
        },

        "NCMC Recharge": {
            steps: ["INPUT", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>NCMC Card Number</label>
                <input class="form-control" id="accountInput">
            </div>
        `
        },

        "Bill Pay": {
            steps: ["INPUT", "FETCH_BILL", "PAY"],
            input: () => `
            <div class="mb-3">
                <label>Bill Type</label>
                <select class="form-select">
                    <option>Electricity</option>
                    <option>Water</option>
                    <option>Gas</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Consumer Number</label>
                <input class="form-control" id="accountInput">
            </div>
        `
        },

        "Scan Pay": {
            steps: ["SCAN", "PAY"],
            input: () => `
            <div class="text-center">
                <button class="btn btn-outline-primary w-100">
                    <i class="bi bi-qr-code-scan me-2"></i> Scan QR Code
                </button>
            </div>
        `
        }
    };

    /* ===============================
       GLOBAL STATE
    ================================ */
    let currentService = '';
    let stepIndex = 0;
    let selectedAmount = 0;

    /* ===============================
       INIT
    ================================ */
    $(document).ready(function() {

        window.rechargeOperators = @json($rechargeOperators);
        window.rechargeCircles = @json($rechargeCircles);
        window.rechargePlanTypes = @json($rechargePlanTypes);

        $('.service-btn').on('click', function() {
            currentService = $(this).data('service');
            stepIndex = 0;
            selectedAmount = 0;

            $('#modalTitle').text(currentService);
            loadStep();
            $('#rechargeModal').modal('show');
        });

    });

    /* ===============================
       STEP ENGINE
    ================================ */
    function loadStep() {

        const config = serviceConfig[currentService];
        const step = config.steps[stepIndex];

        $('#backBtn').toggleClass('d-none', stepIndex === 0);

        if (step === "INPUT" || step === "SCAN") {
            $('#nextBtn').text('View Plans');
            $('#modalBody').html(config.input());
            return;
        }

        if (step === "FETCH_BILL") {
            $('#nextBtn').text('Fetching...');
            $('#modalBody').html(loader("Fetching bill details..."));

            setTimeout(() => {
                selectedAmount = 399;
                stepIndex++;
                loadStep();
            }, 1200);
            return;
        }

        // if (step === "FETCH_PLANS") {
        //     $('#nextBtn').hide();
        //     $('#modalBody').html(loader("Fetching recharge plans..."));

        //     setTimeout(() => {
        //         $('#modalBody').html(`
        //         <div class="list-group">
        //             <button class="list-group-item plan" data-amt="239">₹239 – 28 Days</button>
        //             <button class="list-group-item plan" data-a+mt="299">₹299 – 28 Days</button>
        //             <button class="list-group-item plan" data-amt="719">₹719 – 84 Days</button>
        //         </div>
        //     `);

        //         $('.plan').on('click', function() {
        //             selectedAmount = $(this).data('amt');
        //             $('#nextBtn').show();
        //             stepIndex++;
        //             loadStep();
        //         });
        //     }, 1200);
        //     return;
        // }

        // if (step === "PAY") {
        //     $('#nextBtn').text('Pay Now');
        //     $('#modalBody').html(`
        //     <div class="mb-3">
        //         <label>Amount</label>
        //         <input class="form-control" value="₹${selectedAmount || 399}" readonly>
        //     </div>
        //     <div class="mb-3">
        //         <label>Payment Method</label>
        //         <select class="form-select">
        //             <option>UPI</option>
        //             <option>Wallet</option>
        //             <option>Debit Card</option>
        //             <option>Net Banking</option>
        //         </select>
        //     </div>
        // `);
        // }
    }

    /* ===============================
       BUTTON HANDLERS
    ================================ */
    $('#nextBtn').on('click', function() {
        const config = serviceConfig[currentService];

        if (stepIndex < config.steps.length - 1) {
            stepIndex++;
            loadStep();
        } else {
            alert('Call Payment API');
        }
    });

    $('#backBtn').on('click', function() {
        stepIndex--;
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

<script>
    document.getElementById('nextBtn').addEventListener('click', function() {

        const mobile = document.getElementById('mobile').value;
        const operator_id = document.getElementById('operator').value;
        const circle_id = document.getElementById('circle').value;
        const plan_type = document.getElementById('planType').value;

        if (!mobile || !operator_id || !circle_id || !plan_type) {
            alert('Please fill all fields');
            return;
        }

        fetch('/bbps-recharge/getPlans/' + operator_id + '/' + circle_id + '/' + plan_type, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    mobile: mobile,
                    operator_id: operator_id,
                    circle_id: circle_id,
                    plan_type: plan_type,
                })
            })
            .then(res => res.json())
            .then(response => {
                console.log(response);
                alert('Plans fetched successfully!');
                alert(JSON.stringify(response.data));
                // currentStepIndex++;
                // loadStep();
            })
            .catch(error => {
                console.error(error);
                alert(error);
            });
    });
</script>
@endsection