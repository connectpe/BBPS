<header class="bg-white shadow-sm p-3 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <!-- Sidebar Toggle -->
        <!-- <button id="sidebarToggle" class="btn btn-outline-secondary me-3">
            <i class="bi bi-list"></i>
        </button> -->

        <button id="sidebarToggle" class="btn btn-outline-secondary me-3 d-md-none">
            <i class="bi bi-list"></i>
        </button>


        <h2 class="mb-4">@yield('page-title')</h1>
            <!-- <h6 class="mb-0">Dashboard</h6> -->
    </div>

    <div class="d-flex align-items-center gap-3">

        <!-- Wallet Balance -->
        <div class="text-end">
            <small class="text-muted">Wallet</small>
            <div class="fw-semibold text-success">₹ {{ number_format(12500, 2) }}</div>
        </div>

        <!-- AEPS Balance -->
        <div class="text-end">
            <small class="text-muted">AEPS</small>
            <div class="fw-semibold text-primary">₹ {{ number_format(8420, 2) }}</div>
        </div>

        <!-- Notification + Profile -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center"
                data-bs-toggle="dropdown">
                <i class="bi bi-bell me-2 position-relative">
                    <span class="position-absolute top-0 start-100 translate-middle badge bg-danger">
                        4
                    </span>
                </i>
                <i class="bi bi-person-circle"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-header">Notifications</li>
                <li><a class="dropdown-item" href="javascript:void(0)">BBPS Bill Paid Successfully</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)">AEPS Settlement Completed</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>

                <li><a class="dropdown-item" href="{{route('admin_profile')}}">My Profile</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)">Wallet Statement</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)">AEPS Statement</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <form method="POST" action="{{route('admin.logout')}}">
                        @csrf
                        <button class="dropdown-item text-danger" >Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>