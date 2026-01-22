<header class="bg-white shadow-sm p-3 d-flex justify-content-between align-items-center border">
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


            <button class="btn btn-primary mb-2 ms-5" data-bs-toggle="modal" data-bs-target="#serviceModall">
                ADD
            </button>



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

                <li><a class="dropdown-item" href="{{ route('admin_profile') }}">My Profile</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)">Wallet Statement</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)">AEPS Statement</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<div class="modal fade" id="serviceModall" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-top">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Available Services</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $isAdmin = auth()->user()->status == '1';
                        @endphp

                        @foreach ($services as $service)
                            @php
                                $request = $requestedServices->get($service->id);
                            @endphp

                            <tr>
                                <td>{{ $service->service_name }}</td>

                                <td>
                                    {{-- REQUEST EXISTS --}}
                                    @if ($request)
                                        {{-- APPROVED --}}
                                        @if ($request->status === 'approved')
                                            <button class="btn btn-success btn-sm w-100">
                                                Activated
                                            </button>

                                            {{-- PENDING --}}
                                        @elseif ($request->status === 'pending')
                                            @if ($isAdmin)
                                                <form action="{{ route('service.approve', $request->id) }}"
                                                    method="POST" class="approve-request-form">
                                                    @csrf
                                                    <button class="btn btn-warning btn-sm w-100">
                                                        Requested
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-secondary btn-sm w-100" disabled>
                                                    Requested
                                                </button>
                                            @endif
                                        @endif

                                        {{-- NO REQUEST --}}
                                    @else
                                        @if ($isAdmin)
                                            <span class="text-muted">Not Requested</span>
                                        @else
                                            <form action="{{ route('service.request') }}" method="POST" class="raise-request-form">
                                                @csrf
                                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                                <button class="btn btn-primary btn-sm w-100">
                                                    Raise Request
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<script>
/* USER → Raise Request */
document.querySelectorAll('.raise-request-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to raise this service request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success', 'Service request raised successfully!', 'success');
                        // Optionally, update the UI here, e.g., change button to 'Requested'
                        const button = form.querySelector('button');
                        button.textContent = 'Requested';
                        button.className = 'btn btn-secondary btn-sm w-100';
                        button.disabled = true;
                        form.removeEventListener('submit', arguments.callee); // Remove listener
                    } else {
                        Swal.fire('Error', data.message || 'Failed to raise request', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Network error occurred', 'error');
                });
            }
        });
    });
});

/* ADMIN → Approve Request */
document.querySelectorAll('.approve-request-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Confirm Activation',
            text: 'Do you want to activate this service?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Activate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
