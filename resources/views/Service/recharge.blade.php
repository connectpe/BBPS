@extends('layouts.app')

@section('title', 'Recharge Services')
@section('page-title', 'Recharge Services')
<style>
    .card-height {
        height: 400px;
    }

    .meta-card {
        background: linear-gradient(135deg, rgba(13, 110, 253, .10), rgba(25, 135, 84, .08));
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 14px;
        padding: 12px 12px;
        margin-bottom: 12px;
    }

    .meta-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .meta-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 14px;
        color: rgba(0, 0, 0, .75);
    }

    .meta-ico {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(13, 110, 253, .15);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .meta-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .meta-badge {
        background: rgba(255, 255, 255, .75);
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 12px;
        color: rgba(0, 0, 0, .70);
        align-items: center;
        gap: 6px;
    }

    .meta-badge i {
        opacity: .85;
    }

    .meta-mobile {
        font-weight: 800;
        color: rgba(0, 0, 0, .80);
    }

    .plans-wrap {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .plans-scroll {
        max-height: 320px;
        overflow-y: auto;
    }

    .plan-card {
        width: 100%;
        text-align: left;
        padding: 12px 14px;
        background: #fff;
        border: 0;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        transition: all .15s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .plan-card:last-child {
        border-bottom: 0;
    }

    .plan-card:hover {
        background: rgba(13, 110, 253, .05);
        transform: translateY(-1px);
    }

    .plan-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .plan-badge {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: rgba(13, 110, 253, .12);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-weight: 700;
    }

    .plan-meta {
        min-width: 0;
    }

    .plan-amt {
        font-weight: 800;
        font-size: 15px;
        margin: 0;
        line-height: 1.2;
        white-space: nowrap;
        color: rgba(0, 0, 0, .80);
    }

    .plan-sub {
        margin: 2px 0 0;
        font-size: 12px;
        color: rgba(0, 0, 0, .65);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .plan-right {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 8px;
        color: rgba(0, 0, 0, .55);
        font-size: 12px;
    }

    .plan-chip {
        background: rgba(0, 0, 0, .05);
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 700;
    }

    .plan-card.active {
        background: rgba(25, 135, 84, .10);
        outline: 2px solid rgba(25, 135, 84, .22);
    }

    #rechargeModal .modal-content {
        border: 0;
        border-radius: 18px;
        /* overflow: hidden; */
        box-shadow: 0 18px 45px rgba(0, 0, 0, .18);
    }

    #rechargeModal .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        background: linear-gradient(135deg, rgba(13, 110, 253, .10), rgba(25, 135, 84, .08));
        padding: 14px 16px;
    }

    #rechargeModal .modal-title {
        font-weight: 900;
        font-size: 16px;
        color: rgba(0, 0, 0, .78);
    }

    #rechargeModal .btn-close {
        opacity: .7;
    }

    #rechargeModal .btn-close:hover {
        opacity: 1;
    }

    #rechargeModal .modal-body {
        padding: 16px;
        background: #fff;
    }

    #rechargeModal .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, .06);
        background: rgba(0, 0, 0, .015);
        padding: 12px 16px;
        gap: 10px;
    }

    #rechargeModal #backBtn,
    #rechargeModal #nextBtn {
        border-radius: 12px;
        font-weight: 800;
        padding: 10px 14px;
    }

    #rechargeModal #nextBtn {
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
    }

    #rechargeModal #nextBtn:active {
        transform: translateY(1px);
        box-shadow: 0 6px 12px rgba(13, 110, 253, .14);
    }

    #rechargeModal .form-control,
    #rechargeModal .form-select {
        border-radius: 12px;
        padding: 10px 12px;
        border-color: rgba(0, 0, 0, .12);
    }

    #rechargeModal .form-control:focus,
    #rechargeModal .form-select:focus {
        border-color: rgba(13, 110, 253, .35);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
    }

    #rechargeModal label,
    #rechargeModal .form-label {
        font-weight: 800;
        font-size: 12px;
        color: rgba(0, 0, 0, .65);
    }

    #rechargeModal .spinner-border {
        width: 2rem;
        height: 2rem;
    }

    #rechargeModal .text-center p {
        font-size: 12px;
        color: rgba(0, 0, 0, .55);
    }

    @media (max-width: 576px) {
        #rechargeModal .modal-dialog {
            margin: 12px;
        }

        #rechargeModal .modal-body {
            padding: 14px;
        }

        #rechargeModal .modal-footer {
            padding: 12px 14px;
        }

        #rechargeModal #backBtn,
        #rechargeModal #nextBtn {
            width: 100%;
        }
    }
