@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="row g-4">

    <!-- Card 1 : Banking Image -->
    <!-- <div class="col-md-3">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body d-flex align-items-center justify-content-center p-2">
                <img src="{{ asset('assets/image/pay-image.jpg') }}"
                    class="img-fluid"
                    style="max-width: 100%; max-height: 100%; object-fit: contain;"
                    alt="Banking System">
            </div>
        </div>
    </div> -->


    <!-- Card 2 : Our Services -->
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Our Services</h6>

                <div class="row g-3 text-center">

                    @php
                    $services = [
                    ['name' => 'Bill Pay', 'icon' => 'bi-receipt'],
                    ['name' => 'Cash Collection', 'icon' => 'bi-cash-stack'],
                    ['name' => 'Digital Wallet', 'icon' => 'bi-wallet2'],
                    ['name' => 'DTH Recharge', 'icon' => 'bi-tv'],
                    ['name' => 'OTT', 'icon' => 'bi-play-btn'],
                    ['name' => 'OTH Recharge', 'icon' => 'bi-phone'],
                    ['name' => 'Scan Pay', 'icon' => 'bi-qr-code-scan'],
                    ['name' => 'Uber', 'icon' => 'bi-car-front'],
                    ];

                    $colors = ['#f94144','#f3722c','#f8961e','#f9c74f','#90be6d','#43aa8b','#577590','#277da1','#9d4edd','#ff6d00','#1982c4','#6a4c93'];
                    @endphp


                    @foreach($services as $service)
                    @php
                    $randColor = $colors[array_rand($colors)];
                    @endphp

                    <div class="col-6">
                        <div class="border rounded p-2 h-100 service-box bg-light">
                            <i class="bi {{ $service['icon'] }} fs-4" style="color: {{ $randColor }}"></i>
                            <a href="{{ route('utility_service') }}"
                                class="text-decoration-none text-dark small fw-semibold mt-1 d-block text-center">
                                {{ $service['name'] }}
                            </a>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>



    <!-- Card 3 : Welcome + User Details + Actions -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100 position-relative text-white"
            style="background: linear-gradient(to top, #6b83ec, #485050);">

            <div class="card-body d-flex flex-column align-items-center justify-content-between py-4">

                <!-- Top Spacer -->
                <div></div>

                <!-- Heading -->
                <h5 class="fw-bold mb-4 text-center">Welcome User</h5>


                <!-- User Profile Image -->
                <img src="{{asset('assets\image\user.jpg')}}" alt="User Profile"
                    class="rounded-circle mb-3"
                    style="width:75px; height:75px; object-fit:cover;">

                <!-- Profile Details -->
                <div class="row g-2 small w-100" style="max-width: 300px;">
                    <div class="col-6 opacity-75">User Type</div>
                    <div class="col-6 fw-semibold">E-mail</div>

<div class="col-6 opacity-75">Retailer</div>
                    <div class="col-6 fw-semibold">User@gmail.com</div>

                    <div class="col-6 opacity-75">Entity Type</div>
                    <div class="col-6 fw-semibold">Contact</div>

                    <div class="col-6 opacity-75">Individual</div>
                    <div class="col-6 fw-semibold">9876543210</div>
                </div>

                <!-- Bottom Spacer -->
                <div></div>

            </div>
        </div>
    </div>



</div>

<!-- Row: Date & Text Inputs + Search Button -->
<div class="row g-2 my-4 align-items-end">

    <!-- From Date -->
    <div class="col-md-2">
        <label class="form-label small">From Date</label>
        <input type="date" class="form-control"
            value="{{ now()->format('Y-m-d') }}"
            max="{{ now()->format('Y-m-d') }}">
    </div>

    <!-- To Date -->
    <div class="col-md-2">
        <label class="form-label small">To Date</label>
        <input type="date" class="form-control"
            value="{{ now()->format('Y-m-d') }}"
            max="{{ now()->format('Y-m-d') }}">
    </div>


    <!-- Category Select -->
    <div class="col-md-3">
        <label class="form-label small">Category</label>
        <select class="form-select">
            <option selected disabled>--Select Category--</option>
            <option value="billing">Billing</option>
            <option value="recharge">Recharge</option>
            <option value="wallet">Digital Wallet</option>
            <option value="others">Others</option>
        </select>
    </div>

    <!-- Service Select -->
    <div class="col-md-3">
        <label class="form-label small">Service</label>
        <select class="form-select">
            <option selected disabled>--Select Service--</option>
            <option value="billpay">Bill Pay</option>
            <option value="cashcollection">Cash Collection</option>
            <option value="dth">DTH Recharge</option>
            <option value="ott">OTT</option>
            <option value="scanpay">Scan Pay</option>
            <option value="uber">Uber</option>
        </select>
    </div>

    <!-- Search Button -->
    <div class="col-md-2">
        <button type="button" class="btn font-semibold text-white rounded hover:opacity-90 transition">
            Search
        </button>
    </div>
</div>



<!-- Row: Transaction Cards -->
<div class="row g-3 mt-4">

    <!-- Card 1: Success vs Failure Transaction -->
    <div class="col-md-6">
        <div class="card shadow-sm p-3" style="height:400px;">
            <h5 class="card-title fw-bold">Success Vs Failure Transaction</h5>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <p class="text-muted">No data found</p>
            </div>
        </div>
    </div>

    <!-- Card 2: Success Transaction By Service Wise -->
    <div class="col-md-6">
        <div class="card shadow-sm p-3" style="height:400px;">
            <h5 class="card-title fw-bold">Success Transaction By Service Wise</h5>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <p class="text-muted">No data found</p>
            </div>
        </div>
    </div>

</div>


@endsection