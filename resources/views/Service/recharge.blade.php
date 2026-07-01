@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')
@section('content')
<style>
    .meta-badge,
    .meta-card {
        border: 1px solid rgba(0, 0, 0, .08)
    }

    .meta-title,
    .meta-top {
        align-items: center;
        display: flex;
        gap: 10px
    }

    .plan-left,
    .plan-meta {
        min-width: 0
    }

    #rechargeModal .modal-header,
    .meta-card {
        background: linear-gradient(135deg, rgba(13, 110, 253, .1), rgba(25, 135, 84, .08))
    }

    .card-height {
        height: 400px
    }

    .meta-card {
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 12px
    }

    .meta-top {
        justify-content: space-between;
        margin-bottom: 10px
    }

    .meta-title {
        font-weight: 700;
        font-size: 14px;
        color: rgba(0, 0, 0, .75)
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
        flex: 0 0 auto
    }

    .meta-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px
    }

    .meta-badge {
        background: rgba(255, 255, 255, .75);
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 12px;
        color: rgba(0, 0, 0, .7);
        align-items: center;
        gap: 6px
    }

    .plan-card,
    .plan-left {
        align-items: center;
        gap: 12px;
        display: flex
    }

    .meta-badge i {
        opacity: .85
    }

    .meta-mobile {
        font-weight: 800;
        color: rgba(0, 0, 0, .8)
    }

    .plans-wrap {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 12px;
        overflow: hidden;
        background: #fff
    }

    .plans-scroll {
        max-height: 320px;
        overflow-y: auto
    }

    .plan-card {
        width: 100%;
        text-align: left;
        padding: 12px 14px;
        background: #fff;
        border: 0;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        transition: .15s ease-in-out;
        justify-content: space-between
    }

    .plan-card:last-child {
        border-bottom: 0
    }

    .plan-card:hover {
        background: rgba(13, 110, 253, .05);
        transform: translateY(-1px)
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
        font-weight: 700
    }

    .plan-amt {
        font-weight: 800;
        font-size: 15px;
        margin: 0;
        line-height: 1.2;
        white-space: nowrap;
        color: rgba(0, 0, 0, .8)
    }

    .plan-sub {
        margin: 2px 0 0;
        font-size: 12px;
        color: rgba(0, 0, 0, .65);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .plan-right {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 8px;
        color: rgba(0, 0, 0, .55);
        font-size: 12px
    }

    .plan-chip {
        background: rgba(0, 0, 0, .05);
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 700
    }

    .plan-card.active {
        background: rgba(25, 135, 84, .1);
        outline: rgba(25, 135, 84, .22) solid 2px
    }


    #rechargeModal .modal-content {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(0, 0, 0, .18)
    }

    #rechargeModal .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        padding: 14px 16px
    }

    #rechargeModal .modal-title {
        font-weight: 900;
        font-size: 16px;
        color: rgba(0, 0, 0, .78)
    }

    #rechargeModal .btn-close {
        opacity: .7
    }

    #rechargeModal .btn-close:hover {
        opacity: 1
    }

    #rechargeModal .modal-body {
        padding: 16px;
        background: #fff
    }

    #rechargeModal .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, .06);
        background: rgba(0, 0, 0, .015);
        padding: 12px 16px;
        gap: 10px
    }

    #rechargeModal #backBtn,
    #rechargeModal #nextBtn {
        border-radius: 12px;
        font-weight: 800;
        padding: 10px 14px
    }

    #rechargeModal #nextBtn {
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18)
    }

    #rechargeModal #nextBtn:active {
        transform: translateY(1px);
        box-shadow: 0 6px 12px rgba(13, 110, 253, .14)
    }

    #rechargeModal .form-control,
    #rechargeModal .form-select {
        border-radius: 12px;
        padding: 10px 12px;
        border-color: rgba(0, 0, 0, .12)
    }

    #rechargeModal .form-control:focus,
    #rechargeModal .form-select:focus {
        border-color: rgba(13, 110, 253, .35);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12)
    }

    #rechargeModal .form-label,
    #rechargeModal label {
        font-weight: 800;
        font-size: 12px;
        color: rgba(0, 0, 0, .65)
    }

    #rechargeModal .spinner-border {
        width: 2rem;
        height: 2rem
    }

    #rechargeModal .text-center p {
        font-size: 12px;
        color: rgba(0, 0, 0, .55)
    }

    .service-col {
        width: 10%;
        flex: 0 0 10%
    }

    @media (max-width:992px) {
        .service-col {
            width: 20%;
            flex: 0 0 20%
        }
    }

    .bc-logo {
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: auto
    }

    @media (max-width:768px) {
        .bc-logo {
            width: 80px;
            top: 5px;
            right: 5px
        }
    }

    @media (max-width:576px) {
        #rechargeModal .modal-dialog {
            margin: 12px
        }

        #rechargeModal .modal-body {
            padding: 14px
        }

        #rechargeModal .modal-footer {
            padding: 12px 14px
        }

        #rechargeModal #backBtn,
        #rechargeModal #nextBtn {
            width: 100%
        }

        .service-col {
            width: 25%;
            flex: 0 0 25%
        }

        .bc-logo {
            position: static;
            display: block;
            margin: 0 auto 10px;
            width: 80px
        }
    }
