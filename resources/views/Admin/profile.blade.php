@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')

    <style>
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: scale(1.05);
            /* zoom 5% */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            /* bigger shadow on hover */
        }


        /* Multi Step Highlighter */
        .step-progress {
            font-size: 14px;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #6c757d;
            min-width: 80px;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid #ced4da;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            font-weight: 600;
        }

        .step-label {
            margin-top: 6px;
            white-space: nowrap;
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: #ced4da;
            margin: 0 6px;
        }

        /* ACTIVE */
        .step-item.active .step-circle {
            border-color: #3c5be4;
            background: #3c5be4;
            color: #fff;
        }

        .step-item.active .step-label {
            color: #3c5be4;
            font-weight: 600;
        }

        .error-text {
            color: red;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .is-invalid {
            border-color: red;
        }

        .profile-image {
            height: 75px;
            width: 75px;
            border-radius: 50%;
        }

        /* COMPLETED */
        /* .step-item.completed .step-circle {
                                                                                                                                                            border-color: #198754;
                                                                                                                                                            background: #198754;
                                                                                                                                                            color: #fff;
                                                                                                                                                        } */
    </style>

    @php

        use App\Facades\FileUpload;

        $user = Auth::user();
        $role = $user->role_id; // $role == 1 is Admin and $role == 2 is User.
    @endphp

    @if ($role == 1)


        <div class="row align-items-center border rounded p-2 shadow-sm">
            <!-- User Image -->
            <div class="col">
                <!-- Profile Image -->

                <img id="userImage" src="{{ FileUpload::getFilePath($user?->profile_image) }}" alt="User Image"
                    class="rounded-circle border border-1 border-primary cursor-pointer"
                    onclick="showImage(this.src,'Profile Image')" width="100" height="100" onerror="showInitials(this)">
            </div>

            <!-- User Info -->
            <div class="col">
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="mb-0 text-muted">{{ $user->email }}</p>
            </div>

        </div>

        <div class="row mt-3 g-3">

            <!-- Card 1: Completed Transaction -->

            <div class="col-md-3">
                <div class="card shadow-lg text-center p-3 card-hover">
                    <div class="card-body">
                        <i class="bi bi-check-circle-fill fs-4 text-success mb-2"></i>
                        <h6 class="card-title mb-1">Completed Transaction</h6>
                        <p class="card-text fs-6 fw-bold">{{ number_format(2345) }}</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Spent -->
            <div class="col-md-3">
                <div class="card shadow-lg text-center p-3 card-hover">
                    <div class="card-body">
                        <i class="bi bi-currency-dollar fs-4 text-primary mb-2"></i>
                        <h6 class="card-title mb-1">Total Spent</h6>
                        <p class="card-text fs-6 fw-bold">{{ number_format(1245) }}</p>
                    </div>
                </div>
            </div>

            <!-- Card 3: Wallet Balance -->
            <div class="col-md-3">
                <div class="card shadow-lg text-center p-3 card-hover">
                    <div class="card-body">
                        <i class="bi bi-wallet2 fs-4 text-warning mb-2"></i>
                        <h6 class="card-title mb-1">Wallet Balance</h6>
                        <p class="card-text fs-6 fw-bold">{{ number_format(4567) }}</p>

                    </div>
                </div>
            </div>


            <!-- Card 4: Member Since -->
            <div class="col-md-3">
                <div class="card shadow-lg text-center p-3 card-hover">
                    <div class="card-body">
                        <i class="bi bi-calendar-check fs-4 text-info mb-2"></i>
                        <h6 class="card-title mb-1">Member Since</h6>
                        <p class="card-text fs-6 fw-bold">{{ number_format(2012) }}</p>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-3">
            <div class="col-12">
                <!-- Tabs nav -->
                <ul class="nav nav-tabs" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark active" id="personal-tab" data-bs-toggle="tab"
                            data-bs-target="#personal" type="button" role="tab" aria-controls="personal"
                            aria-selected="true">Personal Information</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="security-tab" data-bs-toggle="tab"
                            data-bs-target="#security" type="button" role="tab" aria-controls="security"
                            aria-selected="false">Security Setting</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="activity-tab" data-bs-toggle="tab"
                            data-bs-target="#activity" type="button" role="tab" aria-controls="activity"
                            aria-selected="false">Activity Log</button>
                    </li>
                    {{-- <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="serviceRequest-tab" data-bs-toggle="tab"
                    data-bs-target="#serviceRequest" type="button" role="tab" aria-controls="serviceRequest"
                    aria-selected="false">Service Request</button>
            </li> --}}
                </ul>
                <!-- Tabs content -->
                <div class="tab-content p-3" id="profileTabContent">

                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                        <div class="row mb-2">
                            <div class="col-md-4 fw-semibold">Full Name:</div>
                            <div class="col-md-8">{{ $user->name }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-semibold">Email Address:</div>
                            <div class="col-md-8">{{ $user->email }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-semibold">Phone Number:</div>
                            <div class="col-md-8">{{ $user->mobile }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-semibold">Date of Birth:</div>
                            <div class="col-md-8">01-Jan-1990</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-semibold">Gender:</div>
                            <div class="col-md-8">Male</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-semibold">City:</div>
                            <div class="col-md-8">New York, USA</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-semibold">Address:</div>
                            <div class="col-md-8">123 Main Street, NY 10001</div>
                        </div>
                    </div>


                    <!-- Security Setting -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <!-- Last Login Info -->
                        <div class="row mb-3">
                            <div class="col-md-4 fw-semibold">Last Login:</div>
                            <div class="col-md-8">15-Jan-2026 10:45 AM</div>
                        </div>

                        <!-- Change Password Form -->
                        <form id="changePasswordForm">
                            @csrf

                            <div class="mb-3 row">
                                <label class="col-md-4 col-form-label fw-semibold">
                                    Old Password:<span class="text-danger">*</span>
                                </label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" name="current_password"
                                        placeholder="Current Password">
                                    <small class="text-danger error-current_password"></small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-md-4 col-form-label fw-semibold">
                                    New Password:<span class="text-danger">*</span>
                                </label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" name="new_password"
                                        placeholder="New Password">
                                    <small class="text-danger error-new_password"></small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-md-4 col-form-label fw-semibold">
                                    Confirm Password:<span class="text-danger">*</span>
                                </label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" name="new_password_confirmation"
                                        placeholder="Confirm Password">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn buttonColor">
                                        Change Password
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>


                    <!-- Activity Log -->
                    <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">01-Jan-2026 09:00 AM:</div>
                            <div class="col-md-8">Logged in from IP 192.168.1.1</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">05-Jan-2026 03:30 PM:</div>
                            <div class="col-md-8">Completed a transaction of $500</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">10-Jan-2026 11:15 AM:</div>
                            <div class="col-md-8">Updated profile information</div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="serviceRequest" role="tabpanel" aria-labelledby="serviceRequest-tab">

                        @php
                            $serviceRequests = [
                                [
                                    'serviceName' => 'Payin',
                                    'businessName' => 'Business Name 1',
                                ],
                                [
                                    'serviceName' => 'Payout',
                                    'businessName' => 'Business Name 2',
                                ],
                            ];

                            $faker = Faker\Factory::create();
                            $randomName = $faker->name;
                        @endphp
                        <div class="row mb-2">
                            @foreach ($serviceRequests as $request)
                                <div class="col-md-12 mb-2">
                                    <div class="border rounded p-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <strong>Service Name:</strong> {{ $request['serviceName'] }} <br />
                                                <strong>Business Name:</strong> {{ $request['businessName'] }}
                                                [{{ $randomName }}] <br />
                                                <strong>Date:</strong> Jan-17-2025 05:45 pm <br />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <script>
            function showInitials(img) {
                const name = "{{ $user->name }}"; // Replace dynamically from backend
                const initial = name.charAt(0).toUpperCase();

                // Create a circle div
                const div = document.createElement('div');
                div.textContent = initial;
                div.style.width = '92px';
                div.style.height = '92px';
                div.style.backgroundColor = '#6b83ec'; // Bootstrap primary color
                div.style.color = 'white';
                div.style.borderRadius = '50%';
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.justifyContent = 'center';
                div.style.fontSize = '2rem';
                div.style.fontWeight = 'bold';
                div.style.border = '2px solid #6b83ec';

                // Replace image with div
                img.replaceWith(div);
            }
        </script>
    @elseif($role == 2 || $role == 3 || $role == 4)
        <div class="row align-items-center border rounded p-2 shadow-sm">
            <!-- User Image -->
            <div class="col">
                <!-- Profile Image -->

                <img id="userImage" src="{{ FileUpload::getFilePath($user?->profile_image) }}" alt="User Image"
                    class="rounded-circle border border-1 border-primary cursor-pointer"
                    onclick="showImage(this.src,'Profile Image')" width="100" height="100"
                    onerror="showInitials(this)">
            </div>

            <!-- User Info -->
            <div class="col">
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="mb-0 text-muted">{{ $user->email }}</p>

                <!-- Badges -->
                <div class="mt-2">
                    <span class="badge bg-success me-1">Active</span>
                    <span class="badge bg-info text-dark me-1">Verified</span>
                    <span class="badge bg-warning text-dark">Premium User</span>
                </div>
            </div>

            <!-- Edit Profile Button -->
            @if ($role == 2)
                <div class="col-auto">
                    <button type="button" class="btn buttonColor" data-bs-toggle="modal"
                        data-bs-target="#completeProfileModal">
                        <i class="bi bi-pencil-square me-1"></i> Complete Profile
                    </button>
                </div>
            @endif

        </div>

        <div class="row mt-3 g-3">

            <!-- Card 1: Completed Transaction -->
            @if ($role != 4)
                <div class="col-md-3">
                    <div class="card shadow-lg text-center p-3 card-hover">
                        <div class="card-body">
                            <i class="bi bi-check-circle-fill fs-4 text-success mb-2"></i>
                            <h6 class="card-title mb-1">Completed Transaction</h6>
                            <p class="card-text fs-6 fw-bold">{{ number_format(2345) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Total Spent -->
                <div class="col-md-3">
                    <div class="card shadow-lg text-center p-3 card-hover">
                        <div class="card-body">
                            <i class="bi bi-currency-dollar fs-4 text-primary mb-2"></i>
                            <h6 class="card-title mb-1">Total Spent</h6>
                            <p class="card-text fs-6 fw-bold">{{ number_format(1245) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Wallet Balance -->
                <div class="col-md-3">
                    <div class="card shadow-lg text-center p-3 card-hover">
                        <div class="card-body">
                            <i class="bi bi-wallet2 fs-4 text-warning mb-2"></i>
                            <h6 class="card-title mb-1">Wallet Balance</h6>
                            <p class="card-text fs-6 fw-bold">{{ number_format(4567) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Member Since -->
                <div class="col-md-3">
                    <div class="card shadow-lg text-center p-3 card-hover">
                        <div class="card-body">
                            <i class="bi bi-calendar-check fs-4 text-info mb-2"></i>
                            <h6 class="card-title mb-1">Member Since</h6>
                            <p class="card-text fs-6 fw-bold">{{ number_format(2012) }}</p>
                        </div>
                    </div>
                </div>
        </div>
    @endif


    <div class="row mt-3">
        <div class="col-12">
            <!-- Tabs nav -->
            <ul class="nav nav-tabs" id="profileTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-dark active" id="personal-tab" data-bs-toggle="tab"
                        data-bs-target="#personal" type="button" role="tab" aria-controls="personal"
                        aria-selected="true">Personal Information</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-dark" id="security-tab" data-bs-toggle="tab"
                        data-bs-target="#security" type="button" role="tab" aria-controls="security"
                        aria-selected="false">Security Setting</button>
                </li>
                @if ($role != 4)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="kyc-tab" data-bs-toggle="tab"
                            data-bs-target="#kyc" type="button" role="tab" aria-controls="kyc"
                            aria-selected="false">KYC Details</button>
                    </li>
                    {{-- <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="activity-tab" data-bs-toggle="tab"
                            data-bs-target="#activity" type="button" role="tab" aria-controls="activity"
                            aria-selected="false">Activity Log</button>
                    </li> --}}


                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="ipwhitelist-tab" data-bs-toggle="tab"
                            data-bs-target="#ipwhitelist" type="button" role="tab" aria-controls="ipwhitelist"
                            aria-selected="false">IP Whitelist</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="banking-tab" data-bs-toggle="tab"
                            data-bs-target="#banking" type="button" role="tab" aria-controls="banking"
                            aria-selected="false">Banking</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="integration-tab" data-bs-toggle="tab"
                            data-bs-target="#integration" type="button" role="tab" aria-controls="integration"
                            aria-selected="false">Key Integration</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="support-tab" data-bs-toggle="tab"
                            data-bs-target="#support" type="button" role="tab" aria-controls="support"
                            aria-selected="false">Support</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark" id="callback-tab" data-bs-toggle="tab"
                            data-bs-target="#callback" type="button" role="tab" aria-controls="callback"
                            aria-selected="false">Webhook</button>
                    </li>
                @endif
            </ul>

            <!-- Tabs content -->
            <div class="tab-content p-3" id="profileTabContent">
                <!-- Personal Information -->
                <!-- Personal Information Tab -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                    <div class="row mb-2">
                        <div class="col-md-4 fw-semibold">Full Name:</div>
                        <div class="col-md-8">{{ $user->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-semibold">Email Address:</div>
                        <div class="col-md-8">{{ $user->email }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-semibold">Phone Number:</div>
                        <div class="col-md-8">{{ $user->mobile }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-semibold">Date of Birth:</div>
                        <div class="col-md-8">01-Jan-1990</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-semibold">Gender:</div>
                        <div class="col-md-8">Male</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-semibold">City:</div>
                        <div class="col-md-8">New York, USA</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-semibold">Address:</div>
                        <div class="col-md-8">123 Main Street, NY 10001</div>
                    </div>
                </div>

                <!-- Security Setting -->
                {{-- <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                    <!-- Last Login Info -->
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Last Login:</div>
                        <div class="col-md-8">15-Jan-2026 10:45 AM</div>
                    </div>

                    <!-- Change Password Form -->
                    <form id="changePasswordForm">
                        @csrf

                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label fw-semibold">
                                Old Password:<span class="text-danger">*</span>
                            </label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" name="current_password"
                                    placeholder="Current Password">
                                <small class="text-danger error-current_password"></small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label fw-semibold">
                                New Password:<span class="text-danger">*</span>
                            </label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" name="new_password"
                                    placeholder="New Password">
                                <small class="text-danger error-new_password"></small>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label fw-semibold">
                                Confirm Password:<span class="text-danger">*</span>
                            </label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" name="new_password_confirmation"
                                    placeholder="Confirm Password">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn buttonColor">
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div> --}}


                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-transparent fw-bold">
                                    <i class="bi bi-shield-lock me-2"></i> Change Password
                                </div>
                                <div class="card-body">
                                    <form id="changePasswordForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Old Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="current_password"
                                                placeholder="Enter current password">
                                            <small class="text-danger error-current_password"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">New Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="new_password"
                                                placeholder="Enter new password">
                                            <small class="text-danger error-new_password"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Confirm Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="new_password_confirmation"
                                                placeholder="Confirm new password">
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn buttonColor w-100">
                                                Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-transparent fw-bold text-dark">
                                    <i class="bi bi-pci-card me-2"></i> Change MPIN
                                </div>
                                <div class="card-body">
                                    <form id="changeMpinForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Current MPIN <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="current_mpin"
                                                maxlength="6" pattern="\d*" inputmode="numeric"
                                                placeholder="Enter 6-digit current MPIN" required>
                                            <small class="text-danger error-current_mpin"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">New MPIN <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="new_mpin" maxlength="6"
                                                pattern="\d*" inputmode="numeric" placeholder="Set 6-digit new MPIN"
                                                required>
                                            <small class="text-danger error-new_mpin"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Confirm New MPIN <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="confirm_mpin"
                                                maxlength="6" pattern="\d*" inputmode="numeric" required
                                                placeholder="Confirm 6-digit new MPIN">
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn buttonColor w-100">
                                                Update MPIN
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KYC Details -->
                <div class="tab-pane fade" id="kyc" role="tabpanel" aria-labelledby="kyc-tab">

                    <!-- KYC Details -->
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Aadhaar Number:</div>
                        <div class="col-md-8">{{ $businessInfo->aadhar_number ?? '----' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">PAN Number:</div>
                        <div class="col-md-8">{{ $businessInfo->pan_number ?? '----' }}</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 fw-bold">Document Status:</div>
                        <div class="col-md-8">
                            <span class="badge bg-success">Verified</span>
                        </div>
                    </div>

                    <hr>

                    <!-- Document Images -->
                    <div class="row">
                        <!-- Aadhaar Front -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-header fw-bold text-center">
                                    Aadhaar Front
                                </div>
                                <div class="card-body text-center">
                                    @if (!empty($businessInfo->aadhar_front_image))
                                        <img src="{{ FileUpload::getFilePath($businessInfo->aadhar_front_image) }}"
                                            class="img-fluid rounded border" alt="Aadhaar Front" style="cursor:pointer"
                                            onclick="showImage(this.src,'Aadhaar Front')">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Aadhaar Back -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-header fw-bold text-center">
                                    Aadhaar Back
                                </div>
                                <div class="card-body text-center">
                                    @if (!empty($businessInfo->aadhar_back_image))
                                        <img src="{{ FileUpload::getFilePath($businessInfo->aadhar_back_image) }}"
                                            class="img-fluid rounded border" style="cursor:pointer"
                                            onclick="showImage(this.src,'Aadhaar Back')" alt="Aadhaar Back">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- PAN Card -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-header fw-bold text-center">
                                    PAN Card
                                </div>
                                <div class="card-body text-center">
                                    @if (!empty($businessInfo->pancard_image))
                                        <img src="{{ FileUpload::getFilePath($businessInfo->pancard_image) }}"
                                            class="img-fluid rounded border" style="cursor:pointer"
                                            onclick="showImage(this.src,'PAN Card')" alt="PAN Card">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Activity Log -->
                <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">01-Jan-2026 09:00 AM:</div>
                        <div class="col-md-8">Logged in from IP 192.168.1.1</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">05-Jan-2026 03:30 PM:</div>
                        <div class="col-md-8">Completed a transaction of $500</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">10-Jan-2026 11:15 AM:</div>
                        <div class="col-md-8">Updated profile information</div>
                    </div>
                </div>

                <!-- Banking Details  -->
                <div class="tab-pane fade" id="banking" role="tabpanel" aria-labelledby="banking-tab">
                    <div class="row mb-3 g-3">


                        <!-- Bank Details Card -->
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-bank me-1"></i> Bank Account Details
                                    </h6>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">Account Holder Name:</span>
                                        <span class="text-muted">{{ $user?->name }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">Account Number:</span>
                                        <span class="text-muted">{{ $usersBank?->account_number }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">IFSC Code:</span>
                                        <span class="text-muted">{{ $usersBank?->ifsc_code }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">Branch Name:</span>
                                        <span class="text-muted">{{ $usersBank?->branch_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Document Card -->
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-file-earmark-text me-1"></i> Bank Document
                                    </h6>

                                    <div class="d-flex align-items-center justify-content-between border rounded p-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- <i class="bi bi-file-earmark-pdf text-danger fs-4"></i> -->
                                            <div>
                                                <div class="fw-semibold">Bank Proof</div>
                                                <small class="text-muted">Cancelled cheque / Passbook</small>
                                            </div>
                                        </div>

                                        <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm"
                                            onclick="showImage('{{ FileUpload::getFilePath($usersBank?->bank_docs) }}','Bank Document')">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Key Details  -->
                <div class="tab-pane fade" id="integration" role="tabpanel" aria-labelledby="integration-tab">

                    <div class="text-end mb-3">
                        <button class="btn buttonColor" data-bs-toggle="modal" data-bs-target="#serviceModal">
                            Generate Key
                        </button>
                    </div>

                    <div class="row mb-2">
                        @foreach ($saltKeys as $key)
                            <div class="col-md-12 mb-2">
                                <div class="border rounded p-3">
                                    <div class="row">
                                        <div class="col-12">
                                            <strong>Client ID:</strong> {{ $key['client_id'] }} <br />
                                            <strong>Client Key:</strong> {{ maskValue($key['client_secret']) }} <br />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

                <!-- Support Representative -->
                <div class="tab-pane fade" id="support" role="tabpanel" aria-labelledby="support-tab">
                    <div class="row mb-3 g-3">
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fa fa-user-tie"></i>

                                        Support Representative
                                    </h6>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">Name:</span>
                                        <span
                                            class="text-muted">{{ $supportRepresentative?->assigned_support?->name ?? '----' }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">Email:</span>
                                        <span
                                            class="text-muted">{{ $supportRepresentative?->assigned_support?->email ?? '----' }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-semibold">Mobile:</span>
                                        <span
                                            class="text-muted">{{ $supportRepresentative?->assigned_support?->mobile ?? '----' }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- IP Whitelist tabl --}}
                <div class="tab-pane fade" id="ipwhitelist" role="tabpanel" aria-labelledby="ipwhitelist-tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>IP Whitelist Management</h6>
                        <button class="btn btn-sm buttonColor shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#addIpModal">
                            <i class="bi bi-plus-circle me-1"></i> Add New IP
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="ipWhitelistTable" class="table table-striped border shadow-sm w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Service</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- callback tab --}}
                <div class="tab-pane fade" id="callback" role="tabpanel" aria-labelledby="callback-tab">
                    <div class="card shadow-sm border-0">
                        <div
                            class="card-header bg-transparent fw-bold border-bottom d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-link-45deg me-2 text-primary"></i> Webhook Configuration</span>
                            <button class="btn btn-sm buttonColor shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#webhookModal">
                                <i class="bi bi-pencil-square me-1"></i> Update URL
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="p-3 border rounded bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-3 fw-bold text-muted text-uppercase small">Callback URL Status</div>
                                    <div class="col-md-9">
                                        <code class="text-primary fw-bold"
                                            id="display_webhook_url">{{ $businessInfo->callback_url ?? 'Not Set' }}</code>
                                    </div>
                                </div>
                                <hr>
                                <p class="mb-0 small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Is URL par transaction ke status updates real-time mein POST request ke through bheje
                                    jayenge.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>


    <!-- Complete Profile Modal  -->
    <div class="modal fade" id="completeProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Complete Your Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- STEP PROGRESS -->
                <div class="step-progress px-4 pt-3">
                    <div class="d-flex justify-content-between align-items-center text-center">

                        <div class="step-item active" data-step="1">
                            <span class="step-circle">1</span>
                            <div class="step-label">Personal</div>
                        </div>

                        <div class="step-line"></div>

                        <div class="step-item" data-step="2">
                            <span class="step-circle">2</span>
                            <div class="step-label">Business</div>
                        </div>

                        <div class="step-line"></div>

                        <div class="step-item" data-step="3">
                            <span class="step-circle">3</span>
                            <div class="step-label">KYC</div>
                        </div>

                        <div class="step-line"></div>

                        <div class="step-item" data-step="4">
                            <span class="step-circle">4</span>
                            <div class="step-label">Banking</div>
                        </div>

                    </div>
                </div>

                <hr class="mt-2">

                <div class="modal-body">


                    <!-- STEP 1: Personal Details -->
                    <div class="step step-1">
                        <h6 class="mb-3">Personal Details</h6>
                        <div class="row g-2">

                            <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" placeholder="Enter full name"
                                    value="{{ $user->name }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="Enter email"
                                    value="{{ $user->email }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" name="mobile"
                                    placeholder="Enter mobile number" value="{{ $user->mobile ?? '' }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Profile Pic
                                </label>
                                <input type="file" class="form-control" accept=".jpg,.jpeg,.png"
                                    name="profile_image">
                                @if (!empty($userdata->profile_image))
                                    <div class="mt-2">
                                        <img src="{{ FileUpload::getFilePath($userdata->profile_image) }}"
                                            alt="Profile Image" class="img-thumbnail cursor-pointer profile-image"
                                            style="max-height: 120px;" onclick="showImage(this.src,'Profile Image')">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Business Details -->
                    <div class="step step-2 d-none">
                        <h6 class="mb-3">Business Details</h6>
                        <div class="row g-2">

                            <div class="col-md-6">
                                <label class="form-label">Business Name</label>
                                <input type="text" class="form-control" placeholder="Enter business name"
                                    name="business_name" id="business_name"
                                    value="{{ $businessInfo->business_name ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Business Category</label>
                                <select class="form-select" name="business_category" id="business_category">
                                    <option value="">--Select Business Category--</option>
                                    @foreach ($businessCategory as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $businessInfo?->business_category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Business Type</label>
                                <input type="text" class="form-control" placeholder="e.g. Retail, IT, Manufacturing"
                                    name="business_type" value="{{ $businessInfo?->business_type }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">CIN No</label>
                                <input type="text" class="form-control validate" name="cin_number"
                                    placeholder="e.g. L12345MH2010PLC123456"
                                    pattern="^[LU][0-9]{5}[A-Z]{2}[0-9]{4}[A-Z]{3}[0-9]{6}$"
                                    title="Enter valid CIN number" maxlength="21"
                                    value="{{ $businessInfo->cin_no ?? '' }}">
                                <span class="error-text">Invalid CIN number</span>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">GST No</label>
                                <input type="text" class="form-control validate" name="gst_number"
                                    placeholder="e.g. 27AAPFU0939F1ZV" maxlength="15"
                                    pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$"
                                    title="Enter valid GST number" style="text-transform: uppercase;"
                                    value="{{ $businessInfo->gst_number ?? '' }}">
                                <span class="error-text">Invalid GST number</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Business PAN</label>
                                <input type="text" class="form-control validate" name="business_pan"
                                    placeholder="e.g. AAACC1234A" maxlength="10" pattern="^[A-Z]{5}[0-9]{4}[A-Z]$"
                                    title="Enter valid PAN (AAAAA9999A)" style="text-transform: uppercase;"
                                    value="{{ $businessInfo->business_pan_number ?? '' }}">
                                <span class="error-text">Invalid PAN number</span>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Business Email</label>
                                <input type="email" class="form-control" placeholder="Enter business email"
                                    name="business_email" value="{{ $businessInfo->business_email ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Business Phone</label>
                                <input type="text" class="form-control validate" name="business_phone"
                                    placeholder="Enter 10 digit mobile number" maxlength="10" pattern="[6-9][0-9]{9}"
                                    title="Enter valid 10-digit mobile number starting with 6-9" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    value="{{ $businessInfo->business_phone ?? '' }}">
                                <span class="error-text">Invalid Phone number</span>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Business Documents
                                    <small class="text-muted">(You can upload multiple files)</small>
                                </label>
                                <input type="file" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png"
                                    name="business_docs[]">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">State</label>
                                <select class="form-select" name="state">
                                    <option value="">--Select State--</option>
                                    <option value="Uttar Pradesh" value="{{ $businessInfo->state ?? '' }}"
                                        {{ $businessInfo?->state == 'Uttar Pradesh' ? 'selected' : '' }}>Uttar Pradesh
                                    </option>
                                    <option value="Bihar">Bihar</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <select class="form-select" name="city">
                                    <option value="">--Select City--</option>
                                    <option value="Lucknow" value="{{ $businessInfo->city ?? '' }}"
                                        {{ $businessInfo?->city == 'Lucknow' ? 'selected' : '' }}>Lucknow</option>
                                    <option value="Kanpur">Kanpur</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Pin Code</label>
                                <input type="text" class="form-control validate" placeholder="Enter 6-digit Pin Code"
                                    name="pincode" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    value="{{ $businessInfo->pincode ?? '' }}" required>
                                <span class="error-text">Invalid Pin code</span>
                            </div>


                            <div class="col-12">
                                <label class="form-label">Business Address</label>
                                <textarea class="form-control" rows="2" placeholder="Enter business address" name="business_address">{{ $businessInfo->address ?? '' }}</textarea>
                            </div>
                            @if (!empty($businessInfo->business_document))
                                @php

                                    $docImage = json_decode($businessInfo->business_document ?? '', true);
                                @endphp
                                <div class="col-12 border p-1">
                                    <h6>Business Document</h6>
                                    @foreach ($docImage as $image)
                                        <img src="{{ FileUpload::getFilePath($image) }}"
                                            class="img m-1 shadow cursor-pointer" alt="Busineee Document"
                                            style="max-height:200px; width:200px;"
                                            onclick="showImage(this.src,'Business Document')">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- STEP 3: KYC Details -->
                    <div class="step step-3 d-none">
                        <div class="step step-3">
                            <h6 class="mb-3">KYC Details</h6>

                            <!-- Aadhaar & PAN Numbers -->
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Aadhaar Number</label>
                                    <input type="text" class="form-control validate"
                                        placeholder="Enter 12-digit Aadhaar Number" name="adhar_number" maxlength="12"
                                        inputmode="numeric" pattern="[0-9]{12}"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" required
                                        value="{{ $businessInfo->aadhar_number ?? '' }}">
                                    <span class="error-text">Invalid Aadhaar number</span>
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label">PAN Number</label>
                                    <input type="text" class="form-control validate"
                                        placeholder="Enter PAN Number (ABCDE1234F)" name="pan_number" maxlength="10"
                                        style="text-transform: uppercase;" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}"
                                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" required
                                        value="{{ $businessInfo->pan_number ?? '' }}">
                                    <span class="error-text">Invalid Pan number</span>
                                </div>



                                <div class="col-md-4">
                                    <label class="form-label">Aadhaar Front</label>
                                    <input type="file" class="form-control" accept=".jpg,.jpeg,.png"
                                        name="adhar_front_image">
                                    @if (!empty($businessInfo->aadhar_front_image))
                                        <div class="mt-2">
                                            <img src="{{ FileUpload::getFilePath($businessInfo->aadhar_front_image) }}"
                                                alt="Aadhaar Front" class="img-thumbnail cursor-pointer "
                                                style="max-height: 120px;" onclick="showImage(this.src,'Aadhaar Front')">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Aadhaar Back</label>
                                    <input type="file" class="form-control" accept=".jpg,.jpeg,.png"
                                        name="adhar_back_image">
                                    @if (!empty($businessInfo->aadhar_back_image))
                                        <div class="mt-2">
                                            <img src="{{ FileUpload::getFilePath($businessInfo->aadhar_back_image) }}"
                                                alt="Aadhaar Back" class="img-thumbnail cursor-pointer"
                                                style="max-height: 120px;" onclick="showImage(this.src,'Aadhaar Back')">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">PAN Card</label>
                                    <input type="file" class="form-control" accept=".jpg,.jpeg,.png"
                                        name="pan_card_image">
                                    @if (!empty($businessInfo->pancard_image))
                                        <div class="mt-2">
                                            <img src="{{ FileUpload::getFilePath($businessInfo->pancard_image) }}"
                                                alt="PAN Card" class="img-thumbnail cursor-pointer"
                                                style="max-height: 120px;" onclick="showImage(this.src,'PAN Card')">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Banking Details -->
                    <div class="step step-4 d-none">
                        <h6 class="mb-3">Banking Details</h6>

                        <div class="row g-2">

                            <div class="col-md-6">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text" class="form-control" placeholder="Enter account holder name"
                                    name="account_holder_name" value="{{ $usersBank->benificiary_name ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control validated" placeholder="Enter Account Number"
                                    name="account_number" maxlength="18" inputmode="numeric" pattern="[0-9]{9,18}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required
                                    value="{{ $usersBank->account_number ?? '' }}">
                                <span class="error-text">Invalid Account number</span>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">IFSC Code</label>
                                <input type="text" class="form-control validate" name="ifsc_code"
                                    placeholder="Enter IFSC Code" maxlength="11" style="text-transform: uppercase;"
                                    pattern="[A-Z]{4}0[A-Z0-9]{6}"
                                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" required
                                    value="{{ $usersBank->ifsc_code ?? '' }}">
                                <span class="invalid-feedback">Invalid IFSC code</span>

                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Branch Name</label>
                                <input type="text" class="form-control" placeholder="Enter branch name"
                                    name="branch_name" value="{{ $usersBank->branch_name ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Bank Documents</label>
                                <input type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png"
                                    name="bank_docs">
                                <small class="text-muted">Upload cheque / passbook copy (Max 2MB each)</small>

                                @if (!empty($usersBank->bank_docs))
                                    <div class="mt-2">
                                        <img src="{{ FileUpload::getFilePath($usersBank->bank_docs) }}"
                                            alt="Bank Document" class="img-thumbnail cursor-pointer"
                                            style="max-height: 120px;" onclick="showImage(this.src,'Bank Document')">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- FOOTER BUTTONS -->
                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-secondary" id="prevStep">Previous</button>
                    <button class="btn buttonColor" id="nextStep">Next</button>
                </div>

            </div>
        </div>
    </div>


    <!-- Generate Key Modal  -->
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="serviceForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Select Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        {{-- <select class="form-select" name="service" id="service" required>
                            <option value="">-- Select Service --</option>
                            @foreach ($UserServices as $userService)
                                <option value="{{ $userService->slug }}">{{ $userService->service_name }}</option>
                            @endforeach
                        </select> --}}
                        <select class="form-select" name="service" id="service" required>
                            <option value="">-- Select Service --</option>
                            @foreach ($UserServices as $userService)
                                <option value="{{ $userService->service->slug }}">
                                    {{ $userService->service->service_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn buttonColor">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>




    {{-- IP Byte list table and add button --}}


    <div class="modal fade" id="ipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add IP to Whitelist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="ipForm">
                    @csrf
                    <input type="hidden" name="ip_id" id="ip_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ip_address" id="modal_ip_address"
                                placeholder="e.g. 123.45.67.89" required pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Service <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="service_id" id="modal_service_id" required>
                                <option value="">-- Choose Service --</option>
                                @foreach ($UserServices as $userService)
                                    @if ($userService->service)
                                        <option value="{{ $userService->service_id }}">
                                            {{ $userService->service->service_name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="modalSubmitBtn" class="btn buttonColor">Save IP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="webhookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Callback URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="webhookForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction Callback URL <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-globe"></i></span>
                                <input type="url" class="form-control" name="callback_url" id="modal_callback_url"
                                    placeholder="https://yourdomain.com/api/callback"
                                    value="{{ $businessInfo->callback_url ?? '' }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="saveWebhookBtn" class="btn buttonColor">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        function showInitials(img) {
            const name = "{{ $user->name }}"; // Replace dynamically from backend
            const initial = name.charAt(0).toUpperCase();

            // Create a circle div
            const div = document.createElement('div');
            div.textContent = initial;
            div.style.width = '92px';
            div.style.height = '92px';
            div.style.backgroundColor = '#6b83ec'; // Bootstrap primary color
            div.style.color = 'white';
            div.style.borderRadius = '50%';
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.style.justifyContent = 'center';
            div.style.fontSize = '2rem';
            div.style.fontWeight = 'bold';
            div.style.border = '2px solid #6b83ec';

            // Replace image with div
            img.replaceWith(div);
        }
    </script>



    <script>
        document.getElementById('nextStep').addEventListener('click', function(e) {

            const currentStep = document.querySelector('.step:not(.d-none)');
            const inputs = currentStep.querySelectorAll('.validate');
            let isValid = true;

            inputs.forEach(input => {
                const pattern = input.getAttribute('pattern');
                const error = input.nextElementSibling;

                if (!pattern) return;

                const regex = new RegExp(pattern);

                if (!input.value || !regex.test(input.value)) {
                    input.classList.add('is-invalid');
                    error.style.display = 'block';
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                    error.style.display = 'none';
                }
            });

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            // ---------- STEP CHANGE LOGIC ----------
            let activeStep = document.querySelector('.step:not(.d-none)');
            let next = activeStep.nextElementSibling;

            if (next && next.classList.contains('step')) {
                activeStep.classList.add('d-none');
                next.classList.remove('d-none');

                // progress bar
                const stepNo = next.classList.contains('step-2') ? 2 :
                    next.classList.contains('step-3') ? 3 : 4;

                document.querySelectorAll('.step-item').forEach(item => {
                    item.classList.toggle('active', item.dataset.step == stepNo);
                });
            }
        });
    </script>


    <script>
        let currentStep = 1;
        const totalSteps = 4;

        function updateNextButton(step, totalSteps) {
            if (step === totalSteps) {
                $('#nextStep').html('Submit <i class="bi bi-check-circle"></i>');
            } else {
                $('#nextStep').html('Next <i class="bi bi-arrow-right"></i>');
            }
        }

        function showStep(step) {
            // Show form step
            $('.step').addClass('d-none');
            $('.step-' + step).removeClass('d-none');

            // Update progress bar
            $('.step-item').removeClass('active completed');

            $('.step-item').each(function() {
                let itemStep = $(this).data('step');
                if (itemStep < step) {
                    $(this).addClass('completed');
                } else if (itemStep === step) {
                    $(this).addClass('active');
                }
            });

            // Buttons
            $('#prevStep').toggle(step !== 1);
            $('#nextStep').text(step === totalSteps ? 'Submit' : 'Next');
            updateNextButton(step, 4);
        }

        $('#nextStep').click(function() {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            } else {
                // alert('Profile Completed Successfully!');
                // $('#completeProfileModal').modal('hide');
                submitProfileForm();
            }
        });

        $('#prevStep').click(function() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        $('#completeProfileModal').on('shown.bs.modal', function() {
            currentStep = 1;
            showStep(currentStep);
            $('#nextStep').removeClass('submitProfileButton')
        });
    </script>


    <script>
        $(document).ready(function() {

            $('#serviceForm').on('submit', function(e) {
                e.preventDefault();

                const service = $('#service').val();
                if (!service) return;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to create key & Id for this service?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm(service);
                    }
                });
            });

        });

        function submitForm(service) {


            let data = [];


            $.ajax({
                url: "{{ route('generate_client_credentials') }}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    service: service,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                }),
                success: function(response) {

                    const client_id = response.data.client_id;
                    const client_key = response.data.client_secret;

                    console.log(client_id);
                    console.log(client_key);

                    const result = {
                        success: true,
                        client_id: client_id,
                        client_key: client_key
                    };

                    console.log(result);

                    if (result.success) {
                        showCredentials(result.client_id, result.client_key);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });


            // const response = {
            //     success: true,
            //     client_id: data[0] || '',
            //     client_key: data[1] || ''
            // };

            // console.log(response);

            // if (response.success) {
            //     showCredentials(response.client_id, response.client_key);
            // }
        }

        function showCredentials(clientId, clientKey) {
            Swal.fire({
                title: 'Client key & Id Generated Successfully',
                html: `
            <div class="text-start">
                <p>
                    <strong>Client ID:</strong>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="clientId" value="${clientId}" readonly>
                        <span class="input-group-text copy-icon" onclick="copyText('clientId')" style="cursor:pointer">
                            <i class="bi bi-clipboard"></i>
                        </span>
                    </div>
                </p>

                <p>
                    <strong>Client Key:</strong>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="clientKey" value="${clientKey}" readonly>
                        <span class="input-group-text copy-icon" onclick="copyText('clientKey')" style="cursor:pointer">
                            <i class="bi bi-clipboard"></i>
                        </span>
                    </div>
                </p>
            </div>
        `,
                confirmButtonText: 'Close'
            }).then(() => {
                const serviceModalEl = document.getElementById('serviceModal');
                const serviceModal = bootstrap.Modal.getInstance(serviceModalEl);
                serviceModal.hide();
            });
        }

        function copyText(id) {
            const input = document.getElementById(id);
            input.select();
            navigator.clipboard.writeText(input.value);

            const icon = input.nextElementSibling.querySelector('i');
            icon.classList.remove('bi-clipboard');
            icon.classList.add('bi-clipboard-check');

            setTimeout(() => {
                icon.classList.remove('bi-clipboard-check');
                icon.classList.add('bi-clipboard');
            }, 1500);
        }
    </script>


    @endif


    <script>
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();

            $('.text-danger').text('');

            $.ajax({
                url: "{{ route('admin.change_password') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonColor: '#3085d6'
                    });

                    $('#changePasswordForm')[0].reset();
                },
                error: function(xhr) {

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        if (errors) {
                            $.each(errors, function(key, value) {
                                $('.error-' + key).text(value[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON.message
                            });
                        }
                    }
                }
            });
        });
    </script>

    <script>
        function submitProfileForm() {
            // alert('Submitting profile form...');
            const formData = new FormData();
            console.log(formData);
            const userId = document.getElementById('user_id').value;

            // Automatically append all input, select, textarea values
            $('#completeProfileModal input, #completeProfileModal select, #completeProfileModal textarea').each(function() {
                const name = $(this).attr('name');
                if (!name) return;

                // Handle file inputs
                if ($(this).is(':file')) {
                    const files = this.files;
                    if (files.length > 0) {
                        if (name.includes('[]')) {
                            // Multiple files
                            for (let i = 0; i < files.length; i++) {
                                formData.append(name, files[i]);
                            }
                        } else {
                            // Single file
                            formData.append(name, files[0]);
                        }
                    }
                }
                // Handle regular inputs (not disabled)
                else if (!$(this).is(':disabled')) {
                    formData.append(name, $(this).val());
                }
            });

            // Add CSRF token
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            // Debug: Check what's being sent
            console.log('FormData contents:');
            for (let pair of formData.entries()) {
                console.log(pair[0], pair[1]);
            }

            // Show loading state
            $('#nextStep').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

            // AJAX request
            $.ajax({
                url: `/completeProfile/${userId}`, // Replace with your route
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Handle success
                    $('#completeProfileModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated',
                        text: response.message,
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    // Handle errors
                    let errorMessage = 'Something went wrong!';

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;

                        // Loop through each field's errors and show them one by one
                        Object.values(errors).forEach(fieldErrors => {
                            fieldErrors.forEach(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    text: error,
                                });
                            });
                        });
                    } else {
                        // Other errors (like 500, 403, etc.)
                        let message = xhr.responseJSON?.message || 'Something went wrong';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                        });
                    }

                    $('#nextStep').prop('disabled', false).html('Submit <i class="bi bi-check-circle"></i>');
                }
            });
        }
    </script>
    <script>
        // Live validation: typing ke sath error remove ho
        document.querySelectorAll('.validate').forEach(input => {

            input.addEventListener('input', function() {
                const pattern = this.getAttribute('pattern');
                const error = this.nextElementSibling;

                if (!pattern) return;

                const regex = new RegExp(pattern);

                if (this.value && regex.test(this.value)) {
                    this.classList.remove('is-invalid');
                    error.style.display = 'none';
                }
            });

        });
    </script>


    <script>
        $(document).ready(function() {
            var table = $('#ipWhitelistTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50, 100],
                ajax: {
                    url: "{{ url('fetch/ip-whitelist/0/all') }}",
                    type: 'POST',
                    data: (d) => {
                        d._token = "{{ csrf_token() }}";
                    }
                },
                columns: [{
                        data: 'id',
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'service.service_name',
                        defaultContent: '<span class="text-muted">N/A</span>'
                    },
                    {
                        data: 'ip_address'
                    },
                    {
                        data: 'is_active',
                        render: (data, type, row) => `
                    <div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" data-id="${row.id}" ${data == "1" ? 'checked' : ''}>
                    </div>`
                    },
                    {
                        data: 'created_at',
                        render: (data) => typeof moment !== 'undefined' ? moment(data).format(
                            'DD-MMM-YYYY hh:mm A') : data
                    },
                    {
                        data: null,
                        render: (data, type, row) => `
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-sm edit-btn me-1 edit-ip" 
                            data-id="${row.id}" data-ip="${row.ip_address}" data-service="${row.service_id}">
                           <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm  delete-ip" data-id="${row.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>`
                    }
                ]
            });


            $('.btn[data-bs-target="#addIpModal"]').on('click', function() {
                $('#ipForm')[0].reset();
                $('#ip_id').val('');
                $('#modalTitle').text('Add IP to Whitelist');
                $('#modalSubmitBtn').text('Save IP');
                $('#modal_service_id').prop('disabled', false);
                $('#ipModal').modal('show');
            });


            $(document).on('click', '.edit-ip', function() {
                const id = $(this).data('id');
                const ip = $(this).data('ip');
                const serviceId = $(this).data('service');

                $('#ip_id').val(id);
                $('#modal_ip_address').val(ip);
                $('#modal_service_id').val(serviceId);

                $('#modalTitle').text('Update IP Address');
                $('#modalSubmitBtn').text('Update IP');
                $('#ipModal').modal('show');
            });


            $('#ipForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#ip_id').val();
                const submitBtn = $('#modalSubmitBtn');
                const targetUrl = id ? "{{ url('update-ip-address') }}/" + id :
                    "{{ route('add_ip_address') }}";

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Saving...');

                $.ajax({
                    url: targetUrl,
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            $('#ipModal').modal('hide');
                            table.ajax.reload(null, false);
                            Swal.fire('Success', res.message, 'success');
                        } else {
                            Swal.fire('Warning', res.message, 'warning');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.status === 422 ? Object.values(xhr.responseJSON
                            .errors).flat().join('<br>') : 'Internal Server Error';
                        Swal.fire('Error', errorMsg, 'error');
                    },
                    complete: () => submitBtn.prop('disabled', false).text(id ? 'Update IP' :
                        'Save IP')
                });
            });


            $(document).on('change', '.status-toggle', function() {
                let checkbox = $(this);
                let id = checkbox.data('id');
                let isChecked = checkbox.is(':checked') ? 1 : 0;
                let statusText = isChecked ? "activate" : "deactivate";

                Swal.fire({
                    title: 'Change Status?',
                    text: `Do you want to ${statusText} this IP address?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            url: "{{ url('status-ip-address') }}/" + id,
                            type: "GET",
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Updated!',
                                        text: res.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload(null,
                                        false);
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                    checkbox.prop('checked', !checkbox.is(
                                        ':checked'));
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Server side error occurred',
                                    'error');
                                checkbox.prop('checked', !checkbox.is(
                                    ':checked'));
                            }
                        });
                    } else {

                        checkbox.prop('checked', !checkbox.is(':checked'));
                    }
                });
            });



            $(document).on('click', '.delete-ip', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let deleteUrl = "{{ url('delete-ip-address') }}/" + id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This IP will be removed from your whitelist!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {


                        $.ajax({
                            url: deleteUrl,
                            type: "GET",
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: res.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload(null, false);
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire('Error',
                                    'Server error: Could not delete the IP.',
                                    'error');
                            }
                        });
                    }
                });
            });
        });
    </script>



    <script>
        $(document).ready(function() {

            if ($('#changeMpinForm').length > 0) {
                $('#changeMpinForm').on('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    $('.text-danger').text('');
                    let submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html(
                        '<i class="spinner-border spinner-border-sm"></i> Updating...');

                    $.ajax({
                        url: "{{ route('generate_mpin') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                Swal.fire('Success', response.message, 'success');
                                $('#changeMpinForm')[0].reset();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                $.each(errors, function(key, value) {
                                    $('.error-' + key).text(value[0]);
                                });
                            } else {
                                let errorMsg = xhr.responseJSON ? xhr.responseJSON.message :
                                    "Internal Server Error";
                                Swal.fire('Error', errorMsg, 'error');
                            }
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).text('Update MPIN');
                        }
                    });
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#webhookForm').on('submit', function(e) {
                e.preventDefault();

                let submitBtn = $('#saveWebhookBtn');
                let modalInput = $('#modal_callback_url').val();
                let displayElement = $('#display_webhook_url');

                // Loading state
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Saving...');

                // Fake delay simulation
                setTimeout(function() {

                    // UI update: Hamesha element exist karega isliye direct update
                    displayElement.text(modalInput);

                    // Modal band karein
                    $('#webhookModal').modal('hide');

                    // Success feedback
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Callback URL updated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }

                    submitBtn.prop('disabled', false).text('Save Changes');

                }, 800);
            });
        });
    </script>


@endsection