</style>

@section('content')
<div class="row g-4 mb-3">
    <div class="col-12">
        <div class="banner position-relative overflow-hidden rounded" style="height: 250px;">
            <img src="{{ asset('assets/image/recharge.jpg') }}" alt="Banking Services Banner"
                class="w-100 h-100 object-fit-cover">
            <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
                <h2 class="fw-bold">Fast & Easy Recharge Services</h2>
                <p class="mb-0">Recharge your mobile, DTH, and pay bills instantly</p>
            </div>
        </div>
    </div>

    @php
    $services = [
    ['name' => 'Mobile Postpaid', 'icon' => 'bi-phone'],
    ['name' => 'Mobile Prepaid', 'icon' => 'bi-phone'],
    ['name' => 'Landline Postpaid', 'icon' => 'bi-telephone'],
    ['name' => 'Broadband Postpaid', 'icon' => 'bi-wifi'],
    ['name' => 'Cable TV', 'icon' => 'bi-tv'],
    ['name' => 'DTH', 'icon' => 'bi-building'],
    ['name' => 'Fastag', 'icon' => 'bi-signpost'],
    ['name' => 'Subscription', 'icon' => 'bi-journal-text'],
    ['name' => 'NCMC Recharge', 'icon' => 'bi-wallet2'],
    ['name' => 'Bill Pay', 'icon' => 'bi-receipt'] ?? null,
    ['name' => 'Scan Pay', 'icon' => 'bi-qr-code-scan'] ?? null,
    ];
    $colors = ['#f94144','#f3722c','#f8961e','#f9c74f','#90be6d','#43aa8b','#577590','#277da1','#9d4edd','#ff6d00','#1982c4','#6a4c93'];
    @endphp

    <div class="col-md-12">
        <div class="card shadow-sm h-100 position-relative">

            <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}" alt="logo" style="position:absolute; top:10px; right:10px; width:70px; height:auto;">

            <div class="card-body mt-lg-1 mt-5">
                <div class="row g-3">
                    @foreach ($services as $service)
                    @php $randColor = $colors[array_rand($colors)]; @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-lg-3 text-center mb-3">
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

<!-- Recharge Modal -->
<div class="modal fade" id="rechargeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header position-relative overflow-visible">
                <h5 class="modal-title" id="modalTitle">
                    Recharge
                </h5>

                <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}"
                    alt=""
                    class="position-absolute"
                    style="top: 10px; right: 50px; width: 70px; z-index: 1060;">

                <button type="button"
                    class="btn-close position-absolute bg-light"
                    data-bs-dismiss="modal"
                    style="top: -15px; right: -15px; z-index: 1061;">
                </button>
            </div>

                <form id="rechargeForm">
                   
                    <div class="modal-body" id="modalBody"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary d-none" id="backBtn">Back</button>

                        {{-- keep as button (no form submit auto) --}}
                        <button type="button" class="btn btn-primary" id="nextBtn">View Plans</button>
                    </div>
                </form>
        </div>
    </div>
</div>