</style>



<div class="row g-4 mb-3">

    @php
    $services = [
    ['name' => 'AEPS', 'icon' => 'bi-person-badge'],
    ['name' => 'Agent Collection', 'icon' => 'bi-people'],
    ['name' => 'Broadband Postpaid', 'icon' => 'bi-wifi'],
    ['name' => 'Cable TV', 'icon' => 'bi-tv'],
    ['name' => 'Clubs and Associations', 'icon' => 'bi-people'],
    ['name' => 'Credit Card', 'icon' => 'bi-credit-card'],
    ['name' => 'DTH', 'icon' => 'bi-display'],
    ['name' => 'Education Fees', 'icon' => 'bi-mortarboard'],
    ['name' => 'Electricity', 'icon' => 'bi-lightning-charge'],
    ['name' => 'Fastag', 'icon' => 'bi-signpost'],
    ['name' => 'Gas', 'icon' => 'bi-fire'],
    ['name' => 'eChallan', 'icon' => 'bi-receipt'],
    ['name' => 'EV Recharge', 'icon' => 'bi-ev-station'],
    ['name' => 'Fleet Card Recharge', 'icon' => 'bi-truck'],
    ['name' => 'Housing Society', 'icon' => 'bi-building'],
    ['name' => 'Insurance', 'icon' => 'bi-shield-check'],
    ['name' => 'Landline Postpaid', 'icon' => 'bi-telephone'],
    ['name' => 'Loan Repayment', 'icon' => 'bi-cash-stack'],
    ['name' => 'LPG Gas', 'icon' => 'bi-fire'],
    ['name' => 'Mobile Postpaid', 'icon' => 'bi-phone'],
    ['name' => 'Mobile Prepaid', 'icon' => 'bi-phone'],
    ['name' => 'Municipal Services', 'icon' => 'bi-gear'],
    ['name' => 'Municipal Taxes', 'icon' => 'bi-currency-dollar'],
    ['name' => 'National Pension System', 'icon' => 'bi-graph-up'],
    ['name' => 'NCMC Recharge', 'icon' => 'bi-wallet2'],
    ['name' => 'Prepaid Meter', 'icon' => 'bi-speedometer2'],
    ['name' => 'Rental', 'icon' => 'bi-building-check'],
    ['name' => 'Subscription', 'icon' => 'bi-journal-text'],
    ['name' => 'Water', 'icon' => 'bi-droplet'],
    ];

    $colors = [
    '#f94144',
    '#f3722c',
    '#f8961e',
    '#f9c74f',
    '#90be6d',
    '#43aa8b',
    '#577590',
    '#277da1',
    '#9d4edd',
    '#ff6d00',
    '#1982c4',
    '#6a4c93',
    ];
    @endphp

    <div class="col-md-12">
        <div class="card shadow-sm h-100">
            <div class="card-body">

                <div class="position-relative">

                    <!-- TOP RIGHT LOGO -->
                    <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}" alt="logo" class="bc-logo">

                    <div class="row align-items-center">

                        <!-- LEFT: Title -->
                        <div class="col-md-2 text-center text-md-start mb-2">
                            <small class="mb-0" style="color:#333;">
                                Select Bharat-Connect Service
                            </small>
                        </div>

                        <!-- CENTER: Services -->
                        <div class="col-md-9">
                            <div class="d-flex flex-wrap justify-content-center">

                                @foreach ($services as $service)
                                @php $randColor = $colors[array_rand($colors)]; @endphp

                                <div class="service-col text-center mb-2">

                                    <a href="javascript:void(0)"
                                        class="text-decoration-none text-dark d-flex flex-column align-items-center service-btn"
                                        data-service="{{ $service['name'] }}">

                                        <div class="rounded-circle d-flex justify-content-center align-items-center"
                                            style="width:40px; height:40px; font-size:1.1rem; background:#e1e8ef; color: {{ $randColor }};">

                                            <i class="bi {{ $service['icon'] }}"></i>
                                        </div>

                                        <span style="font-size:10px;" class="mt-1 text-center">
                                            {{ $service['name'] }}
                                        </span>

                                    </a>
                                </div>
                                @endforeach

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="row">
        <div class="col-md-6">

            @php
            $categories = ['Adani Electricity', 'BSES Rajdhani Power Limited', 'Torrent Power Limited'];
            @endphp

            <div class="card shadow-sm card-height h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Basic List Group</h5>
                    <!-- <img src="{{ asset('assets/image/Logo/bhaxrat-connect-logo.jpg') }}" alt="" style="width: 70px;"> -->
                </div>
                <div class="card-body d-flex flex-column">
                    <form class="row">

                        <div class="col-12 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">--Select Category--</option>
                                @foreach ($bbpsCategories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->bbps_category_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="biller_name" class="form-label">Select Biller:</label>
                            <select class="form-select" id="biller-list" name="biller_name">
                                <option value="">-- Select Biller --</option>
                            </select>
                        </div>


                        <!-- Buttons -->
                        <div class="col-12 mt-2 gap-2">
                            <button type="button"
                                class="btn w-100 font-semibold rounded hover:opacity-90 transition buttonColor">

                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm card-height h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Request Form</h5>
                    <!-- <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}" alt="" style="width: 70px;"> -->
                </div>
                <div class="card-body d-flex flex-column">
                    <form class="row g-3 flex-grow-1">
                        <!-- Biller Name -->
                        <div class="col-12">
                            <label for="billerName" class="form-label">Biller Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="billerName" placeholder="Biller Name">
                        </div>

                        <!-- Contract Account Number -->
                        <div class="col-12">
                            <label for="account_no" class="form-label">Contract Account Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_no"
                                placeholder="Contract Account Number">
                        </div>

                        <!-- TPIN -->
                        <div class="col-12">
                            <label for="TPIN" class="form-label">TPIN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="TPIN" placeholder="Enter TPIN">
                        </div>

                        <!-- Button -->
                        <div class="col-12">
                            <button type="button"
                                class="btn w-100 font-semibold rounded hover:opacity-90 transition buttonColor">

                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>



