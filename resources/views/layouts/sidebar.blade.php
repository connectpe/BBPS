<style>
    .nav-item {
        margin-top: 10px;
    }
</style>
<nav id="sidebar" class="d-flex flex-column sidebar  p-3 text-white">

    <!-- Close Button  -->
    <div class="d-flex justify-content-end d-lg-none mb-3">
        <button id="sidebarClose" class="btn btn-outline-light">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>


    <h5 class="text-center mb-4">BBPS PANEL</h5>

    @php

    $role = 1// 1 for the admin and 2 for the user

    @endphp

    @if($role == 1)
    <ul class="nav nav-pills flex-column mb-auto">

        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{route('dashboard')}}" class="nav-link text-white  {{Route::is('dashboard') ? 'sidebar-active' : ''}}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <!-- Services -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">

                <!-- Services Dropdown -->
                @php
                // Array of child route names for the Services dropdown
                $serviceRoute = [
                'utility_service',
                'recharge_service',
                'banking_service',
                ];

                // Check if the current route is in the array
                $servicesActive = in_array(Route::currentRouteName(), $serviceRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $servicesActive ? '' : 'collapsed' }} {{ $servicesActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse"
                        href="#bankingServices"
                        role="button"
                        aria-expanded="{{ $servicesActive ? 'true' : 'false' }}"
                        aria-controls="bankingServices">
                        <span><i class="bi bi-grid-1x2 me-2"></i></i> Services</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $servicesActive ? 'show' : '' }} ms-3" id="bankingServices">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{route('banking_service')}}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'banking_service' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-bank me-2"></i> <!-- Icon for Banking -->
                                    Banking Services
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('utility_service') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'utility_service' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-lightning-charge-fill me-2"></i> <!-- Icon for Utility -->
                                    Utility Services
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{route('recharge_service')}}" class="nav-link text-white {{ Route::currentRouteName() == 'recharge_service' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-phone-fill me-2"></i> Recharge
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </li>

        <!-- Transaction  -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">

                <!-- Transaction Dropdown -->
                @php
                // Array of child route names for the Services dropdown
                $transactionRoute = [
                'transaction_status',
                'transaction_complaint',
                'complaint_status',
                ];

                // Check if the current route is in the array
                $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $transactionActive ? '' : 'collapsed' }} {{ $transactionActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse"
                        href="#transaction"
                        role="button"
                        aria-expanded="{{ $transactionActive ? 'true' : 'false' }}"
                        aria-controls="transaction">
                        <span><i class="bi bi-receipt-cutoff me-2"></i> Transactions</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $transactionActive ? 'show' : '' }} ms-3" id="transaction">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{route('transaction_status')}}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'transaction_status' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-list-check me-2"></i> <!-- Icon for Banking -->
                                    Transaction Status
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('transaction_complaint') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'transaction_complaint' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <!-- Icon for Utility -->
                                    Transaction Complaint
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{route('complaint_status')}}" class="nav-link text-white {{ Route::currentRouteName() == 'complaint_status' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-info-circle-fill me-2"></i> Complaint Status
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </li>

        <li class="nav-item">
            <a href="{{route('users')}}" class="nav-link text-white {{Route::is('users') ? 'sidebar-active' : ''}}">
                <i class="bi bi-people-fill me-2"></i> Users
            </a>
        </li>

        <li class="nav-item">
           <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-link nav-link text-white p-0 m-0 align-middle">
                    Logout
                </button>
            </form>
            
        </li>

    </ul>
    @elseif($role == 2)
    <ul class="nav nav-pills flex-column mb-auto">

        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{route('dashboard')}}" class="nav-link text-white  {{Route::is('dashboard') ? 'sidebar-active' : ''}}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <!-- Services -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">

                <!-- Services Dropdown -->
                @php
                // Array of child route names for the Services dropdown
                $serviceRoute = [
                    'utility_service',
                    'recharge_service',
                    'banking_service',
                ];

                // Check if the current route is in the array
                $servicesActive = in_array(Route::currentRouteName(), $serviceRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $servicesActive ? '' : 'collapsed' }} {{ $servicesActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse"
                        href="#bankingServices"
                        role="button"
                        aria-expanded="{{ $servicesActive ? 'true' : 'false' }}"
                        aria-controls="bankingServices">
                        <span><i class="bi bi-grid-1x2 me-2"></i></i> Services</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $servicesActive ? 'show' : '' }} ms-3" id="bankingServices">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{route('banking_service')}}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'banking_service' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-bank me-2"></i> <!-- Icon for Banking -->
                                    Banking Services
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('utility_service') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'utility_service' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-lightning-charge-fill me-2"></i> <!-- Icon for Utility -->
                                    Utility Services
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{route('recharge_service')}}" class="nav-link text-white {{ Route::currentRouteName() == 'recharge_service' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-phone-fill me-2"></i> Recharge
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </li>

        <!-- Transaction  -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">

                <!-- Transaction Dropdown -->
                @php
                // Array of child route names for the Services dropdown
                $transactionRoute = [
                'transaction_status',
                'transaction_complaint',
                'complaint_status',
                ];

                // Check if the current route is in the array
                $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $transactionActive ? '' : 'collapsed' }} {{ $transactionActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse"
                        href="#transaction"
                        role="button"
                        aria-expanded="{{ $transactionActive ? 'true' : 'false' }}"
                        aria-controls="transaction">
                        <span><i class="bi bi-receipt-cutoff me-2"></i> Transactions</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $transactionActive ? 'show' : '' }} ms-3" id="transaction">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{route('transaction_status')}}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'transaction_status' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-list-check me-2"></i> <!-- Icon for Banking -->
                                    Transaction Status
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('transaction_complaint') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'transaction_complaint' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <!-- Icon for Utility -->
                                    Transaction Complaint
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{route('complaint_status')}}" class="nav-link text-white {{ Route::currentRouteName() == 'complaint_status' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-info-circle-fill me-2"></i> Complaint Status
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </li>


        <li class="nav-item">
           <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-link nav-link text-white p-0 m-0 align-middle">
                    Logout
                </button>
            </form>
            
        </li>

    </ul>
    @endif



</nav>