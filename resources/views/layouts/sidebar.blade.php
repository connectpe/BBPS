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
    @php
        $role = Auth::user()->role_id;
    @endphp

    @if ($role == 1)
        <h5 class="text-center mb-4">ADMIN PANEL </h5>
    @else
        <h5 class="text-center mb-4">USER PANEL </h5>
    @endif



    @if ($role == 1)

    <ul class="nav nav-pills flex-column mb-auto">

        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
                class="nav-link text-white {{ Route::is('dashboard') ? 'sidebar-active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>


        <!-- User Management -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">

                @php
                $userRoute = ['users', 'view_user','request_services'];
                $userActive = in_array(Route::currentRouteName(), $userRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $userActive ? '' : 'collapsed' }} {{ $userActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse" href="#userManagement" role="button"
                        aria-expanded="{{ $userActive ? 'true' : 'false' }}" aria-controls="userManagement">
                        <span><i class="bi bi-people me-2"></i> User Management</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $userActive ? 'show' : '' }} ms-3" id="userManagement">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('users') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'users' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-person-lines-fill me-2"></i>
                                    Users
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('request_services') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'request_services' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-clipboard-check me-2"></i>
                                    Service Requests
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
            </ul>
        </li>

        <!-- Transactions -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">

                @php
                $transactionRoute = ['reports/recharge','reports/utility','reports/banking','reports'];
                $servicesActive = in_array(Route::currentRouteName(), $transactionRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $servicesActive ? '' : 'collapsed' }} {{ $servicesActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse" href="#transactionManagement" role="button"
                        aria-expanded="{{ $servicesActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-cash-stack me-2"></i> Transactions</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $servicesActive ? 'show' : '' }} ms-3" id="transactionManagement">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ url('reports/recharge') }}"
                                    class="nav-link text-white {{ request()->is('reports/recharge') ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-phone me-2"></i>
                                    Recharge
                                </a>
                            </li>


                            <li class="nav-item">
                                <a href="{{ url('reports/banking') }}"
                                    class="nav-link text-white {{ request()->is('reports/banking') ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-bank me-2"></i>
                                    Banking
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('reports/utility') }}"
                                    class="nav-link text-white {{ request()->is('reports/utility') ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    Utility
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </li>

        <!-- Reports -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">
                @php
                $reportRoute = ['complain.report','api_log'];
                $reportActive = in_array(Route::currentRouteName(), $reportRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $reportActive ? '' : 'collapsed' }} {{ $reportActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse" href="#reportRoute" role="button"
                        aria-expanded="{{ $reportActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-file-earmark-text me-2"></i> Reports
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $reportActive ? 'show' : '' }} ms-3" id="reportRoute">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="{{ route('complain.report') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'complain.report' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-file-earmark-text me-2"></i> Complaint
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('api_log') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'api_log' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-journal-text me-2"></i> API
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </li>

          <!-- Matster Settings -->
        <li class="nav-item mt-2">
            <ul class="nav nav-pills flex-column mb-auto">
                @php
                $masterRoute = ['our_servicess','providers'];
                $masterActive = in_array(Route::currentRouteName(), $masterRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $masterActive ? '' : 'collapsed' }} {{ $masterActive ? 'sidebar-active' : '' }}"
                        data-bs-toggle="collapse" href="#masterRoute" role="button"
                        aria-expanded="{{ $masterActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-gear me-2"></i> Master Setting
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $masterActive ? 'show' : '' }} ms-3" id="masterRoute">
                        <ul class="nav flex-column">

                            <li class="nav-item">
                                <a href="{{ route('our_servicess') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'our_servicess' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-briefcase me-2"></i>
                                    Our Services
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('providers') }}"
                                    class="nav-link text-white {{ Route::currentRouteName() == 'providers' ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-person-badge me-2"></i> Providers
                                </a> 
                            </li>

                        </ul>
                    </div>
                </li>
            </ul>
        </li>

        <!-- Ledger -->
        <li class="nav-item">
            <a href="{{ route('ladger.index') }}"
                class="nav-link text-white {{ Route::currentRouteName() == 'ladger.index' ? 'sidebar-active' : '' }}">
                <i class="bi bi-journal-text me-2"></i>
                Ledger
            </a>
        </li>
         <li class="nav-item">
            <a href="{{ route('schemes.index') }}"
                class="nav-link text-white {{ Route::currentRouteName() == 'schemes.index' ? 'sidebar-active' : '' }}">
                <i class="bi bi-diagram-3 me-2"></i> Schemes
            </a>
        </li>


        <!-- Logout -->
        <li class="nav-item">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="nav-link btn btn-link text-white w-100 text-start d-flex align-items-center gap-2 px-3"
                    style="background-color: #e76666;">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                    <span>Logout</span>
                </button>
            </form>
        </li>

    </ul>



    @elseif($role == 2)
        <ul class="nav nav-pills flex-column mb-auto">

            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                    class="nav-link text-white {{ Route::is('dashboard') ? 'sidebar-active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <!-- Services -->
            <li class="nav-item mt-2">
                <ul class="nav nav-pills flex-column mb-auto">

                    @php
                        $serviceRoute = ['utility_service', 'recharge_service', 'banking_service'];
                        $servicesActive = in_array(Route::currentRouteName(), $serviceRoute);
                    @endphp

                    <li class="nav-item">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $servicesActive ? '' : 'collapsed' }} {{ $servicesActive ? 'sidebar-active' : '' }}"
                            data-bs-toggle="collapse" href="#bankingServices" role="button"
                            aria-expanded="{{ $servicesActive ? 'true' : 'false' }}">
                            <span><i class="bi bi-gear-fill me-2"></i> Services</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>

                        <div class="collapse {{ $servicesActive ? 'show' : '' }} ms-3" id="bankingServices">
                            <ul class="nav flex-column">

                                <li class="nav-item">
                                    <a href="{{ route('banking_service') }}"
                                        class="nav-link text-white {{ Route::currentRouteName() == 'banking_service' ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-bank me-2"></i>
                                        Banking Services
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('utility_service') }}"
                                        class="nav-link text-white {{ Route::currentRouteName() == 'utility_service' ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-lightning-charge me-2"></i>
                                        Utility Services
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('recharge_service') }}"
                                        class="nav-link text-white {{ Route::currentRouteName() == 'recharge_service' ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-phone me-2"></i>
                                        Recharge Services
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </li>

            <!-- Transaction -->
            <li class="nav-item mt-2">
                <ul class="nav nav-pills flex-column mb-auto">

                    @php
                        $transactionRoute = [
                            'transaction_status',
                            'transaction_complaint',
                            'complaint_status',
                            'reports/recharge',
                            'reports/banking',
                            'reports/utility',
                            'reports',
                        ];
                        $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);
                    @endphp

                    <li class="nav-item">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $transactionActive ? '' : 'collapsed' }} {{ $transactionActive ? 'sidebar-active' : '' }}"
                            data-bs-toggle="collapse" href="#transaction" role="button"
                            aria-expanded="{{ $transactionActive ? 'true' : 'false' }}">
                            <span><i class="bi bi-receipt me-2"></i> Transaction Report</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>

                        <div class="collapse {{ $transactionActive ? 'show' : '' }} ms-3" id="transaction">
                            <ul class="nav flex-column">

                                <li class="nav-item">
                                    <a href="{{ url('reports/recharge') }}"
                                        class="nav-link text-white {{ request()->is('reports/recharge') ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-phone me-2"></i>
                                        Recharge
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ url('reports/banking') }}"
                                        class="nav-link text-white {{ request()->is('reports/banking') ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-bank me-2"></i>
                                        Banking
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ url('reports/utility') }}"
                                        class="nav-link text-white {{ request()->is('reports/utility') ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-lightning-charge me-2"></i>
                                        Utility
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('transaction_status') }}"
                                        class="nav-link text-white {{ Route::currentRouteName() == 'transaction_status' ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-list-check me-2"></i>
                                        Transaction Status
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('transaction_complaint') }}"
                                        class="nav-link text-white {{ Route::currentRouteName() == 'transaction_complaint' ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-exclamation-octagon me-2"></i>
                                        Transaction Complaint
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('complaint_status') }}"
                                        class="nav-link text-white {{ Route::currentRouteName() == 'complaint_status' ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Complaint Status
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                </ul>
            </li>

            <!-- Ledger Report -->
            {{-- <li class="nav-item">
            <a href="javascript:void(0)" class="nav-link text-white">
                <i class="bi bi-journal-text me-2"></i> Ledger Report
            </a>
        </li> --}}

            <li class="nav-item">
                <a href="{{ route('ladger.index') }}"
                    class="nav-link text-white {{ Route::currentRouteName() == 'ladger.index' ? 'sidebar-active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i>
                    Ledger Report
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="nav-link btn btn-link text-white w-100 text-start d-flex align-items-center gap-2 px-3"
                        style="background-color: #e76666;">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    @elseif($role == 3)
        <ul class="nav nav-pills flex-column mb-auto">

            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                    class="nav-link text-white {{ Route::is('dashboard') ? 'sidebar-active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>


            <li class="nav-item mt-2">
                <ul class="nav nav-pills flex-column mb-auto">

                    @php
                        $userRoute = ['enabled_services'];
                        $userActive = in_array(Route::currentRouteName(), $userRoute);
                    @endphp

                    <li class="nav-item">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $userActive ? '' : 'collapsed' }} {{ $userActive ? 'sidebar-active' : '' }}"
                            data-bs-toggle="collapse" href="#userManagement" role="button"
                            aria-expanded="{{ $userActive ? 'true' : 'false' }}" aria-controls="userManagement">
                            <span><i class="bi bi-people me-2"></i> Service</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>

                        <div class="collapse {{ $userActive ? 'show' : '' }} ms-3" id="userManagement">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('enabled_services') }}"
                                        class="nav-link text-white {{ Route::currentRouteName() == 'enabled_services' ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-clipboard-check me-2"></i>
                                        Services
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </li>

            <!-- Transaction -->
            <li class="nav-item mt-2">
                <ul class="nav nav-pills flex-column mb-auto">

                    @php
                        $transactionRoute = ['reports/recharge', 'reports/banking', 'reports/utility', 'reports'];
                        $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);
                    @endphp

                    <li class="nav-item">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $transactionActive ? '' : 'collapsed' }} {{ $transactionActive ? 'sidebar-active' : '' }}"
                            data-bs-toggle="collapse" href="#transaction" role="button"
                            aria-expanded="{{ $transactionActive ? 'true' : 'false' }}">
                            <span><i class="bi bi-receipt me-2"></i> Transaction Report</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>

                        <div class="collapse {{ $transactionActive ? 'show' : '' }} ms-3" id="transaction">
                            <ul class="nav flex-column">

                                <li class="nav-item">
                                    <a href="{{ url('reports/recharge') }}"
                                        class="nav-link text-white {{ request()->is('reports/recharge') ? 'sidebar-active' : '' }}">
                                        <i class="bi bi-phone me-2"></i>
                                        Recharge
                                    </a>
                                </li>

                                <!-- <li class="nav-item">
                                <a href="{{ url('reports/banking') }}"
                                    class="nav-link text-white {{ request()->is('reports/banking') ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-bank me-2"></i>
                                    Banking
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('reports/utility') }}"
                                    class="nav-link text-white {{ request()->is('reports/utility') ? 'sidebar-active' : '' }}">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    Utility
                                </a>
                            </li> -->

                            </ul>
                        </div>
                    </li>
                </ul>
            </li>
            <!--
        <li class="nav-item">
            <a href="{{ route('reseller_reports') }}"
                class="nav-link text-white {{ Route::is('reseller_reports') ? 'sidebar-active' : '' }}">
                <i class="bi bi-file-earmark-text me-2"></i> Reports
            </a>
        </li> -->


            <!-- Logout -->
            <li class="nav-item">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="nav-link btn btn-link text-white w-100 text-start d-flex align-items-center gap-2 px-3"
                        style="background-color: #e76666;">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    @endif

</nav>