{{-- Static View Bill Modal  --}}



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


@include('Include.Service-modal.prepaid-recharge')
@include('Include.Service-modal.view-bill')

<script>
    let clickCount = 0;

    $("#confirmRechargeBtn").on("click", function(event) {
        clickCount++;
        if (clickCount >= 4) {
            $(this).prop("disabled", true);
            $(this).text("Limit Exceeded");

            Swal.fire({
                icon: 'error',
                title: 'Limit Exceeded',
                text: 'You have attempted too many times. Please try again later.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        }
    });


    // Event binding
    $(document).on('click', '.service-btn', function() {
        let service = $(this).data('service');
        openServiceModal(service);
    });

    function openServiceModal(service) {
        if (service === 'Mobile Prepaid') {
            $('#rechargeModal').modal('show');
        } else {
            $('#viewBillModal').modal('show');
        }
    }

    function spinLoader(text) {
        return `
                <div class="text-center my-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">${text}</p>
                </div>
            `;
    }

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
    $('#category').on('change', function() {
        let categoryId = $(this).val();

        $('#biller-list').html('<option value="">Loading...</option>');

        if (categoryId) {
            $.ajax({
                url: '/get-billers/' + categoryId,
                type: 'GET',
                success: function(response) {
                    let options = '<option value="">-- Select Biller --</option>';

                    if (response.length > 0) {
                        response.forEach(function(biller) {
                            options += `
                            <option value="${biller.biller_name}">
                                ${biller.biller_name}
                            </option>
                        `;
                        });
                    } else {
                        options = '<option value="">No billers found</option>';
                    }

                    $('#biller-list').html(options);
                }
            });
        } else {
            $('#biller-list').html('<option value="">-- Select Biller --</option>');
        }
    });
</script>
@endsection