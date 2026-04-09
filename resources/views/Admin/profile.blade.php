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


    .otp-box {
        width: 50px;
        height: 50px;
        text-align: center;
        font-size: 1.5rem;
        margin: 0 5px;
        border-radius: 10px;
        border: none;
        outline: none;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .15);
    }
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
                <p class="card-text fs-6 fw-bold">{{ number_format($completedTxn) }}</p>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Spent -->
    <div class="col-md-3">
        <div class="card shadow-lg text-center p-3 card-hover">
            <div class="card-body">
                <i class="bi bi-currency-rupee fs-4 text-primary mb-2"></i>
                <h6 class="card-title mb-1">Total Spent</h6>
                <p class="card-text fs-6 fw-bold">{{ number_format($totalSpent, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Card 3: Wallet Balance -->
    <div class="col-md-3">
        <div class="card shadow-lg text-center p-3 card-hover">
            <div class="card-body">
                <i class="bi bi-wallet2 fs-4 text-warning mb-2"></i>
                <h6 class="card-title mb-1">Wallet Balance</h6>
                <p class="card-text fs-6 fw-bold">{{ number_format($businessWallet, 2) }}</p>

            </div>
        </div>
    </div>


    <!-- Card 4: Member Since -->
    <div class="col-md-3">
        <div class="card shadow-lg text-center p-3 card-hover">
            <div class="card-body">
                <i class="bi bi-calendar-check fs-4 text-info mb-2"></i>
                <h6 class="card-title mb-1">Member Since</h6>
                <p class="card-text fs-6 fw-bold">
                    {{ $userdata->created_at ? \Carbon\Carbon::parse($userdata->created_at)->format('Y') : '' }}
                </p>
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
                            <input type="password" class="form-control" name="new_password" placeholder="New Password">
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
                            <small class="text-danger error-new_password"></small>
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
            onclick="showImage(this.src,'Profile Image')" width="100" height="100" onerror="showInitials(this)">
    </div>

    <!-- User Info -->
    <div class="col">
        <h4 class="mb-1">{{ $user->name ?? '----' }}</h4>
        <p class="mb-0 text-muted">{{ $user->email ?? '----' }}</p>

        @php

        $statusArray = [
        '0' => 'Initiated',
        '1' => 'Active',
        '2' => 'InActive',
        '3' => 'Pending',
        '4' => 'Suspended',
        ];
        $statusClass = ['1' => 'success', '2' => 'danger', '3' => 'warning', '4' => 'secondary'];
        $statusLabel = $statusArray[$user->status] ?? 'NA';
        @endphp

        <!-- Badges -->
        <div class="mt-2 d-flex gap-2">
            <span class="badge bg-{{ $statusClass[$user->status] ?? 'dark' }}">{{ $statusLabel }}</span>
            @if (in_array($role, [2, 3]))
            @php
            // Default values if businessInfo is null or missing 'is_kyc' attribute
            $message = 'KYC Not Verified';
            $badge = 'danger';
            $isKyc = false;

            // Check if $businessInfo exists and has the is_kyc attribute
            if ($businessInfo && isset($businessInfo->is_kyc)) {
            if ($businessInfo->is_kyc == '1') {
            $message = 'KYC Verified';
            $badge = 'success';
            $isKyc = true;
            }
            }
            @endphp
            <span class="badge bg-{{ $badge }} text-white" title="{{ $message }}">{{ $message }}</span>
            @endif

        </div>
    </div>

    <!-- Edit Profile Button -->
    @if ($role == 2 || $role == 3)
    <div class="col-auto">
        <div class="d-flex flex-column gap-2 mt-lg-0 mt-2">

            <button type="button" class="btn buttonColor" data-bs-toggle="modal" data-bs-target="#completeProfileModal">
                <i class="bi bi-pencil-square me-1"></i> Complete Profile
            </button>

            <button type="button" class="btn buttonColor" data-bs-toggle="modal"
                data-bs-target="#documentVerificationModal">
                <i class="bi bi-file-earmark-check me-1"></i> Documents Verification
            </button>

        </div>
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
                <p class="card-text fs-6 fw-bold">{{ number_format($completedTxn) }}</p>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Spent -->
    <div class="col-md-3">
        <div class="card shadow-lg text-center p-3 card-hover">
            <div class="card-body">
                <i class="bi bi-currency-rupee fs-4 text-primary mb-2"></i>
                <h6 class="card-title mb-1">Total Spent</h6>
                <p class="card-text fs-6 fw-bold">₹ {{ number_format($totalSpent, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Card 3: Wallet Balance -->
    <div class="col-md-3">
        <div class="card shadow-lg text-center p-3 card-hover">
            <div class="card-body">
                <i class="bi bi-wallet2 fs-4 text-warning mb-2"></i>
                <h6 class="card-title mb-1">Wallet Balance</h6>
                <p class="card-text fs-6 fw-bold">₹ {{ number_format($walletBalance ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Card 4: Member Since -->
    <div class="col-md-3">
        <div class="card shadow-lg text-center p-3 card-hover">
            <div class="card-body">
                <i class="bi bi-calendar-check fs-4 text-info mb-2"></i>
                <h6 class="card-title mb-1">Member Since</h6>
                <p class="card-text fs-6 fw-bold">
                    {{ $userdata->created_at ? \Carbon\Carbon::parse($userdata->created_at)->format('Y') : '' }}
                </p>
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
                <button class="nav-link fw-bold text-dark" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc"
                    type="button" role="tab" aria-controls="kyc" aria-selected="false">KYC Details</button>
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
                    <div class="col-md-4 fw-semibold">Organization Name</div>
                    <div class="col-md-8">{{ $user->business?->business_name ?? '----' }}</div>
                </div>
                {{-- <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">Gender:</div>
                    <div class="col-md-8">Male</div>
                </div> --}}
                <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">City:</div>
                    <div class="col-md-8">{{ $user->business?->city ?? '----' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">Address:</div>
                    <div class="col-md-8">{{ $user->business?->address ?? '----' }}</div>
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
                            <input type="password" class="form-control" name="new_password" placeholder="New Password">
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
                            <small class="text-danger error-new_password_confirmation"></small>
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

                    @if ($role != 4)
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div
                                class="card-header bg-transparent fw-bold text-dark d-flex justify-content-between align-items-center">

                                <div>
                                    <i class="bi bi-pci-card me-2"></i> Change MPIN
                                </div>

                                <button class="btn btn-sm buttonColor" data-bs-toggle="modal"
                                    data-bs-target="#forgotMpinModal">
                                    Forgot MPIN?
                                </button>

                            </div>
                            <div class="card-body">
                                <form id="changeMpinForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Current MPIN <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="current_mpin" maxlength="6"
                                            pattern="\d*" inputmode="numeric" placeholder="Enter 4-digit current MPIN"
                                            required>
                                        <small class="text-danger error-current_mpin"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">New MPIN <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="new_mpin" maxlength="6"
                                            pattern="\d*" inputmode="numeric" placeholder="Set 4-digit new MPIN"
                                            required>
                                        <small class="text-danger error-new_mpin"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Confirm New MPIN <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="new_mpin_confirmation"
                                            maxlength="6" pattern="\d*" inputmode="numeric" required
                                            placeholder="Confirm 4-digit new MPIN">
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
                    @endif

                </div>
            </div>

            <!-- KYC Details -->
            <div class="tab-pane fade" id="kyc" role="tabpanel" aria-labelledby="kyc-tab">

                <!-- KYC Details -->
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">Aadhaar Number:</div>
                            <div class="col-7">{{ $businessInfo->aadhar_number ?? '----' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">PAN Number:</div>
                            <div class="col-7">{{ $businessInfo->pan_number ?? '----' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">Business PAN Number:</div>
                            <div class="col-7">{{ $businessInfo->business_pan_number ?? '----' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">GSTIN:</div>
                            <div class="col-7">{{ $businessInfo->gst_number ?? '----' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">ITR Filled:</div>
                            <div class="col-7">
                                {{ $businessInfo?->itr_filled == '1' ? 'Yes' : ($businessInfo?->itr_filled == '0' ? 'No'
                                : '----') }}
                            </div>
                        </div>
                    </div>

                    @if ($businessInfo?->itr_filled == '0')
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">ITR Not Filled Reason:</div>
                            <div class="col-7">{{ $businessInfo->itr_not_filed_reason ?? '----' }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">Document Status:</div>
                            <div class="col-7">
                                <span class="badge bg-{{ $businessInfo?->is_kyc == '1' ? 'success' : 'danger' }}">
                                    {{ $businessInfo?->is_kyc == '1' ? 'Verified' : 'Not Verified' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <hr>

                <!-- Document Images -->
                <div class="row">
                    <!-- Aadhaar Front -->
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Aadhaar Front
                                @if (!empty($businessInfo->aadhar_front_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->aadhar_front_image) }}','Aadhaar Front')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Aadhaar Back -->
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Aadhaar Back
                                @if (!empty($businessInfo->aadhar_back_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->aadhar_back_image) }}','Aadhaar Back')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- PAN Card -->
                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                PAN Card
                                @if (!empty($businessInfo->pancard_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->pancard_image) }}','PAN Card')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Individual Photo
                                @if (!empty($businessInfo->individual_photo))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->individual_photo) }}','Individual Photo')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Business PAN Image
                                @if (!empty($businessInfo->business_pan_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->business_pan_image) }}','Business PAN Image')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Registeration Certificate
                                @if (!empty($businessInfo->registration_certificate_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->registration_certificate_image) }}','Registeration Certificate')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                GST Reg. Certificate
                                @if (!empty($businessInfo->gst_registration_certificate_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->gst_registration_certificate_image) }}','GST Registeration Certificate')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>


                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Business Address Proof
                                @if (!empty($businessInfo->business_address_proof_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->business_address_proof_image) }}','Business Address Proof Image')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Inside Image
                                @if (!empty($businessInfo->inside_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->inside_image) }}','Inside Image')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                OutSide Image
                                @if (!empty($businessInfo->outside_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->outside_image) }}','OutSide Image')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Signed MOA Image
                                @if (!empty($businessInfo->signed_moa_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->signed_moa_image) }}','Signed MOA Image')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Signed AOA Image
                                @if (!empty($businessInfo->signed_aoa_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->signed_aoa_image) }}','Signed AOA Image')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Board Resolution
                                @if (!empty($businessInfo->board_resoultion_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->board_resoultion_image) }}','Board Resolution')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                Declaration
                                @if (!empty($businessInfo->nsdl_declaration_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->nsdl_declaration_image) }}','Declaration')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header text-center">
                                ITR Filled Image
                                @if (!empty($businessInfo->itr_file_image))
                                <span style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->itr_file_image) }}','ITR Filled Image')"></i>
                                </span>
                                @else
                                <span>
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
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

                @if($serviceActive)
                <div class="text-end mb-3">
                    <button class="btn buttonColor" data-bs-toggle="modal" data-bs-target="#serviceModal">
                        Generate Key
                    </button>
                </div>
                @endif


                <div class="row mb-2">
                    {{-- @foreach ($saltKeys as $key)
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
                    @endforeach --}}

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="">
                                <tr>
                                    <th>#</th>
                                    <th>Service Name</th>
                                    <th>Client ID</th>
                                    <th>Client Key</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($saltKeys as $key)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>{{ $key->globalServices->service_name ?? 'N/A' }}</td>

                                    <!-- Client ID -->
                                    <td>{{ $key->client_id }}</td>
                                    <td>{{ maskValue($key->client_secret) }}</td>
                                    <td>
                                        <span class="badge {{ $key->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $key->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $key->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No records found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
                                    <span class="text-muted">{{ $supportRepresentative?->assigned_support?->name ??
                                        '----' }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold">Email:</span>
                                    <span class="text-muted">{{ $supportRepresentative?->assigned_support?->email ??
                                        '----' }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold">Mobile:</span>
                                    <span class="text-muted">{{ $supportRepresentative?->assigned_support?->mobile ??
                                        '----' }}</span>
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

            {{-- Webhook / Callback Tab --}}
            <div class="tab-pane fade" id="callback" role="tabpanel" aria-labelledby="callback-tab">
                <div class="card shadow-sm border-0">
                    <div
                        class="card-header bg-transparent fw-bold border-bottom d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-link-45deg me-2 text-primary"></i>
                            Webhook Configuration
                        </span>

                        <button class="btn btn-sm buttonColor shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#addWebhookModal">
                            <span id="webhookBtnText">
                                <i class="bi bi-plus-circle me-1"></i>
                                Add URL
                            </span>
                        </button>

                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Service Name</th>
                                            <th>Servie Slug</th>
                                            <th>Url</th>
                                            <th>Created At</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($webhookUrl as $value)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $value->service->service_name ?? '' }}</td>
                                            <td>{{ $value->service->slug ?? '' }}</td>
                                            <td>{{ $value->url }}</td>
                                            <td>{{ $value->created_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <i class="bi bi-pencil-square me-1 text-primary"
                                                    onclick="editWebHookUrl('{{$value->id}}','{{$value->service_id}}','{{$value->url}}')"></i>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                No records found
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--Add Webhook Modal --}}
            <div class="modal fade" id="addWebhookModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                Add WebHook URL
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="addWebhookForm">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Select Service <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-gear"></i></span>
                                        <select class="form-select" name="service_id" id="service_id" required>
                                            <option value="">--Select Service--</option>
                                            @foreach ($UserServices as $userService)
                                            <option value="{{ $userService?->service?->id }}">
                                                {{ $userService?->service?->service_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label fw-semibold">
                                        Transaction Callback URL <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-globe"></i></span>
                                        <input type="url" class="form-control" name="url" id="url"
                                            placeholder="https://yourdomain.com/api/callback" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" id="addWebhookBtn" class="btn buttonColor">Submit
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            {{--Edit Webhook Modal --}}
            <div class="modal fade" id="editWebhookModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                Edit WebHook URL
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="editWebhookForm">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Select Service <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-gear"></i></span>
                                        <select class="form-select" name="edit_service_id" id="edit_service_id"
                                            required>
                                            <option value="">--Select Service--</option>
                                            @foreach ($UserServices as $userService)
                                            <option value="{{ $userService?->service?->id }}">
                                                {{ $userService?->service?->service_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="url_id" id="url_id">
                                <div class="mb-2">
                                    <label class="form-label fw-semibold">
                                        Transaction Callback URL <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-globe"></i></span>
                                        <input type="url" class="form-control" name="edit_url" id="edit_url"
                                            placeholder="https://yourdomain.com/api/callback" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" id="editWebhookBtn" class="btn buttonColor">Update
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php

$kycMessage =
$businessInfo?->is_kyc == '1'
? 'KYC verified'
: 'You will not be able to change the details, once KYC
verified';
$kycColor = $businessInfo?->is_kyc == '1' ? 'text-success' : 'text-danger';

@endphp

<!-- Complete Profile Modal  -->
<div class="modal fade" id="completeProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Complete Your Profile (<small class="{{ $kycColor }}">{{ $kycMessage }}</small>)
                </h5>
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

                    {{-- <div class="step-line"></div>

                    <div class="step-item" data-step="5">
                        <span class="step-circle">5</span>
                        <div class="step-label">Transaction</div>
                    </div> --}}


                </div>
            </div>

            <hr class="mt-2">

            <div class="modal-body">


                <!-- STEP 1: Personal Details -->
                <div class="step step-1">
                    <h6 class="mb-3">Personal Details</h6>
                    <div class="row g-3">

                        <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">

                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control skip-draft" placeholder="Enter full name"
                                value="{{ $user->name }}" disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control skip-draft" placeholder="Enter email"
                                value="{{ $user->email }}" disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" class="form-control skip-draft" name="mobile"
                                placeholder="Enter mobile number" value="{{ $user->mobile ?? '' }}" disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Profile Pic
                            </label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="profile_image">
                                @if (!empty($userdata->profile_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($userdata->profile_image) }}','Profile Image')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Business Details -->
                <div class="step step-2 d-none">
                    <h5 class="mb-3">Business Details</h5>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Business Name<span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" placeholder="Enter business name"
                                name="business_name" id="business_name"
                                value="{{ $businessInfo->business_name ?? '' }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Business Email<span class="text-danger">*</span></label>
                            <input type="email" class="form-control" placeholder="Enter business email"
                                name="business_email" value="{{ $businessInfo->business_email ?? '' }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Business Phone<span class="text-danger">*</span></label>
                            <input type="text" class="form-control validate" name="business_phone"
                                placeholder="Enter 10 digit mobile number" maxlength="10" pattern="[6-9][0-9]{9}"
                                title="Enter valid 10-digit mobile number starting with 6-9" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                value="{{ $businessInfo->business_phone ?? '' }}">
                            <span class="error-text">Invalid Phone number</span>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">CIN No<span class="text-danger">*</span></label>
                            <input type="text" class="form-control validate" name="cin_number"
                                placeholder="e.g. L12345MH2010PLC123456"
                                pattern="^[LU][0-9]{5}[A-Z]{2}[0-9]{4}[A-Z]{3}[0-9]{6}$" title="Enter valid CIN number"
                                maxlength="21" value="{{ $businessInfo->cin_no ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                            <span class="error-text">Invalid CIN number</span>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">GST No<span class="text-danger">*</span></label>
                            <input type="text" class="form-control validate" name="gst_number"
                                placeholder="e.g. 27AAPFU0939F1ZV" maxlength="15"
                                pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$"
                                title="Enter valid GST number" style="text-transform: uppercase;"
                                value="{{ $businessInfo->gst_number ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                            <span class="error-text">Invalid GST number</span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Business PAN<span class="text-danger">*</span></label>
                            <input type="text" class="form-control validate" name="business_pan"
                                placeholder="e.g. AAACC1234A" maxlength="10" pattern="^[A-Z]{5}[0-9]{4}[A-Z]$"
                                title="Enter valid PAN (AAAAA9999A)" style="text-transform: uppercase;"
                                value="{{ $businessInfo->business_pan_number ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                            <span class="error-text">Invalid PAN number</span>
                        </div>

                        <div class="col-md-6">
                            <label for="business_category" class="form-label">Business Category<span
                                    class="text-danger">*</span></label>
                            <select class="form-select form-select2 w-100" name="business_category"
                                id="business_category">
                                <option value="">--Select Business Category--</option>
                                @foreach ($businessCategory as $category)
                                <option value="{{ $category->id }}" {{ $businessInfo?->business_category_id ==
                                    $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Business Type<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="e.g. Retail, IT, Manufacturing"
                                name="business_type" id="business_type" value="{{ $businessInfo?->business_type }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">State<span class="text-danger">*</span></label>
                            <select class="form-select form-select2" name="state" id="state">
                                <option value="">--Select State--</option>
                                <option value="Uttar Pradesh" value="{{ $businessInfo->state ?? '' }}" {{
                                    $businessInfo?->state == 'Uttar Pradesh' ? 'selected' : '' }}>
                                    Uttar Pradesh
                                </option>
                                <option value="Bihar">Bihar</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City<span class="text-danger">*</span></label>
                            <select class="form-select form-select2" name="city" id="city">
                                <option value="">--Select City--</option>
                                <option value="Lucknow" value="{{ $businessInfo->city ?? '' }}" {{ $businessInfo?->city
                                    == 'Lucknow' ? 'selected' : '' }}>
                                    Lucknow</option>
                                <option value="Kanpur">Kanpur</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pin Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control validate" placeholder="Enter 6-digit Pin Code"
                                name="pincode" id="pincode" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                value="{{ $businessInfo->pincode ?? '' }}" required>
                            <span class="error-text">Invalid Pin code</span>
                        </div>


                        <div class="col-6">
                            <label class="form-label">Business Address<span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="2" placeholder="Enter business address"
                                name="business_address">{{ $businessInfo->address ?? '' }}</textarea>
                        </div>



                        <h5 class="my-3">Business Documents</h5>
                        <hr>


                        <div class="col-md-6">
                            <label class="form-label">Business PAN Image<span class="text-danger">*</span></label>

                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="business_pan_image" {{ $isKyc ? 'disabled' : '' }}>

                                @if (!empty($businessInfo->business_pan_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->business_pan_image) }}','Business PAN')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif

                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Registeration Certificate<span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="registration_certificate_image" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($businessInfo->registration_certificate_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->registration_certificate_image) }}','Registeration Certificate')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">GST Registeration Certificate<span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="gst_registration_certificate_image" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($businessInfo->gst_registration_certificate_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->gst_registration_certificate_image) }}','GST Registeration Certificate')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Business Address Proof Image <i
                                    title="Electricity Bill (latest)/ Rent Agreement / Landline / Internet Bill / Property Tax Receipt"
                                    class="fas fa-circle-info"></i></label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="business_address_proof_image" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($businessInfo->business_address_proof_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->business_address_proof_image) }}','Business Address Proof Image')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Inside Image<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="inside_image">
                                @if (!empty($businessInfo->inside_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->inside_image) }}','Inside Image')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">OutSide Image<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="outside_image">
                                @if (!empty($businessInfo->outside_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->outside_image) }}','OutSide Image')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Signed MOA Image</label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="signed_moa_image" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($businessInfo->signed_moa_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->signed_moa_image) }}','Signed MOA Image')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Signed AOA Image</label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="signed_aoa_image" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($businessInfo->signed_aoa_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->signed_aoa_image) }}','Signed AOA Image')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Board Resolution</label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="board_resolution" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($businessInfo->board_resoultion_image))
                                <span class="input-group-text" style="cursor:pointer;" title="Preview">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->board_resoultion_image) }}','Board Resolution')"></i>
                                </span>
                                <a href="{{ FileUpload::getFilePath($businessInfo->board_resoultion_image) }}"
                                    download="Board_Resolution_{{ $businessInfo->user_id }}"
                                    class="input-group-text btn-light" style="text-decoration: none; color: inherit;"
                                    title="Download">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Declaration</label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="nsdl_declaration" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($businessInfo->nsdl_declaration_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->nsdl_declaration_image) }}','Declaration')"></i>
                                </span>
                                <a href="{{ FileUpload::getFilePath($businessInfo->nsdl_declaration_image) }}"
                                    download="Declaration_{{ $businessInfo->user_id ?? 'file' }}"
                                    class="input-group-text btn-light" style="text-decoration: none; color: inherit;"
                                    title="Download Declaration">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ITR Filled<span class="text-danger">*</span></label>
                            <select class="form-select form-select2" name="itr_filled" id="itr_filled"
                                onchange="showHideITRNotReason(this.value, 'div_itr_not_reason')">
                                <option value="">--Select State--</option>
                                <option value="1" {{ $businessInfo?->itr_filled == '1' ? 'selected' : '' }}>Yes
                                </option>
                                <option value="0" {{ $businessInfo?->itr_filled == '0' ? 'selected' : '' }}>No
                                </option>
                            </select>
                        </div>


                        <div class="col-md-6 d-none" id="div_itr_filled_image">
                            <label class="form-label">ITR Filled Image<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                    name="itr_filled_image" id="itr_filled_image">
                                @if (!empty($businessInfo->itr_file_image))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($businessInfo->itr_file_image) }}','ITR Filled Image')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 d-none" id="div_itr_not_reason">
                            <label class="form-label">ITR Not Filled Reason<span class="text-danger">*</span></label>
                            <textarea class="form-control" placeholder="ITR Not Filled Reason" name="itr_not_reason"
                                id="itr_not_reason"> {{ $businessInfo->itr_not_filed_reason ?? '' }} </textarea>
                        </div>
                    </div>
                </div>


                <!-- STEP 3: KYC Details -->
                <div class="step step-3 d-none">
                    <div class="step step-3">
                        <h6 class="mb-3">KYC Details (Individual)</h6>

                        <!-- Aadhaar & PAN Numbers -->
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Individual Aadhaar Number<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control validate"
                                    placeholder="Enter 12-digit Aadhaar Number" name="adhar_number" maxlength="12"
                                    inputmode="numeric" pattern="[0-9]{12}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required
                                    value="{{ $businessInfo->aadhar_number ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                                <span class="error-text">Invalid Aadhaar number</span>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Individual PAN Number<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control validate"
                                    placeholder="Enter PAN Number (ABCDE1234F)" name="pan_number" maxlength="10"
                                    style="text-transform: uppercase;" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}"
                                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" required
                                    value="{{ $businessInfo->pan_number ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                                <span class="error-text">Invalid Pan number</span>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Individual Aadhaar Front<span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                        name="adhar_front_image" {{ $isKyc ? 'disabled' : '' }}>
                                    @if (!empty($businessInfo->aadhar_front_image))
                                    <span class="input-group-text" style="cursor:pointer;">
                                        <i class="fa-solid fa-eye"
                                            onclick="showImage('{{ FileUpload::getFilePath($businessInfo->aadhar_front_image) }}','Aadhaar Front')"></i>
                                    </span>
                                    @else
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </span>
                                    @endif
                                </div>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Individual Aadhaar Back<span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                        name="adhar_back_image" {{ $isKyc ? 'disabled' : '' }}>
                                    @if (!empty($businessInfo->aadhar_back_image))
                                    <span class="input-group-text" style="cursor:pointer;">
                                        <i class="fa-solid fa-eye"
                                            onclick="showImage('{{ FileUpload::getFilePath($businessInfo->aadhar_back_image) }}','Aadhaar Back')"></i>
                                    </span>
                                    @else
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Individual PAN Card<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                        name="pan_card_image" {{ $isKyc ? 'disabled' : '' }}>

                                    @if (!empty($businessInfo->pancard_image))
                                    <span class="input-group-text" style="cursor:pointer;">
                                        <i class="fa-solid fa-eye"
                                            onclick="showImage('{{ FileUpload::getFilePath($businessInfo->pancard_image) }}','PAN Card')"></i>
                                    </span>
                                    @else
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Individual Photo<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" class="form-control skip-draft" accept=".jpg,.jpeg,.png"
                                        name="individual_photo" id="individual_photo" {{ $isKyc ? 'disabled' : '' }}>
                                    @if (!empty($businessInfo->individual_photo))
                                    <span class="input-group-text" style="cursor:pointer;">
                                        <i class="fa-solid fa-eye"
                                            onclick="showImage('{{ FileUpload::getFilePath($businessInfo->individual_photo) }}','Individual Photo')"></i>
                                    </span>
                                    @else
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Banking Details -->
                <div class="step step-4 d-none">
                    <h6 class="mb-3">Banking Details</h6>

                    <div class="row g-2">

                        <div class="col-md-6">
                            <label class="form-label">Account Holder Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter account holder name"
                                name="account_holder_name" value="{{ $usersBank->benificiary_name ?? '' }}" {{ $isKyc
                                ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Account Number<span class="text-danger">*</span></label>
                            <input type="text" class="form-control validated" placeholder="Enter Account Number"
                                name="account_number" maxlength="18" inputmode="numeric" pattern="[0-9]{9,18}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" required
                                value="{{ $usersBank->account_number ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                            <span class="error-text">Invalid Account number</span>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">IFSC Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control validate" name="ifsc_code"
                                placeholder="Enter IFSC Code" maxlength="11" style="text-transform: uppercase;"
                                pattern="[A-Z]{4}0[A-Z0-9]{6}"
                                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')" required
                                value="{{ $usersBank->ifsc_code ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                            <span class="invalid-feedback">Invalid IFSC code</span>

                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Branch Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter branch name" name="branch_name"
                                value="{{ $usersBank->branch_name ?? '' }}" {{ $isKyc ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bank Proof Documents<span class="text-danger">*</span>
                                (<small>Passbook/Cancelled Check/Bank
                                    Statement/Verification Letter</small> )</label>
                            <div class="input-group">
                                <input type="file" class="form-control skip-draft" accept=".pdf,.jpg,.jpeg,.png"
                                    name="bank_docs" {{ $isKyc ? 'disabled' : '' }}>
                                @if (!empty($usersBank->bank_docs))
                                <span class="input-group-text" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye"
                                        onclick="showImage('{{ FileUpload::getFilePath($usersBank->bank_docs) }}','Bank Document')"></i>
                                </span>
                                @else
                                <span class="input-group-text">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </span>
                                @endif
                            </div>
                            <small class="text-muted">Upload cheque / passbook copy (Max 2MB each)</small>
                        </div>
                    </div>
                </div>

                {{-- <div class="step step-5 d-none">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                                <div class="p-4 text-center"
                                    style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-bottom: 1px solid #e2e8f0;">

                                    <h5 class="fw-bold mb-1" style="color: #1e293b;">Secure Transaction</h5>

                                    <div class="d-inline-block px-3 py-1 rounded-pill bg-success bg-opacity-10 text-success small fw-medium"
                                        style="font-size: 11px;">
                                        <i class="bi bi-patch-check-fill me-1"></i> 100% Safe & Encrypted Payment
                                    </div>
                                </div>

                                <div class="card-body p-4">

                                    <div class="bg-light rounded-4 p-3 mb-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Amount</span>
                                            <span class="fw-semibold text-dark">₹ <span
                                                    id="baseAmountText">0.00</span></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Tax (GST 18%)</span>
                                            <span class="fw-semibold text-warning">+ ₹ <span
                                                    id="gstAmountText">0.00</span></span>
                                        </div>
                                        <hr class="my-2" style="border-top: 1px dashed #cbd5e1;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark">Total Amount</span>
                                            <span class="fs-3 fw-bolder text-success">₹ <span
                                                    id="totalAmountText">0.00</span></span>
                                        </div>
                                    </div>

                                    <div id="gstHelperText" class="mt-2 small text-muted">
                                        <i class="bi bi-info-circle-fill text-warning me-1"></i>
                                        An additional 18% GST will be charged on ₹<span id="txt_base">0</span>.
                                        (GST: ₹<span id="txt_gst">0</span>)
                                    </div>

                                    <form id="nsdlPayForm">
                                        @csrf
                                        <input type="hidden" name="amount" id="finalAmount" class="skip-draft">

                                        <button type="submit"
                                            class="btn btn-primary btn-lg w-100 py-3 shadow fw-bold border-0"
                                            id="payNowBtn"
                                            style="border-radius: 12px; background: #3c5be4; transition: 0.3s;">
                                            <i class="bi bi-qr-code me-2"></i>
                                            Pay Now
                                        </button>
                                    </form>
                                </div>

                                <div class="card-footer bg-white border-0 text-center pb-4">
                                    <div class="d-flex justify-content-center align-items-center gap-3">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/UPI-Logo-vector.svg"
                                            alt="UPI" height="20" style="opacity: 0.6;">
                                        <span class="text-muted" style="font-size: 12px;">Supported by NSDL
                                            Gateway</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-none animate__animated animate__zoomIn" id="qrBox">
                                <div class="card border-2 border-dashed border-primary bg-white">
                                    <div class="card-body text-center p-4">
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success px-4 py-2 rounded-pill mb-3">
                                            <i class="bi bi-clock me-1"></i> QR Valid for 5:00 mins
                                        </span>

                                        <div id="qrCanvasWrap" class="d-flex justify-content-center mb-3"></div>

                                        <div class="bg-light p-2 rounded-3 small">
                                            <span class="text-muted">Transaction ID:</span>
                                            <span class="fw-bold ms-1" id="txnIdText">-</span>
                                        </div>

                                        <p class="small text-muted mt-3 mb-0">Scan with PhonePe, GPay, or any UPI
                                            App
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}



            </div>

            <!-- FOOTER BUTTONS -->
            <div class="modal-footer d-flex justify-content-between">
                <button class="btn btn-secondary" id="prevStep">Previous</button>
                <button class="btn buttonColor" id="nextStep">Next</button>
            </div>

        </div>
    </div>
</div>

<!-- Document verification modal -->
<div class="modal fade" id="documentVerificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Document Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- STEP PROGRESS -->
            <div class="step-progress px-4 pt-3">
                <div class="d-flex justify-content-between text-center">

                    <div class="step-item active" data-step="1">
                        <span class="step-circle">1</span>
                        <div class="step-label">PAN</div>
                    </div>

                    <div class="step-line"></div>

                    <div class="step-item" data-step="2">
                        <span class="step-circle">2</span>
                        <div class="step-label">GSTIN</div>
                    </div>

                    <div class="step-line"></div>

                    <div class="step-item" data-step="3">
                        <span class="step-circle">3</span>
                        <div class="step-label">CIN</div>
                    </div>

                    <div class="step-line"></div>

                    <div class="step-item" data-step="4">
                        <span class="step-circle">4</span>
                        <div class="step-label">Bank</div>
                    </div>

                    <div class="step-line"></div>

                    <div class="step-item" data-step="5">
                        <span class="step-circle">5</span>
                        <div class="step-label">VideoKyc</div>
                    </div>

                </div>
            </div>

            <hr class="mt-2">

            <div class="modal-body" id="docVerifyModalBody">

                <!-- STEP 1 -->
                <div class="doc-step" data-doc-step="1">
                    <h6 class="mb-3">PAN Verification</h6>
                    <div class="card border shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1"><strong>PAN Number:</strong>
                                    <span id="panNumber">-</span>
                                </p>
                                <span class="badge" id="panBadge">Checking...</span>
                            </div>
                            <button type="button" class="btn" id="panButton"
                                onclick="verifyDocument('pan')">Verify</button>
                        </div>
                        <div class="mt-3" id="panMessage"></div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="doc-step d-none" data-doc-step="2">
                    <h6 class="mb-3">GSTIN Verification</h6>
                    <div class="card border shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1"><strong>GST Number:</strong>
                                    <span id="gstNumber">-</span>
                                </p>
                                <span class="badge" id="gstBadge">Checking...</span>
                            </div>
                            <button type="button" class="btn" id="gstButton"
                                onclick="verifyDocument('gst')">Verify</button>
                        </div>
                        <div class="mt-3" id="gstMessage"></div>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="doc-step d-none" data-doc-step="3">
                    <h6 class="mb-3">CIN Verification</h6>
                    <div class="card border shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1"><strong>CIN:</strong>
                                    <span id="cinNumber">-</span>
                                </p>
                                <span class="badge" id="cinBadge">Checking...</span>
                            </div>
                            <button type="button" class="btn" id="cinButton"
                                onclick="verifyDocument('cin')">Verify</button>
                        </div>
                        <div class="mt-3" id="cinMessage"></div>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div class="doc-step d-none" data-doc-step="4">
                    <h6 class="mb-3">Bank Verification</h6>
                    <div class="card border shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1"><strong>Account Number:</strong>
                                    <span id="bankNumber">-</span>
                                </p>
                                <span class="badge" id="bankBadge">Checking...</span>
                            </div>
                            <button type="button" class="btn" id="bankButton"
                                onclick="verifyDocument('bank')">Verify</button>
                        </div>
                        <div class="mt-3" id="bankMessage"></div>
                    </div>
                </div>

                <!-- STEP 5 -->
                <div class="doc-step d-none" data-doc-step="5">
                    <h6 class="mb-3">Video KYC</h6>
                    <div class="card border shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1"><strong>Name:</strong>
                                    <span id="videoKycName">-</span>
                                </p>
                                <p class="mb-1"><strong>Email:</strong>
                                    <span id="videoKycEmail">-</span>
                                </p>
                                <p class="mb-1"><strong>Phone:</strong>
                                    <span id="videoKycPhone">-</span>
                                </p>
                                <span class="badge" id="videoKycBadge">Checking...</span>
                            </div>
                            <button type="button" class="btn" id="videoKycButton"
                                onclick="verifyDocument('videokyc')">Verify</button>
                        </div>
                        <div class="mt-3" id="videoKycMessage"></div>
                    </div>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-between">
                <button class="btn btn-secondary" id="prevDocStep">Previous</button>
                <button class="btn buttonColor" id="nextDocStep">Next</button>
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
                    <select class="form-select form-select2" name="service" id="service" required>
                        <option value="">-- Select Service --</option>
                        @foreach ($UserServices as $userService)
                        <option value="{{ $userService?->service?->slug }}">
                            {{ $userService?->service?->service_name }}
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
                        <label class="form-label fw-semibold">Select Service <span class="text-danger">*</span></label>
                        <select class="form-select form-select2" name="service_id" id="modal_service_id" required>
                            <option value="">-- Choose Service --</option>
                            @foreach ($UserServices as $userService)
                            @if ($userService?->service)
                            <option value="{{ $userService->service_id }}">
                                {{ $userService?->service?->service_name }}
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


<!-- Forgot MPIN Modal -->
<div class="modal fade" id="forgotMpinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Reset MPIN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- EMAIL DISPLAY -->
                <div id="emailSection">
                    <p class="text-center">
                        OTP will be sent to<br>
                        <strong id="maskedEmail">{{ $userdata->masked_email }}</strong>
                    </p>

                    <div class="text-center mt-3">
                        <button class="btn btn-sm buttonColor" id="sendOtpBtn">
                            Send OTP
                        </button>
                    </div>
                </div>


                <!-- OTP SECTION -->
                <div id="otpSection" style="display:none">

                    <p class="text-center mb-3">Enter 4 Digit OTP sent to your Email</p>

                    <div class="d-flex justify-content-center gap-2 mb-3">

                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box form-control" id="otp1"
                            autofocus>
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box form-control" id="otp2">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box form-control" id="otp3">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box form-control" id="otp4">

                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm buttonColor" id="verifyOtpBtn">
                            Verify OTP
                        </button>
                        <button class="btn btn-sm buttonColor" id="resendOtpBtn">
                            Resend OTP
                        </button>
                    </div>

                </div>


                <!-- NEW MPIN SECTION -->
                <div id="mpinSection" style="display:none">

                    <div class="mb-3">
                        <label class="form-label">New MPIN</label>
                        <input type="password" class="form-control" id="newMpin" placeholder="Enter MPIN" maxlength="4"
                            inputmode="numeric">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm MPIN</label>
                        <input type="password" class="form-control" id="confirmMpin" placeholder="Confirm MPIN"
                            maxlength="4" inputmode="numeric">
                    </div>

                    <div class="text-end">
                        <button class="btn btn-sm buttonColor" id="updateMpinBtn">
                            Update MPIN
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>





<script>
    document.querySelectorAll('.otp-box').forEach((input, index, inputs) => {

        input.addEventListener('keyup', function (e) {

            if (this.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            if (e.key === "Backspace" && index > 0) {
                inputs[index - 1].focus();
            }

        });

    });


    $('#sendOtpBtn, #resendOtpBtn').click(function () {

        let $btn = $(this);
        $btn.prop('disabled', true);
        $btn.html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...'
        );

        $.ajax({
            url: "{{ route('send_otp_forget_mpin') }}",
            success: function (response) {
                if (response.status === true) {
                    Swal.fire('Success', response.message || 'OTP sent successfully', 'success');
                    $('#emailSection').hide();
                    $('#otpSection').show();
                    if ($btn.attr('id') === 'sendOtpBtn') {
                        $btn.text('OTP Sent'); // permanently change Send OTP button
                    } else {
                        $btn.text('Resend OTP'); // revert Resend OTP button text
                    }
                } else {
                    Swal.fire('Error', response.message || 'Failed to send otp', 'error');
                    $btn.text($btn.attr('id') === 'sendOtpBtn' ? 'Send OTP' : 'Resend OTP');
                    $btn.prop('disabled', false);
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', xhr.responseJSON?.message ||
                    'Something went wrong. Please try again.', 'error');
                $btn.text($btn.attr('id') === 'sendOtpBtn' ? 'Send OTP' : 'Resend OTP');
                $btn.prop('disabled', false);
            },
            complete: function () {
                if ($btn.attr('id') === 'resendOtpBtn') {
                    $btn.prop('disabled', false);
                }
            }
        });

    });


    $('#verifyOtpBtn').click(function () {

        let $btn = $(this);

        let otp = $('#otp1').val() + $('#otp2').val() + $('#otp3').val() + $('#otp4').val();

        if (otp.length < 4) {
            Swal.fire('Error', 'Please enter complete 4-digit OTP', 'error');
            return;
        }

        $btn.prop('disabled', true);
        $btn.html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...'
        );

        $.ajax({
            url: "{{ route('verify_otp_forget_mpin') }}", // Laravel route
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                otp: otp
            },
            success: function (response) {
                if (response.status === true) {
                    Swal.fire('Success', response.message || 'OTP Verified', 'success');

                    // Hide OTP section & show New MPIN section
                    $('#otpSection').hide();
                    $('#mpinSection').show();

                } else {
                    Swal.fire('Error', response.message || 'Invalid OTP', 'error');
                    $btn.prop('disabled', false);
                    $btn.text('Verify OTP');
                }
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                $btn.prop('disabled', false);
                $btn.text('Verify OTP');
            },
            complete: function () {
                $btn.prop('disabled', false);
                $btn.text('Verify OTP');
            }
        });

    });


    $('#updateMpinBtn').click(function () {

        let $btn = $(this);

        const newMpin = $("#newMpin").val();
        const confirmMpin = $("#confirmMpin").val();

        $btn.prop('disabled', true);
        $btn.html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...'
        );

        $.ajax({
            url: "{{ route('forget_mpin') }}", // Laravel route
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                newMpin: newMpin,
                confirmMpin: confirmMpin,
            },
            success: function (response) {
                if (response.status === true) {
                    $("#forgotMpinModal").modal('hide');
                    Swal.fire('Success', response.message || 'MPIN Changed Successfully',
                        'success');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Swal.fire('Error', response.message || 'MPIN not Changed', 'error');
                    $btn.prop('disabled', false);
                    $btn.text('Update MPIN');
                }
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                $btn.prop('disabled', false);
                $btn.text('Update MPIN');
            },
            complete: function () {
                $btn.prop('disabled', false);
                $btn.text('Update MPIN');
            }
        });

    });
</script>




<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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
    document.getElementById('nextStep').addEventListener('click', function (e) {

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
               next.classList.contains('step-3') ? 3 :
               next.classList.contains('step-4') ? 4 : 1;


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

        $('.step').addClass('d-none');
        $('.step-' + step).removeClass('d-none');


        $('.step-item').removeClass('active completed');

        $('.step-item').each(function () {
            let itemStep = $(this).data('step');
            if (itemStep < step) {
                $(this).addClass('completed');
            } else if (itemStep === step) {
                $(this).addClass('active');
            }
        });


        $('#prevStep').toggle(step !== 1);
        $('#nextStep').text(step === totalSteps ? 'Submit' : 'Next');
        updateNextButton(step, 4);
    }

    $('#nextStep').click(function () {
        saveDraft();
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        } else {
            // alert('Profile Completed Successfully!');
            // $('#completeProfileModal').modal('hide');
            submitProfileForm();
        }
    });

    $('#prevStep').click(function () {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    $('#completeProfileModal').on('shown.bs.modal', function () {

        currentStep = 1;
        showStep(currentStep);

        const userId = $('#user_id').val();
        const draft = JSON.parse(localStorage.getItem('profileDraft'));

        if (draft && draft.user_id !== userId) {
            localStorage.removeItem('profileDraft');
        }

        if (!profileExists) {

            const draft = JSON.parse(localStorage.getItem('profileDraft'));

            if (draft) {
                Object.keys(draft).forEach(function (key) {
                    const field = document.querySelector('[name="' + key + '"]');
                    if (field && !field.disabled) {
                        field.value = draft[key];
                    }
                });
            }
        } else {
            localStorage.removeItem('profileDraft');
        }
    });
</script>

{{-- <script>
    $(document).on('submit', '#nsdlPayForm', function (e) {
        e.preventDefault();

        let btn = $('#payNowBtn');
        btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Generating...');

        $('#qrBox').addClass('d-none');
        $('#qrCanvasWrap').html('');
        $('#qrImg').hide().attr('src', '');

        $.ajax({
            url: "{{ route('nsdl-initiatePayment') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function (res) {
                if (!res.status) {
                    Swal.fire('Error', res.message || 'Failed', 'error');
                    return;
                }

                $('#txnIdText').text(res.data.transaction_id || '-');
                $('#orderIdText').text(res.data.order_id || '-');
                $('#qrBox').removeClass('d-none');


                if (res.data.qr_url) {
                    $('#qrImg').attr('src', res.data.qr_url).show();
                    return;
                }


                if (res.data.qr_string) {
                    new QRCode(document.getElementById("qrCanvasWrap"), {
                        text: res.data.qr_string,
                        width: 220,
                        height: 220
                    });
                    return;
                }

                Swal.fire('Warning', 'QR data not found in API response', 'warning');
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Server Error', 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Pay Now');
            }
        });
    });
</script> --}}


<script>
    // function for show and hide if itr not filled
    function showHideITRNotReason(value, divId) {
        const isZero = value == 0 || value === '0';
        $('#' + divId).toggleClass('d-none', !isZero);
        $('#div_itr_filled_image').toggleClass('d-none', isZero);

    }

    $(document).ready(function () {

        $('#serviceForm').on('submit', function (e) {
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


        const itrFilled = "{{ $businessInfo?->itr_filled == 1 ? '1' : '0' }}";
        showHideITRNotReason(itrFilled, 'div_itr_not_reason')
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
            success: function (response) {

                const client_id = response.data.client_id;
                const client_key = response.data.client_secret;

                // console.log(client_id);
                // console.log(client_key);

                const result = {
                    success: true,
                    client_id: client_id,
                    client_key: client_key
                };

                // console.log(result);

                if (result.success) {
                    showCredentials(result.client_id, result.client_key);
                }
            },
            error: function (xhr) {
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
    $('#changePasswordForm').on('submit', function (e) {
        e.preventDefault();

        $('.text-danger').text('');

        $.ajax({
            url: "{{ route('admin.change_password') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    confirmButtonColor: '#3085d6'
                });

                $('#changePasswordForm')[0].reset();
            },
            error: function (xhr) {

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    if (errors) {
                        $.each(errors, function (key, value) {
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
        // console.log(formData);
        const userId = document.getElementById('user_id').value;
        // console.log('User ID:', userId);
        // Automatically append all input, select, textarea values
        $('#completeProfileModal input, #completeProfileModal select, #completeProfileModal textarea').each(function () {
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
        // console.log('FormData contents:');
        for (let pair of formData.entries()) {
            // console.log(pair[0], pair[1]);
        }

        // Show loading state
        $('#nextStep').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

        // AJAX request
        $.ajax({
            url: "{{ route('admin.complete_profile', ['user_id' => ':userId']) }}".replace(':userId', userId),

            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                // Handle success
                $('#completeProfileModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: response.message,
                });
                const url = new URL(window.location);
                url.searchParams.delete('is_kyc');
                history.replaceState(null, '', url);

                setTimeout(() => {
                    location.reload();
                }, 2000);
            },
            error: function (xhr) {
                // Handle errors
                let errorMessage = 'Something went wrong!';

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    // First, remove previous error highlights
                    Object.keys(errors).forEach(field => {
                        const input = document.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.remove('is-invalid');
                        }
                    });

                    // Loop through each field's errors
                    Object.entries(errors).forEach(([field, fieldErrors]) => {
                        const input = document.querySelector(`[name="${field}"]`);
                        if (input) {
                            // Highlight the input field
                            input.classList.add('is-invalid');
                        }

                        // Optional: show SweetAlert for each error
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

        input.addEventListener('input', function () {
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
    $(document).ready(function () {
        var table = $('#ipWhitelistTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [
                [0, 'desc']
            ],
            ajax: {
                url: "{{ url('fetch/ip-whitelist/0') }}",
                type: 'POST',
                data: (d) => {
                    d._token = "{{ csrf_token() }}";
                    d.is_deleted = '0'
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


        $('.btn[data-bs-target="#addIpModal"]').on('click', function () {
            $('#ipForm')[0].reset();
            $('#ip_id').val('');
            $('#modalTitle').text('Add IP to Whitelist');
            $('#modalSubmitBtn').text('Save IP');
            $('#modal_service_id').prop('disabled', false);
            $('#ipModal').modal('show');
        });


        $(document).on('click', '.edit-ip', function () {
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


        $('#ipForm').on('submit', function (e) {
            e.preventDefault();
            const id = $('#ip_id').val();
            const submitBtn = $('#modalSubmitBtn');
            const targetUrl = id ? "{{ route('update_ip_address', ['id' => ':id']) }}".replace(':id',
                id) : "{{ route('add_ip_address') }}";

            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Saving...');

            $.ajax({
                url: targetUrl,
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    if (res.status) {
                        $('#ipModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('Success', res.message, 'success');
                    } else {
                        Swal.fire('Warning', res.message, 'warning');
                    }
                },
                error: function (xhr) {
                    let errorMsg = xhr.status === 422 ? Object.values(xhr.responseJSON
                        .errors).flat().join('<br>') : 'Internal Server Error';
                    Swal.fire('Error', errorMsg, 'error');
                },
                complete: () => submitBtn.prop('disabled', false).text(id ? 'Update IP' :
                    'Save IP')
            });
        });

        $(document).on('change', '.status-toggle', function () {
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
                        url: "{{ route('status_ip_address', ['id' => ':id']) }}"
                            .replace(':id', id),
                        type: "GET",
                        success: function (res) {
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
                        error: function () {
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



        $(document).on('click', '.delete-ip', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let deleteUrl = "{{ route('delete_ip_address', ['id' => ':id']) }}".replace(':id', id);

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
                        success: function (res) {
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
                        error: function (xhr) {
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
    $(document).ready(function () {
        if ($('#changeMpinForm').length > 0) {
            $('#changeMpinForm').on('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to update your MPIN?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('.text-danger').text('');
                        let submitBtn = $(this).find('button[type="submit"]');
                        submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Updating...');

                        $.ajax({
                            url: "{{ route('generate_mpin') }}",
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: $(this).serialize(),
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire('Success', response.message, 'success');
                                    $('#changeMpinForm')[0].reset();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function (xhr) {
                                if (xhr.status === 422) {
                                    let errors = xhr.responseJSON.errors;
                                    $.each(errors, function (key, value) {
                                        $('.error-' + key).text(value[0]);
                                    });
                                } else {
                                    let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : "Internal Server Error";
                                    Swal.fire('Error', errorMsg, 'error');
                                }
                            },
                            complete: function () {
                                submitBtn.prop('disabled', false).text('Update MPIN');
                            }
                        });
                    }
                });
            });
        }
    });
</script>

<script>

        function editWebHookUrl(id, service_id, url) {
            $("#editWebhookModal").modal('show')
            $("#url_id").val(id);
            $("#edit_service_id").val(service_id).trigger('change');
            $("#edit_url").val(url);
        }

        $(document).ready(function () {

            $('#addWebhookForm').on('submit', function (e) {
                e.preventDefault();

                let btn = $('#addWebhookBtn');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                $.ajax({
                    url: "{{ route('add_web_hook_url') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.status) {
                            $('#addWebhookModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let firstErrorMessage = Object.values(errors)[0][0];
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: firstErrorMessage,
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                xhr.responseJSON?.message || 'Something went wrong on the server.',
                                'error'
                            );
                        }
                    },
                    complete: function () {
                        btn.prop('disabled', false).text('Submit');
                    }
                });
            });



            $('#editWebhookForm').on('submit', function (e) {
                e.preventDefault();

                let btn = $('#editWebhookBtn');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                $.ajax({
                    url: "{{ route('edit_web_hook_url') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.status) {
                            $('#editWebhookModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let firstErrorMessage = Object.values(errors)[0][0];
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: firstErrorMessage,
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                xhr.responseJSON?.message || 'Something went wrong on the server.',
                                'error'
                            );
                        }
                    },
                    complete: function () {
                        btn.prop('disabled', false).text('Update');
                    }
                });
            });


        });
</script>
<script>
        $(function () {
            const kyc = @json(request('is_kyc') === 'Yes');

            if (kyc == true || kyc == 1) {
                $("#completeProfileModal").modal('show');
            }
        });
</script>



<!-- For the Save data in local  -->
<script>
        const profileExists = @json(!empty($businessInfo));

        function saveDraft() {

            if (profileExists) return;
            const userId = $('#user_id').val(); // Get the current logged-in user ID
            const draftData = {
                user_id: userId
            };

            $('#completeProfileModal input, #completeProfileModal select, #completeProfileModal textarea').each(function () {

                const name = $(this).attr('name');
                if (name === '_token') return;
                if (!name) return;
                if ($(this).is(':file')) return;
                if ($(this).is(':disabled')) return;
                if ($(this).hasClass('skip-draft')) return;
                draftData[name] = $(this).val();
            });

            localStorage.setItem('profileDraft', JSON.stringify(draftData));
        }
</script>

@if ($role == 2 || $role == 3)
<script>
        let docVerifyStep = 1;
        let documentData = {};

        function getDocStepEl(step) {
            return document.querySelector('#docVerifyModalBody .doc-step[data-doc-step="' + step + '"]');
        }

        document.getElementById('documentVerificationModal')
            .addEventListener('show.bs.modal', function () {

                // Reset to step 1
                docVerifyStep = 1;
                document.querySelectorAll('#docVerifyModalBody .doc-step').forEach(el => el.classList.add("d-none"));
                getDocStepEl(1).classList.remove("d-none");
                updateDocSteps();

                fetch("{{ route('document.verification.data') }}")
                    .then(res => res.json())
                    .then(data => {

                        if (!data.status) return;

                        documentData = data;
                        console.log(data);

                        // Fill Numbers
                        document.getElementById("panNumber").innerText = data.business_pan_number ?? '-';
                        document.getElementById("gstNumber").innerText = data.gst_number ?? '-';
                        document.getElementById("cinNumber").innerText = data.cin_no ?? '-';
                        document.getElementById("bankNumber").innerText = data.account_number ?? '-';
                        document.getElementById("videoKycName").innerText = data.name ?? '-';
                        document.getElementById("videoKycEmail").innerText = data.email ?? '-';
                        document.getElementById("videoKycPhone").innerText = data.phone ?? '-';

                        setDocStatus("pan", data.pan_verified);
                        setDocStatus("gst", data.is_gstin_verify);
                        setDocStatus("cin", data.is_cin_verify);
                        setDocStatus("bank", data.bank_verified);
                        setDocStatus("videoKyc", data.videokyc_verified);
                    });
            });


        function setDocStatus(type, verified) {
            let badge = document.getElementById(type + "Badge");
            let button = document.getElementById(type + "Button");
            let message = document.getElementById(type + "Message");

            if (verified == 1) {
                badge.className = "badge bg-success";
                badge.innerText = "Verified";
                button.className = "btn btn-success";
                button.innerText = "Verified";
                message.innerHTML =
                    `<div class="alert alert-success mb-0">${type.toUpperCase()} verified successfully.</div>`;
            } else {
                badge.className = "badge bg-danger";
                badge.innerText = "Not Verified";
                button.className = "btn btn-danger";
                button.innerText = "Verify";
                message.innerHTML = `<div class="alert alert-danger mb-0">${type.toUpperCase()} not verified.</div>`;
            }
        }


        // Step navigation
        document.getElementById("nextDocStep").addEventListener("click", function () {
            if (docVerifyStep < 5) {
                getDocStepEl(docVerifyStep).classList.add("d-none");
                docVerifyStep++;
                getDocStepEl(docVerifyStep).classList.remove("d-none");
                updateDocSteps();
            }
        });

        document.getElementById("prevDocStep").addEventListener("click", function () {
            if (docVerifyStep > 1) {
                getDocStepEl(docVerifyStep).classList.add("d-none");
                docVerifyStep--;
                getDocStepEl(docVerifyStep).classList.remove("d-none");
                updateDocSteps();
            }
        });

        function updateDocSteps() {
            // Step indicator active class
            document.querySelectorAll("#documentVerificationModal .step-item").forEach(item => {
                item.classList.remove("active");
                if (item.dataset.step == docVerifyStep) {
                    item.classList.add("active");
                }
            });

            // Button control
            let prevBtn = document.getElementById("prevDocStep");
            let nextBtn = document.getElementById("nextDocStep");

            if (docVerifyStep === 1) {
                prevBtn.classList.add("d-none");
            } else {
                prevBtn.classList.remove("d-none");
            }

            if (docVerifyStep === 5) {
                nextBtn.classList.add("d-none");
            } else {
                nextBtn.classList.remove("d-none");
            }
        }


        // API integration
        function verifyDocument(type) {
            let url = "";
            let payload = {};

            if (type === "pan") {
                url = "{{ route('pan.verify') }}";
                payload = {
                    pan_number: documentData.business_pan_number,
                    pan_name: documentData.business_pan_name
                };
            }

            if (type === "gst") {
                url = "{{ route('gstin.verify') }}";
                payload = {
                    gst_number: documentData.gst_number
                };
            }

            if (type === "cin") {
                url = "{{ route('cin.verify') }}";
                payload = {
                    cin_no: documentData.cin_no
                };
            }

            if (type === "bank") {
                url = "{{ route('ifsc.verify') }}";
                payload = {
                    // account_number: documentData.account_number,
                    // beneficiary_name: documentData.benificiary_name,
                    // phone: documentData.phone,
                    ifsc: documentData.ifsc_code
                };
            }
            if (type === "videokyc") {
                url = "{{ route('videokyc.verify') }}";
                payload = {
                    name: documentData.name,
                    email: documentData.email,
                    phone: documentData.phone,
                    address: documentData.address
                };
            }

            fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(response => {
                    console.log("Response:", response);
                    if (response.status) {
                        setDocStatus(type, 1);
                    } else {
                        setDocStatus(type, 0);
                    }
                })
                .catch(err => console.log(err));
        }
</script>
@endif
@endsection