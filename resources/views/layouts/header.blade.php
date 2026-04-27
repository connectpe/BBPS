<header class="bg-white shadow-sm p-3 d-flex justify-content-between align-items-center border">
    <div class="d-flex align-items-center">

        @php
            $role = Auth::user()->role_id;
        @endphp

        @if (in_array($role, [2, 3]))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModall" title="Services">
                <i class="bi bi-gear-fill fs-6"></i>
            </button>
        @endif

        <button class="btn btn-sm btn-danger ms-2" id="clearCacheBtn">
            Clear Cache
        </button>
    </div>

    <div class="d-flex align-items-center gap-3">

        {{-- Wallet --}}
        @if ($role == 1)
            <div class="text-end">
                <small class="text-muted">Main Wallet</small>
                <div class="fw-semibold text-success">₹ {{ number_format(0, 2) }}</div>
            </div>

            <div class="text-end">
                <small class="text-muted">Business Wallet</small>
                <div class="fw-semibold text-primary">₹ {{ number_format($businessWallet ?? 0, 2) }}</div>
            </div>
        @else
            <div class="text-end">
                <small class="text-muted">Business Wallet</small>
                <div class="fw-semibold text-success">₹ {{ number_format($businessWallet ?? 0, 2) }}</div>
            </div>
        @endif

        {{-- Notification Icon --}}
        <div class="position-relative cursor-pointer">
            <i class="bi bi-bell fs-4"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                4
            </span>
        </div>

        {{-- Profile Dropdown --}}
        <div class="dropdown">
            <button
                class="btn bg-light border rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                data-bs-toggle="dropdown" style="width: 34px; height: 34px;">
                <i class="bi bi-person-circle fs-4 text-primary"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                <li>
                    <a class="dropdown-item" href="{{ route('admin_profile', auth::id()) }}">
                        <i class="bi bi-person me-2"></i> My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="bi bi-wallet2 me-2"></i> Wallet Statement
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="bi bi-credit-card me-2"></i> AEPS Statement
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
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
                            $isAdmin = auth()->check() && auth()->user()->role_id == 1;
                        @endphp


                        @foreach ($services as $service)
                            @php
                                $request = $requestedServices->get($service->id);
                            @endphp

                            <tr>
                                <td>{{ $service->service_name }}</td>


                                @php
                                    $userKycStatus = false;
                                    if (auth()->check()) {
                                        $business = \App\Models\BusinessInfo::where('user_id', auth()->id())->first();
                                        $userKycStatus = $business && $business->is_kyc == 1;
                                    }
                                    $isAdmin = auth()->check() && auth()->user()->role_id == 1;
                                @endphp
                                <td>
                                    @if ($userKycStatus || $isAdmin)
                                        @if ($request && $request->status === 'approved')
                                            <button class="btn btn-success btn-sm w-100">Activated</button>
                                        @elseif ($request && $request->status === 'pending')
                                            <button class="btn btn-secondary btn-sm w-100">Requested</button>
                                        @else
                                            <form action="{{ route('service.request') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                                <button class="btn btn-primary btn-sm w-100">Raise Request</button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('admin_profile', ['user_id' => Auth::user()->id, 'is_kyc' => 'Yes']) }}"
                                            class="text-danger small text-decoration-none">Complete Profile</a>
                                    @endif
                                </td>

                                {{-- <td>
                                @if ($request)
                                @if ($request->status === 'approved')
                                <button class="btn btn-success btn-sm w-100">
                                    Activated
                                </button>
                                @elseif ($request->status === 'pending')
                                @if ($isAdmin)
                                @else
                                <button class="btn btn-secondary btn-sm w-100">
                                    Requested
                                </button>
                                @endif
                                @endif
                                @else
                                @if ($isAdmin)
                                <span class="text-muted">Not Requested</span>
                                @else
                                <form action="{{ route('service.request') }}"
                            method="POST"
                            class="raise-request-form">
                            @csrf
                            <input type="hidden" name="service_id"
                                value="{{ $service->id }}">
                            <button class="btn btn-primary btn-sm w-100">
                                Raise Request
                            </button>
                            </form>
                            @endif

                            @endif
                            </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