<!-- MPIN Modal -->
<div class="modal fade" id="mpinModal" tabindex="-1" aria-labelledby="mpinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="mpinForm" onsubmit="submitMpin(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="mpinModalLabel">Enter MPIN</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="text-muted mb-3">Please enter your 4-digit MPIN to proceed with recharge</p>

                    <input type="password" name="mpin" id="mpinInput"
                        class="form-control text-center fw-bold"
                        maxlength="4" inputmode="numeric"
                        placeholder="Enter your MPIN"
                        style="letter-spacing: 10px; font-size: 22px;"
                        required />

                    <input type="hidden" name="amount" id="rechargeAmount">
                    <input type="hidden" name="mobile" id="rechargeMobile">
                    <input type="hidden" name="operator_id" id="rechargeOperatorId">
                    <input type="hidden" name="circle_id" id="rechargeCircleId">
                    <input type="hidden" name="plan_id" id="rechargePlanId">

                    <div class="text-danger mt-2 d-none" id="mpinError">Invalid MPIN</div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    {{-- <button type="submit" onclick="var e=this;setTimeout(function(){e.disabled=true;},0);return true;" class="btn btn-primary">Confirm Recharge</button> --}}
                    <button type="submit" id="confirmRechargeBtn" class="btn btn-primary">Confirm Recharge</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
$rechargeOperators = [
['name' => 'Jio', 'icon' => 'bi-telephone-fill', 'op_id' => 140],
['name' => 'Airtel', 'icon' => 'bi-telephone-fill', 'op_id' => 141],
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
['name' => 'Data', 'code' => 'DATA', 'plan_id' => 3],
['name' => 'Popular', 'code' => 'POPULAR', 'plan_id' => 1],
['name' => 'Unlimited', 'code' => 'UNLIMITED', 'plan_id' => 2],
['name' => 'Talktime', 'code' => 'TALKTIME', 'plan_id' => 4],
['name' => 'SMS', 'code' => 'SMS', 'plan_id' => 5],
['name' => 'Roaming', 'code' => 'ROAMING', 'plan_id' => 6],
];
@endphp





<script>
    let clickCount = 0;

    document.getElementById("confirmRechargeBtn").addEventListener("click", function (event) {
        clickCount++;

        if (clickCount >= 3) {
            this.disabled = true;
            this.innerText = "Limit Exceeded";
            Swal.fire({
                icon: 'error',
                title: 'Limit Exceeded',
                text: 'You have attempted too many times. Please try again later.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            // event.preventDefault(); 
        }
    });
</script>


<script>
    function buildOperatorDropdown() {
        let options = `<option value="">Select Operator</option>`;
        window.rechargeOperators.forEach(op => {
            options += `<option value="${op.op_id}">${op.name}</option>`;
        });

        return `
                <div class="mb-3">
                    <label>Operator</label>
                    <select class="form-select" id="operator" name="operator">
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
                    <select class="form-select" id="circle" name="circle">
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
                    <select class="form-select" id="planType" name="planType">
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
        "Mobile Postpaid": {
            steps: ["INPUT"],
            input: () => `
                    <div class="mb-3">
                        <label>Mobile Number</label>
                        <input class="form-control" id="postmobile" name="postmobile" type="text">
                    </div>
                    ${buildOperatorDropdown()}
                    ${buildCircleDropdown()}
                `
        },

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

        "Landline Postpaid": {
            steps: ["INPUT", "FETCH_BILL"],
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
                        <button type="button" class="btn btn-outline-primary w-100">
                            <i class="bi bi-qr-code-scan me-2"></i> Scan QR Code
                        </button>
                    </div>
                `
        }
    };

    /*===============================
       GLOBAL STATE
    ================================ */
    let currentService = '';
    let stepIndex = 0;
    let selectedAmount = 0;
    let __fetchingPlans = false;


    let __payLock = false; // Pay Now multi hit stop
    let __nextClickLock = false; // fast click spam stop

    let selectedMeta = {
        mobile: '',
        operator_id: '',
        operatorName: '',
        circle_id: '',
        circleName: '',
        plan_id: '',
        planName: '',
        amount: '',
    };

    let cachedPlans = [];
    let __plansReqId = 0;
    let __plansAbortController = null;

    function renderMetaHeader(meta) {
        return `
                <div class="meta-card">
                    <div class="meta-top">
                        <div class="meta-title">
                            <div class="meta-ico"><i class="bi bi-receipt-cutoff"></i></div>
                            <div>
                                <div style="font-size:12px; opacity:.75;">Recharge Details</div>
                                <div class="meta-mobile">${meta.mobile || ''}</div>
                            </div>
                        </div>
                    </div>

                    <div class="meta-badges">
                        <div class="meta-badge"><i class="bi bi-sim"></i><span><b>Operator:</b> ${meta.operatorName || '-'}</span></div>
                        <div class="meta-badge"><i class="bi bi-geo-alt"></i><span><b>Circle:</b> ${meta.circleName || '-'}</span></div>
                        <div class="meta-badge"><i class="bi bi-lightning-charge"></i><span><b>Plan:</b> ${meta.planName || '-'}</span></div>
                    </div>
                </div>
            `;
    }

    function renderPlansList(plans, mobile) {
        let html = `${renderMetaHeader(selectedMeta)}<div class="plans-wrap"><div class="plans-scroll">`;

        plans.forEach((p) => {
            const amt = p.amount ?? p.price ?? p.recharge_amount ?? p.amt ?? 0;

            let validity = p.validity ?? p.validityDays ?? p.days ?? p.planValidity ?? '';
            let desc = p.description ?? p.desc ?? p.planName ?? '';

            let talktimeValue =
                p.talktime ??
                p.talkTime ??
                p.talktime_amount ??
                p.talktimeAmount ??
                p.talk_value ??
                '';

            validity = (validity + '').replace(/--/g, '').replace(/\s+/g, ' ').trim();
            desc = (desc + '').replace(/\s+/g, ' ').trim();

            const validityText = validity ? validity : '—';

            if (talktimeValue) {
                const tv = (talktimeValue + '').trim();
                desc = (desc ? desc + ' • ' : '') + `Talktime ₹${tv}`;
            }

            const descText = desc ? desc : 'Plan details';

            html += `
                    <button type="button" class="plan-card plan" data-amt="${amt}">
                        <div class="plan-left">
                            <div class="plan-badge">₹</div>
                            <div class="plan-meta">
                                <p class="plan-amt">₹${amt}</p>
                                <p class="plan-sub">${descText}</p>
                            </div>
                        </div>

                        <div class="plan-right">
                            <span class="plan-chip">${validityText}</span>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </button>
                `;
        });

        html += `</div></div>`;

        document.getElementById('modalBody').innerHTML = html;

        $('.plan').off('click').on('click', function() {
            $('.plan').removeClass('active');
            $(this).addClass('active');

            selectedAmount = $(this).data('amt') || 0;
            selectedMeta.amount = selectedAmount;

            stepIndex = 2; // PAY step
            loadStep();
        });
    }

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
            __fetchingPlans = false;


            __payLock = false;
            __nextClickLock = false;

            selectedMeta = {
                mobile: '',
                operator_id: '',
                operatorName: '',
                circle_id: '',
                circleName: '',
                plan_id: '',
                planName: '',
                amount: 0,
            };

            cachedPlans = [];

            if (__plansAbortController) {
                try {
                    __plansAbortController.abort();
                } catch (e) {}
            }
            __plansAbortController = null;
            __plansReqId++;

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
            $('#nextBtn').text('View Plans').show().prop('disabled', false);
            $('#modalBody').html(config.input());
            return;
        }

        if (step === "FETCH_BILL") {
            $('#nextBtn').text('Fetching...').prop('disabled', true).show();
            $('#modalBody').html(loader("Fetching bill details..."));

            setTimeout(() => {
                selectedAmount = 399;
                stepIndex++;
                loadStep();
            }, 1200);
            return;
        }

        if (step === "FETCH_PLANS") {
            $('#nextBtn').hide();

            if (cachedPlans && cachedPlans.length) {
                renderPlansList(cachedPlans, selectedMeta.mobile);
                return;
            }

            $('#modalBody').html(`
                    ${renderMetaHeader(selectedMeta)}
                    ${loader("Fetching recharge plans...")}
                `);
            return;
        }

        if (step === "PAY") {
            $('#nextBtn').text('Pay Now').show().prop('disabled', false);

            $('#modalBody').html(`
                    ${renderMetaHeader(selectedMeta)}

                    <div class="mb-3">
                        <label>Amount</label>
                        <input class="form-control" id="amount" value="₹${selectedAmount || 0}" readonly>
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

                    <div class="alert alert-info py-2 mb-0" style="font-size:12px;">
                        <i class="bi bi-shield-check me-1"></i> You will be redirected to secure payment.
                    </div>
                `);

            return;
        }
    }

    /* ===============================
       BUTTON HANDLERS

        FIXED: prevents multiple Pay Now hits

    ================================ */
    $('#nextBtn').off('click').on('click', function(e) {
        e.preventDefault();

        const config = serviceConfig[currentService];
        const step = config.steps[stepIndex];


        if (__nextClickLock) return;
        __nextClickLock = true;
        setTimeout(() => {
            __nextClickLock = false;
        }, 350);


        if (currentService === "Mobile Prepaid" && stepIndex === 0) {
            return;
        }
    });

    /* ===============================
       BUTTON HANDLERS
       FIXED: prevents multiple Pay Now hits
    ================================ */
    $('#nextBtn').off('click').on('click', function(e) {
        e.preventDefault();

        const config = serviceConfig[currentService];
        const step = config.steps[stepIndex];


        if (__nextClickLock) return;
        __nextClickLock = true;
        setTimeout(() => {
            __nextClickLock = false;
        }, 350);


        if (currentService === "Mobile Prepaid" && stepIndex === 0) {
            return;
        }



        if (currentService === "Mobile Postpaid" && stepIndex === 0) {
            document.getElementById('rechargeForm')
                .dispatchEvent(new Event('submit', {
                    cancelable: true
                }));
            return;
        }



        if (step === "PAY") {
            if (__payLock) return;
            __payLock = true;
            $('#nextBtn').prop('disabled', true);
            rechargeValidation();
            return;
        }

        if (stepIndex < config.steps.length - 1) {
            stepIndex++;
            loadStep();
        } else {
            rechargeValidation();
        }
    });

    $('#backBtn').on('click', function() {
        if (__plansAbortController) {
            try {
                __plansAbortController.abort();
            } catch (e) {}
        }
        __plansAbortController = null;

        __fetchingPlans = false;
        __plansReqId++;


        __payLock = false;
        $('#nextBtn').prop('disabled', false);


        if (currentService === "Mobile Postpaid" && stepIndex === 0) {
            document.getElementById('rechargeForm')
                .dispatchEvent(new Event('submit', {
                    cancelable: true
                }));
            return;
        }


        if (step === "PAY") {
            if (__payLock) return;
            __payLock = true;
            $('#nextBtn').prop('disabled', true);
            rechargeValidation();
            return;
        }

        if (stepIndex < config.steps.length - 1) {
            stepIndex++;
            loadStep();
        } else {
            rechargeValidation();
        }
    });


    $('#backBtn').off('click').on('click', function () {

   
        if (__plansAbortController) {
            try { __plansAbortController.abort(); } catch (e) {}
        }
        __plansAbortController = null;

       __fetchingPlans = false;
       __plansReqId++;

   
       __payLock = false;
       $('#nextBtn').prop('disabled', false);
       if (stepIndex > 0) stepIndex--;
        loadStep();
    });


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

        if (currentService !== "Mobile Prepaid") return;


        if (stepIndex !== 0) return;

        if (!document.getElementById('mobile')) return;
        if (__fetchingPlans) return;

        __fetchingPlans = true;

        const mobile = document.getElementById('mobile').value;

        const operatorSelect = document.getElementById('operator');
        const circleSelect = document.getElementById('circle');
        const planSelect = document.getElementById('planType');

        const operator_id = operatorSelect.value;
        const circle_id = circleSelect.value;
        const plan_type = planSelect.value;

        if (!mobile || !operator_id || !circle_id || !plan_type) {
            __fetchingPlans = false;
            alert('Please fill all fields');
            return;
        }

        const operatorName = operatorSelect.options[operatorSelect.selectedIndex]?.text || '';
        const circleName = circleSelect.options[circleSelect.selectedIndex]?.text || '';
        const planName = planSelect.options[planSelect.selectedIndex]?.text || '';

        selectedMeta = {
            mobile,
            operator_id,
            operatorName,
            circle_id,
            circleName,
            plan_id: plan_type,
            planName,
            amount: 0,
        };

        cachedPlans = [];

        stepIndex = 1; // FETCH_PLANS
        loadStep();

        if (__plansAbortController) {
            try {
                __plansAbortController.abort();
            } catch (e) {}
        }
        __plansAbortController = new AbortController();
        const reqId = ++__plansReqId;

        fetch('/user/bbps-recharge/getPlans/' + operator_id + '/' + circle_id + '/' + plan_type, {
                method: 'POST',
                signal: __plansAbortController.signal,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    mobile,
                    operator_id,
                    circle_id,
                    plan_type
                })
            })
            .then(res => res.json())
            .then(response => {
                if (reqId !== __plansReqId) return;

                __fetchingPlans = false;

                if (!response.success) {
                    alert(response.message || 'Plans not available');
                    stepIndex = 0;
                    loadStep();
                    return;
                }

                cachedPlans = response.data || [];
                renderPlansList(cachedPlans, mobile);
            })
            .catch(error => {
                if (error.name === 'AbortError') return;
                console.error(error);
                __fetchingPlans = false;
                alert('Error fetching plans');
                stepIndex = 0;
                loadStep();
            });
    });
