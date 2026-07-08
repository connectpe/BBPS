<style>
    .sidebar {
        width: 260px;
        height: 100vh;
        /* change this */
        background: linear-gradient(180deg, #445db8, #667eea);
        color: #fff;
        transition: all 0.3s ease;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        overflow-x: hidden;
        overflow-y: auto;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.15);
    }

    .sidebar.collapsed {
        width: 140px;
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.35);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.55);
    }

    /* .sidebar-menu li {
    list-style: none;
} */

    .sidebar-header {
        /* text-align: center; */
        padding: 20px 10px;
        position: relative;
        padding-bottom: 0px;
    }

    .sidebar-logo {
        width: 175px;
        transition: all 0.3s ease;
    }

    .sidebar.collapsed .sidebar-logo {
        width: 75px;
    }

    .toggle-btn {
        position: absolute;
        top: 18px;
        right: 15px;
        border: none;
        background: transparent;
        color: #fff;
        font-size: 26px;
        cursor: pointer;
        z-index: 1100;
        padding: 0;
        box-shadow: none;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 32px;
        height: 32px;
        line-height: 1;
    }

    .toggle-btn i {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-menu {
        padding: 10px;
    }

    .sidebar-menu .nav-link {
        color: #fff;
        border-radius: 10px;
        margin-bottom: 8px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .sidebar-menu .nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .menu-text {
        white-space: nowrap;
    }

    .submenu-arrow {
        margin-left: auto;
    }

    .submenu .nav-link {
        padding-left: 35px;
        font-size: 14px;
    }

    /* Desktop collapse */
    .sidebar.collapsed .nav-link {
        flex-direction: column;
        text-align: center;
        gap: 4px;
        padding: 12px 5px;
    }

    .sidebar.collapsed .menu-text {
        font-size: 11px;
    }

    .sidebar.collapsed .submenu-arrow {
        display: none;
    }

    .sidebar.collapsed .submenu {
        /* display: none !important; */
    }

    /* Mobile */
    @media(max-width: 991px) {
        .sidebar {
            left: -260px;
            width: 260px;
        }

        .sidebar.show {
            left: 0;
        }

        .sidebar.collapsed {
            width: 260px;
        }

        .sidebar.collapsed .nav-link {
            flex-direction: row;
            text-align: left;
            gap: 12px;
        }

        .sidebar.collapsed .menu-text {
            font-size: 14px;
        }

        .sidebar.collapsed .submenu-arrow {
            display: block;
        }

        .toggle-btn {
            position: fixed;
            top: -5px;
            left: 289px;
            /* left: auto; */
            color: blue !important;
        }
    }

    .main-wrapper {
        margin-left: 260px;
        width: calc(100% - 260px);
        transition: all 0.3s ease;
    }

    /* Sidebar collapsed */
    .sidebar.collapsed~.main-wrapper {
        margin-left: 140px;
        width: calc(100% - 95px);
    }

    .sidebar-menu li {
        list-style: none;
    }

    /* Mobile */
    @media(max-width: 991px) {
        .main-wrapper {
            margin-left: 0;
            width: 100%;
        }
    }

    /* Active menu */
    .sidebar-menu .nav-link.active {
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
        font-weight: 600;
        border-left: 4px solid #fff;
    }

    /* Active submenu */
    .submenu .nav-link.active {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        border-left: 4px solid #ffd54f;
        padding-left: 31px;
    }
</style>

<nav class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <img src="{{ asset('assets/image/Logo/sidebar-logo.png') }}" class="sidebar-logo">

        <button class="toggle-btn" id="toggleBtn">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="sidebar-menu">

        {{-- <a href="#" class="nav-link">
            <i class="bi bi-speedometer2"></i>
            <span class="menu-text">Dashboard</span>
        </a> --}}

        @php
            $role = Auth::user()->role_id;
        @endphp
        @if ($role == 1)
            <ul class="sidebar-menu">

                {{-- Dashboard --}}
                <li>
                    <a href="{{ route('dashboard') }}" class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @php
                    $serviceRequestCount = App\Models\UserService::where('status', 'pending')->count();

                    $userRoute = [
                        'users',
                        'associates',
                        'view_user',
                        'request_services',
                        'support_details',
                        'load_money_request',
                    ];
                    $userActive = in_array(Route::currentRouteName(), $userRoute);

                    $transactionRoute = [
                        'reports/recharge',
                        'reports/utility',
                        'reports/banking',
                        'reports',
                        'payout_transaction',
                    ];
                    $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);

                    $reportRoute = ['complain.report', 'api_log'];
                    $reportActive = in_array(Route::currentRouteName(), $reportRoute);

                    $upiRoute = [
                        'upi_initiation',
                        'upi_collection',
                        'all_upi_transactions',
                        'api_callback',
                        'upi_manual_settlement',
                    ];
                    $upiActive = in_array(Route::currentRouteName(), $upiRoute);

                    $masterRoute = [
                        'our_servicess',
                        'providers',
                        'categories.index',
                        'defaultslug',
                        'business_category',
                    ];
                    $masterActive = in_array(Route::currentRouteName(), $masterRoute);

                    $apiRoute = ['payin_docs'];
                    $apiActive = in_array(Route::currentRouteName(), $apiRoute);

                    $settingsRoute = ['users_log'];
                    $settingsActive = in_array(Route::currentRouteName(), $settingsRoute);

                    $documentVerificationRoute = ['bank_account', 'pan_verification', 'gstin_verification'];
                    $documentVerificationActive = in_array(Route::currentRouteName(), $documentVerificationRoute);
                @endphp


                {{-- User Management --}}
                <li>
                    <a class="nav-link {{ $userActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#userManagement">
                        <i class="bi bi-people"></i>
                        <span class="menu-text">User Manager</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $userActive ? 'show' : '' }}" id="userManagement">
                        <a href="{{ route('users') }}"
                            class="nav-link {{ Route::currentRouteName() == 'users' ? 'active' : '' }}"><i
                                class="bi bi-person-lines-fill"></i><span class="menu-text">Users</span></a>
                        <a href="{{ route('associates') }}"
                            class="nav-link {{ Route::currentRouteName() == 'associates' ? 'active' : '' }}"><i
                                class="fa-solid fa-handshake"></i><span class="menu-text">Associated Partners</span></a>

                        <a href="{{ route('request_services') }}"
                            class="nav-link d-flex justify-content-between align-items-center {{ Route::currentRouteName() == 'request_services' ? 'active' : '' }}">
                            <span><i class="bi bi-clipboard-check"></i> Service Requests</span>
                            @if ($serviceRequestCount)
                                <span class="badge bg-light text-dark">{{ $serviceRequestCount }}</span>
                            @endif
                        </a>

                        <a href="{{ route('support_details') }}"
                            class="nav-link {{ Route::currentRouteName() == 'support_details' ? 'active' : '' }}"><i
                                class="bi bi-person-plus-fill"></i><span class="menu-text">Support User</span></a>
                        <a href="{{ route('load_money_request') }}"
                            class="nav-link {{ Route::currentRouteName() == 'load_money_request' ? 'active' : '' }}"><i
                                class="bi bi-wallet2"></i><span class="menu-text">Load Money</span></a>
                    </div>
                </li>


                {{-- Transactions --}}
                <li>
                    <a class="nav-link {{ $transactionActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#transactionManagement">
                        <i class="bi bi-cash-stack"></i>
                        <span class="menu-text">Transactions</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $transactionActive ? 'show' : '' }}" id="transactionManagement">
                        <a href="{{ url('reports/recharge') }}"
                            class="nav-link {{ Route::currentRouteName() == 'recharge' ? 'active' : '' }}"><i
                                class="bi bi-phone"></i><span class="menu-text">Recharge</span></a>
                        <a href="{{ url('reports/banking') }}"
                            class="nav-link {{ Route::currentRouteName() == 'banking_service' ? 'active' : '' }}"><i
                                class="bi bi-bank"></i><span class="menu-text">Banking</span></a>
                        <a href="{{ url('reports/utility') }}"
                            class="nav-link {{ Route::currentRouteName() == 'utility_service' ? 'active' : '' }}"><i
                                class="bi bi-lightning-charge"></i><span class="menu-text">Utility</span></a>
                        <a href="{{ route('payout_transaction') }}"
                            class="nav-link {{ Route::currentRouteName() == 'payout_transaction' ? 'active' : '' }}"><i
                                class="bi bi-arrow-left-right"></i><span class="menu-text">Payout Transaction</span></a>
                    </div>
                </li>


                {{-- Reports --}}
                <li>
                    <a class="nav-link {{ $reportActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#reportRoute">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Reports</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $reportActive ? 'show' : '' }}" id="reportRoute">
                        <a href="{{ route('complain.report') }}"
                            class="nav-link {{ Route::currentRouteName() == 'complain.report' ? 'active' : '' }}"><i
                                class="bi bi-file-earmark-text"></i><span class="menu-text">Complaint</span></a>
                        <a href="{{ route('api_log') }}"
                            class="nav-link {{ Route::currentRouteName() == 'api_log' ? 'active' : '' }}"><i
                                class="bi bi-journal-text"></i><span class="menu-text">API Log</span></a>
                    </div>
                </li>


                {{-- UPI Services --}}
                <li>
                    <a class="nav-link {{ $upiActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#upiServices">
                        <i class="bi bi-phone"></i>
                        <span class="menu-text">UPI Services</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $upiActive ? 'show' : '' }}" id="upiServices">
                        <a href="{{ route('upi_initiation') }}"
                            class="nav-link {{ Route::currentRouteName() == 'upi_initiation' ? 'active' : '' }}"><i
                                class="bi bi-arrow-up-right-circle"></i><span class="menu-text">UPI
                                Initiation</span></a>
                        <a href="{{ route('upi_collection') }}"
                            class="nav-link {{ Route::currentRouteName() == 'upi_collection' ? 'active' : '' }}"><i
                                class="bi bi-plus-circle"></i><span class="menu-text">UPI Collection</span></a>
                        <a href="{{ route('all_upi_transactions') }}"
                            class="nav-link {{ Route::currentRouteName() == 'all_upi_transactions' ? 'active' : '' }}"><i
                                class="bi bi-list-check"></i><span class="menu-text">All Transactions</span></a>
                        <a href="{{ route('upi_callback') }}"
                            class="nav-link {{ Route::currentRouteName() == 'upi_callback' ? 'active' : '' }}"><i
                                class="bi bi-arrow-repeat"></i><span class="menu-text">UPI Callback</span></a>
                        <a href="{{ route('upi_manual_settlement') }}"
                            class="nav-link {{ Route::currentRouteName() == 'upi_manual_settlement' ? 'active' : '' }}"><i
                                class="bi bi-wallet2"></i><span class="menu-text">Manual Settlement</span></a>
                    </div>
                </li>


                {{-- Master Settings --}}
                <li>
                    <a class="nav-link {{ $masterActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#masterRoute">
                        <i class="bi bi-gear"></i>
                        <span class="menu-text">Master Setting</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $masterActive ? 'show' : '' }}" id="masterRoute">
                        <a href="{{ route('our_servicess') }}"
                            class="nav-link {{ Route::currentRouteName() == 'our_servicess' ? 'active' : '' }}"><i
                                class="bi bi-briefcase"></i><span class="menu-text">Our Services</span></a>
                        <a href="{{ route('providers') }}"
                            class="nav-link {{ Route::currentRouteName() == 'providers' ? 'active' : '' }}"><i
                                class="bi bi-person-badge"></i><span class="menu-text">Providers</span></a>
                        <a href="{{ route('categories.index') }}"
                            class="nav-link {{ Route::currentRouteName() == 'categories.index' ? 'active' : '' }}"><i
                                class="bi bi-tags"></i><span class="menu-text">Categories</span></a>
                        <a href="{{ route('defaultslug') }}"
                            class="nav-link {{ Route::currentRouteName() == 'defaultslug' ? 'active' : '' }}"><i
                                class="bi bi-link-45deg"></i><span class="menu-text">Default Provider</span></a>
                        <a href="{{ route('business_category') }}"
                            class="nav-link {{ Route::currentRouteName() == 'business_category' ? 'active' : '' }}"><i
                                class="bi bi-grid"></i><span class="menu-text">Business Category</span></a>
                    </div>
                </li>


                {{-- Single Links --}}
                <li><a href="{{ route('schemes.index') }}"
                        class="nav-link {{ Route::currentRouteName() == 'schemes.index' ? 'active' : '' }}"><i
                            class="bi bi-diagram-3"></i><span class="menu-text">Schemes</span></a></li>
                <li><a href="{{ route('user_assign_to_support') }}"
                        class="nav-link {{ Route::currentRouteName() == 'user_assign_to_support' ? 'active' : '' }}"><i
                            class="bi bi-person-check"></i><span class="menu-text">Assign User</span></a></li>
                <li><a href="{{ route('nsdl-payment') }}"
                        class="nav-link {{ Route::currentRouteName() == 'nsdl-payment' ? 'active' : '' }}"><i
                            class="bi bi-receipt"></i><span class="menu-text">NSDL Payment</span></a></li>
                <li><a href="{{ route('ladger.index') }}"
                        class="nav-link {{ Route::currentRouteName() == 'ladger.index' ? 'active' : '' }}"><i
                            class="bi bi-journal-text"></i><span class="menu-text">Ledger</span></a></li>
                <li><a href="{{ route('add_agreement') }}"
                        class="nav-link {{ Route::currentRouteName() == 'add_agreement' ? 'active' : '' }}"><i
                            class="bi bi-file-earmark-arrow-up"></i><span class="menu-text">Documents
                            Upload</span></a></li>
                <li><a href="{{ route('maintenance_mode') }}"
                        class="nav-link {{ Route::currentRouteName() == 'maintenance_mode' ? 'active' : '' }}"><i
                            class="bi bi-tools"></i><span class="menu-text">Maintenance Mode</span></a></li>
                {{-- API Documentation --}}
                <li>
                    <a class="nav-link {{ $apiActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#apiDocsAdmin">
                        <i class="bi bi-code-slash"></i>
                        <span class="menu-text">API Documentation</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $apiActive ? 'show' : '' }}" id="apiDocsAdmin">
                        <a href="{{ route('payin_docs') }}"
                            class="nav-link {{ Route::currentRouteName() == 'payin_docs' ? 'active' : '' }}">
                            <i class="bi bi-wallet-fill"></i>
                            <span class="menu-text">Payin</span>
                        </a>
                    </div>
                </li>

                {{-- Document Verification --}}
                <li>
                    <a class="nav-link {{ $documentVerificationActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#documentVerificationMenu">
                        <i class="bi bi-shield-check"></i>
                        <span class="menu-text">Document Verification</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $documentVerificationActive ? 'show' : '' }}"
                        id="documentVerificationMenu">
                        <a href="{{ route('bank_account') }}"
                            class="nav-link {{ Route::currentRouteName() == 'bank_account' ? 'active' : '' }}">
                            <i class="bi bi-bank"></i>
                            <span class="menu-text">Bank Account</span>
                        </a>
                        <a
                            href="{{ route('pan_verification') }}"class="nav-link {{ Route::currentRouteName() == 'pan_verification' ? 'active' : '' }}">
                            <i class="bi bi-person-vcard"></i>
                            <span class="menu-text">PAN Verification</span>
                        </a>
                        <a href="{{ route('gstin_verification') }}"
                            class="nav-link {{ Route::currentRouteName() == 'gstin_verification' ? 'active' : '' }}">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span class="menu-text">GSTIN Verification</span>
                        </a>
                    </div>
                </li>

                {{-- Settings --}}
                <li>
                    <a class="nav-link {{ $settingsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#settingsMenu">
                        <i class="bi bi-gear"></i>
                        <span class="menu-text">Settings</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $settingsActive ? 'show' : '' }}" id="settingsMenu">
                        <a href="{{ route('users_log') }}"
                            class="nav-link {{ Route::currentRouteName() == 'users_log' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-clock"></i>
                            <span class="menu-text">Users Log</span>
                        </a>
                    </div>
                </li>

                {{-- Logout --}}
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="menu-text">Logout</span>
                        </button>
                    </form>
                </li>

            </ul>
        @elseif($role == 2)
            <ul class="sidebar-menu">

                {{-- Dashboard --}}
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @php
                    $serviceRoute = ['utility_service', 'recharge_service', 'banking_service'];
                    $servicesActive = in_array(Route::currentRouteName(), $serviceRoute);

                    $transactionRoute = [
                        'transaction_status',
                        'transaction_complaint',
                        'complaint_status',
                        'payout_transaction',
                    ];
                    $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);

                    $upiRoute = ['upi_initiation', 'upi_collection', 'all_upi_transactions'];
                    $upiActive = in_array(Route::currentRouteName(), $upiRoute);

                    $apiRoute = ['payin_docs_user'];
                    $apiActive = in_array(Route::currentRouteName(), $apiRoute);
                @endphp

                {{-- Services --}}
                {{-- <li>
        <a class="nav-link {{ $servicesActive ? 'active' : '' }}"
            data-bs-toggle="collapse" href="#servicesMenu">
            <i class="bi bi-gear-fill"></i>
            <span class="menu-text">Services</span>
            <i class="bi bi-chevron-down submenu-arrow"></i>
        </a>

        <div class="collapse submenu {{ $servicesActive ? 'show' : '' }}" id="servicesMenu">
            <a href="{{ route('banking_service') }}"
                class="nav-link {{ Route::currentRouteName() == 'banking_service' ? 'active' : '' }}">
                <i class="bi bi-bank"></i>
                <span class="menu-text">Banking Services</span>
            </a>

            <a href="{{ route('utility_service') }}"
                class="nav-link {{ Route::currentRouteName() == 'utility_service' ? 'active' : '' }}">
                <i class="bi bi-lightning-charge"></i>
                <span class="menu-text">Utility Services</span>
            </a>

            <a href="{{ route('recharge_service') }}"
                class="nav-link {{ Route::currentRouteName() == 'recharge_service' ? 'active' : '' }}">
                <i class="bi bi-phone"></i>
                <span class="menu-text">Recharge Services</span>
            </a>
        </div>
    </li> --}}

                <li>
                    <a href="{{ route('recharge_service') }}"
                        class="nav-link {{ Route::currentRouteName() == 'recharge_service' ? 'active' : '' }}">
                        <i class="bi bi-gear-fill"></i>
                        <span class="menu-text">Services</span>
                    </a>
                </li>

                {{-- AEPS Services --}}

                @php
                    $aepsRoute = ['aeps.services'];
                    $aepsActive = in_array(Route::currentRouteName(), $aepsRoute);
                @endphp
                <li>
                    <a class="nav-link {{ $aepsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#aepsMenu">
                        <i class="bi bi-fingerprint"></i>
                        <span class="menu-text">AEPS</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $aepsActive ? 'show' : '' }}" id="aepsMenu">
                        <a href="{{ route('aeps.services') }}"
                            class="nav-link {{ Route::currentRouteName() == 'aeps.services' ? 'active' : '' }}">
                            <i class="bi bi-grid"></i>
                            <span class="menu-text">AEPS Services</span>
                        </a>
                    </div>
                </li>

                {{-- Transaction Report --}}
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

                    $transactionActive =
                        in_array(Route::currentRouteName(), $transactionRoute) ||
                        request()->is('reports/recharge') ||
                        request()->is('reports/banking') ||
                        request()->is('reports/utility');
                @endphp

                <li>
                    <a class="nav-link {{ $transactionActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#transactionMenu">
                        <i class="bi bi-receipt"></i>
                        <span class="menu-text">Report</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $transactionActive ? 'show' : '' }}" id="transactionMenu">

                        <a href="{{ url('reports/recharge') }}"
                            class="nav-link {{ request()->is('reports/recharge') ? 'active' : '' }}">
                            <i class="bi bi-phone"></i>
                            <span class="menu-text">Recharge</span>
                        </a>

                        <a href="{{ url('reports/banking') }}"
                            class="nav-link {{ request()->is('reports/banking') ? 'active' : '' }}">
                            <i class="bi bi-bank"></i>
                            <span class="menu-text">Banking</span>
                        </a>

                        <a href="{{ url('reports/utility') }}"
                            class="nav-link {{ request()->is('reports/utility') ? 'active' : '' }}">
                            <i class="bi bi-lightning-charge"></i>
                            <span class="menu-text">Utility</span>
                        </a>

                        <a href="{{ route('transaction_status') }}"
                            class="nav-link {{ Route::currentRouteName() == 'transaction_status' ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i>
                            <span class="menu-text">Transaction Status</span>
                        </a>

                        <a href="{{ route('transaction_complaint') }}"
                            class="nav-link {{ Route::currentRouteName() == 'transaction_complaint' ? 'active' : '' }}">
                            <i class="bi bi-exclamation-octagon"></i>
                            <span class="menu-text">Transaction Complaint</span>
                        </a>

                        <a href="{{ route('complaint_status') }}"
                            class="nav-link {{ Route::currentRouteName() == 'complaint_status' ? 'active' : '' }}">
                            <i class="bi bi-info-circle"></i>
                            <span class="menu-text">Complaint Status</span>
                        </a>
                    </div>
                </li>

                {{-- UPI Services --}}
                <li>
                    <a class="nav-link {{ $upiActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#upiMenu">
                        <i class="bi bi-phone"></i>
                        <span class="menu-text">UPI Services</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $upiActive ? 'show' : '' }}" id="upiMenu">
                        <a href="{{ route('upi_initiation') }}"
                            class="nav-link {{ Route::currentRouteName() == 'upi_initiation' ? 'active' : '' }}">
                            <i class="bi bi-arrow-up-right-circle"></i>
                            <span class="menu-text">UPI Initiation</span>
                        </a>

                        <a href="{{ route('upi_collection') }}"
                            class="nav-link {{ Route::currentRouteName() == 'upi_collection' ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i>
                            <span class="menu-text">UPI Collection</span>
                        </a>

                        <a href="{{ route('all_upi_transactions') }}"
                            class="nav-link {{ Route::currentRouteName() == 'all_upi_transactions' ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i>
                            <span class="menu-text">All UPI Transactions</span>
                        </a>
                    </div>
                </li>

                {{-- Single Links --}}
                <li>
                    <a href="{{ route('payout_transaction') }}"
                        class="nav-link {{ Route::currentRouteName() == 'payout_transaction' ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i>
                        <span class="menu-text">Payout Transaction</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('user_load_money_request') }}"
                        class="nav-link {{ Route::currentRouteName() == 'user_load_money_request' ? 'active' : '' }}">
                        <i class="bi bi-wallet2"></i>
                        <span class="menu-text">Load Money Request</span>
                    </a>
                </li>

                {{-- API Documentation --}}
                <li>
                    <a class="nav-link {{ $apiActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#apiMenu">
                        <i class="bi bi-code-slash"></i>
                        <span class="menu-text">API Documentation</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $apiActive ? 'show' : '' }}" id="apiMenu">
                        <a href="{{ route('payin_docs_user') }}"
                            class="nav-link {{ Route::currentRouteName() == 'payin_docs_user' ? 'active' : '' }}">
                            <i class="bi bi-wallet-fill"></i>
                            <span class="menu-text">Payin</span>
                        </a>
                    </div>
                </li>

                <li>
                    <a href="{{ route('ladger.index') }}"
                        class="nav-link {{ Route::currentRouteName() == 'ladger.index' ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i>
                        <span class="menu-text">Ledger Report</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('all_agreements') }}"
                        class="nav-link {{ Route::currentRouteName() == 'all_agreements' ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Documents</span>
                    </a>
                </li>

                {{-- Logout --}}
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="menu-text">Logout</span>
                        </button>
                    </form>
                </li>

            </ul>
        @elseif($role == 3)
            <ul class="sidebar-menu">

                {{-- Dashboard --}}
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @php
                    $userRoute = ['enabled_services', 'api_partner_services'];
                    $userActive = in_array(Route::currentRouteName(), $userRoute);

                    $transactionRoute = ['reports', 'reports/recharge', 'reports/banking', 'reports/utility'];
                    $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);
                @endphp

                {{-- Services --}}
                <li>
                    <a class="nav-link {{ $userActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#userManagement">
                        <i class="bi bi-people"></i>
                        <span class="menu-text">Service</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $userActive ? 'show' : '' }}" id="userManagement">
                        <a href="{{ route('enabled_services') }}"
                            class="nav-link {{ Route::currentRouteName() == 'enabled_services' ? 'active' : '' }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span class="menu-text">Services</span>
                        </a>

                        <a href="{{ route('api_partner_services') }}"
                            class="nav-link {{ Route::currentRouteName() == 'api_partner_services' ? 'active' : '' }}">
                            <i class="bi bi-hdd-network"></i>
                            <span class="menu-text">API Partner Services</span>
                        </a>
                    </div>
                </li>

                {{-- Payout Transaction --}}
                <li>
                    <a href="{{ route('payout_transaction') }}"
                        class="nav-link {{ Route::currentRouteName() == 'payout_transaction' ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i>
                        <span class="menu-text">Payout Transaction</span>
                    </a>
                </li>

                {{-- Transaction Report --}}
                <li>
                    <a class="nav-link {{ $transactionActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#transactionMenu">
                        <i class="bi bi-receipt"></i>
                        <span class="menu-text">Transaction Report</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $transactionActive ? 'show' : '' }}" id="transactionMenu">
                        <a href="{{ route('reports', ['type' => 'recharge']) }}"
                            class="nav-link {{ request()->is('reports/recharge') ? 'active' : '' }}">
                            <i class="bi bi-phone"></i>
                            <span class="menu-text">Recharge</span>
                        </a>

                        {{-- Future Reports --}}
                        {{--
            <a href="{{ route('reports', ['type' => 'banking']) }}" class="nav-link">
                <i class="bi bi-bank"></i>
                <span class="menu-text">Banking</span>

            </a>

            <a href="{{ route('reports', ['type' => 'utility']) }}" class="nav-link">
                <i class="bi bi-lightning-charge"></i>
                <span class="menu-text">Utility</span>
            </a>
            --}}
                    </div>
                </li>

                {{-- Agreement --}}
                <li>
                    <a href="{{ route('all_agreements') }}"
                        class="nav-link {{ Route::currentRouteName() == 'all_agreements' ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Agreement</span>
                    </a>
                </li>

                {{-- Logout --}}
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="menu-text">Logout</span>
                        </button>
                    </form>
                </li>

            </ul>
        @elseif($role == 4)
            <ul class="sidebar-menu">

                {{-- Dashboard --}}
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                {{-- Users List --}}
                <li>
                    <a href="{{ route('support_userlist') }}"
                        class="nav-link {{ Route::is('support_userlist') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span class="menu-text">Users List</span>
                    </a>
                </li>

                {{-- Complaints Report --}}
                <li>
                    <a href="{{ route('complain.report') }}"
                        class="nav-link {{ Route::is('complain.report') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-data"></i>
                        <span class="menu-text">Complaints Report</span>
                    </a>
                </li>

                {{-- Payout Transaction --}}
                <li>
                    <a href="{{ route('payout_transaction') }}"
                        class="nav-link {{ Route::currentRouteName() == 'payout_transaction' ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i>
                        <span class="menu-text">Payout Transaction</span>
                    </a>
                </li>

                @php
                    $transactionRoute = ['reports/recharge', 'reports/utility', 'reports/banking', 'reports'];
                    $transactionActive = in_array(Route::currentRouteName(), $transactionRoute);
                @endphp

                {{-- Transactions --}}
                <li>
                    <a class="nav-link {{ $transactionActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#transactionManagement">
                        <i class="bi bi-cash-stack"></i>
                        <span class="menu-text">Transactions</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>

                    <div class="collapse submenu {{ $transactionActive ? 'show' : '' }}" id="transactionManagement">
                        <a href="{{ url('reports/recharge') }}"
                            class="nav-link {{ request()->is('reports/recharge') ? 'active' : '' }}">
                            <i class="bi bi-phone"></i>
                            <span class="menu-text">Recharge</span>
                        </a>

                        <a href="{{ url('reports/banking') }}"
                            class="nav-link {{ request()->is('reports/banking') ? 'active' : '' }}">
                            <i class="bi bi-bank"></i>
                            <span class="menu-text">Banking</span>
                        </a>

                        <a href="{{ url('reports/utility') }}"
                            class="nav-link {{ request()->is('reports/utility') ? 'active' : '' }}">
                            <i class="bi bi-lightning-charge"></i>
                            <span class="menu-text">Utility</span>
                        </a>
                    </div>
                </li>

                {{-- Agreement --}}
                <li>
                    <a href="{{ route('all_agreements') }}"
                        class="nav-link {{ Route::currentRouteName() == 'all_agreements' ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Agreement</span>
                    </a>
                </li>

                {{-- Logout --}}
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="menu-text">Logout</span>
                        </button>
                    </form>
                </li>

            </ul>
        @endif
    </div>
</nav>

{{-- <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', function () {
        if (window.innerWidth <= 991) {
            sidebar.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    });
</script> --}}
<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    const toggleIcon = toggleBtn.querySelector('i');

    toggleBtn.addEventListener('click', function() {

        if (window.innerWidth <= 991) {
            sidebar.classList.toggle('show');

            if (sidebar.classList.contains('show')) {
                toggleIcon.classList.remove('bi-list');
                toggleIcon.classList.add('bi-x');
            } else {
                toggleIcon.classList.remove('bi-x');
                toggleIcon.classList.add('bi-list');
            }

        } else {
            sidebar.classList.toggle('collapsed');

            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('bi-list');
                toggleIcon.classList.add('bi-x');
            } else {
                toggleIcon.classList.remove('bi-x');
                toggleIcon.classList.add('bi-list');
            }
        }
    });
</script>