</script>

<script>
    function rechargeValidation() {
        const payload = {
            amt: String(selectedMeta.amount),
            cn: String(selectedMeta.mobile),
            op: String(selectedMeta.operator_id),
            cir: String(selectedMeta.circle_id),
            planCode: String(selectedMeta.plan_id),
            adParams: {}
        };

        fetch("{{ route('bbps.validateRecharge') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.message?.code === "RECHARGEVALIDATIONSUCCESS") {
                    Swal.fire({
                        icon: 'error',
                        title: `<span style="font-size:15px; color: #AA4A44;">${res.message?.code || 'N/A'}</span>`,
                        html: `<p>${res.message?.text || 'Validation failed. Please try again.'}</p>`,
                        confirmButtonText: 'OK'
                    });
                    return;

                } else if (res.message?.code === "RECHARGEVALIDATIONFAILURE") {

                    const currentModalEl = document.querySelector('#rechargeModal.modal.show');
                    if (currentModalEl) {
                        const currentModal = bootstrap.Modal.getInstance(currentModalEl);
                        currentModal.hide();
                    }

                    document.getElementById('rechargeAmount').value = selectedMeta.amount;
                    document.getElementById('rechargeMobile').value = selectedMeta.mobile;
                    document.getElementById('rechargeOperatorId').value = selectedMeta.operator_id;
                    document.getElementById('rechargeCircleId').value = selectedMeta.circle_id;
                    document.getElementById('rechargePlanId').value = selectedMeta.plan_id;

                    const mpinModal = new bootstrap.Modal(document.getElementById('mpinModal'));
                    mpinModal.show();

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: `<span style="font-size:15px; color: #AA4A44;">${res.message || 'N/A'}</span>`,
                        html: `<p>${res.message || 'Validation failed. Please try again.'}</p>`,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                alert('Recharge validation error');
            })
            .finally(() => {

                __payLock = false;
                $('#nextBtn').prop('disabled', false);
            });
    }

    function submitMpin(e) {
        e.preventDefault();

        const form = document.getElementById('mpinForm');
        const formData = new FormData(form);

        const mpin = formData.get('mpin');

        if (!/^\d{4}$/.test(mpin)) {
            document.getElementById('mpinError').classList.remove('d-none');
            return;
        }
        document.getElementById('mpinError').classList.add('d-none');

        fetch("{{ route('bbps.mpin_auth') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.status == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'MPIN Verified',
                        html: res.message?.text || res.message || 'MPIN verified successfully',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        const mpinModalEl = document.getElementById('mpinModal');
                        const mpinModal = bootstrap.Modal.getInstance(mpinModalEl);
                        if (mpinModal) mpinModal.hide();
                        document.getElementById('mpinInput').value = '';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'MPIN Verification Failed',
                        html: `<p>${res.message?.text || res.message || 'There was an error verifying your MPIN.'}</p>`,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    text: err.message || 'An error occurred while processing your request.',
                    confirmButtonText: 'OK'
                });
            });
    }
</script>

<script>
    document.getElementById('rechargeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (currentService !== 'Mobile Postpaid') return;

        const modal = document.getElementById('rechargeModal');

        const data = {
            cn: modal.querySelector('#postmobile')?.value,
            op: modal.querySelector('#operator')?.value,
            cir: modal.querySelector('#circle')?.value
        };

        console.log('Postpaid Data:', data);

        fetch("{{ route('bbps.postpaid_villBill') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                console.log('Server Response:', res);
            })
            .catch(err => {
                console.error('Error:', err);
            });
    });
</script>
@endsection