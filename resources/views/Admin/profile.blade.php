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
</style>


<div class="row align-items-center border rounded p-2 shadow-sm">
    <!-- User Image -->
    <div class="col">
        <!-- Profile Image -->
        <img id="userImage" src="path/to/user-image.jpg" alt="User Image" class="rounded-circle border border-1 border-primary" width="100" height="100" onerror="showInitials(this)">
    </div>

    <!-- User Info -->
    <div class="col">
        <h4 class="mb-1">{{Auth::user()->name}} </h4>
        <p class="mb-0 text-muted">{{Auth::user()->email}} </p>

        <!-- Badges -->
        <div class="mt-2">
            
                @if(Auth::user()->status == '1')
                   <span class="badge bg-success me-1"> Active</span>
                @else
                   <span class="badge bg-danger me-1"> Inactive</span>

                @endif
            
            <span class="badge bg-info text-dark me-1">Verified</span>
            <span class="badge bg-warning text-dark">Premium User</span>
        </div>
    </div>


    <!-- Edit Profile Button -->
    <div class="col-auto">
        <a href="/edit-profile" class="btn text-white" style="background-color: #6b83ec;">
            <i class="bi bi-pencil-square me-1"></i> Edit
        </a>
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
                <button class="nav-link fw-bold text-dark active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">Personal Information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">Security Setting</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc" type="button" role="tab" aria-controls="kyc" aria-selected="false">KYC Details</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">Activity Log</button>
            </li>
        </ul>

        <!-- Tabs content -->
        <div class="tab-content border p-3" id="profileTabContent">
            <!-- Personal Information -->
            <!-- Personal Information Tab -->
            <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">Full Name:</div>
                    <div class="col-md-8">{{Auth::user()->name}}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">Email Address:</div>
                    <div class="col-md-8">{{Auth::user()->email}}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">Phone Number:</div>
                    <div class="col-md-8">+1 234 567 890</div>
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
            @if(isset($_SESSION['data']) && isset($_SESSION['status']))
                <script>
                    alert($_SESSION['message'])
                </script>
            @endif
            <!-- Security Setting -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <!-- Last Login Info -->
                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">Last Login:</div>
                    <div class="col-md-8">15-Jan-2026 10:45 AM</div>
                </div>

                <!-- Change Password Form -->
                <form method="post" action="{{route('password.reset')}}">
                    @csrf
                    
                    <div class="mb-3 row">
                        <label for="oldPassword" class="col-md-4 col-form-label fw-semibold">Old Password:</label>
                        <div class="col-md-8">
                            <input name="current_password" type="password" class="form-control" id="oldPassword" placeholder="Enter old password">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="newPassword" class="col-md-4 col-form-label fw-semibold">New Password:</label>
                        <div class="col-md-8">
                            <input name="new_password" type="password" class="form-control" id="newPassword" placeholder="Enter new password">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="confirmPassword" class="col-md-4 col-form-label fw-semibold">Confirm Password:</label>
                        <div class="col-md-8">
                            <input name="confirmation_new_password" type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 offset-md-4">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </div>
                </form>
            </div>


            <!-- KYC Details -->
            <div class="tab-pane fade" id="kyc" role="tabpanel" aria-labelledby="kyc-tab">
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Aadhaar Number:</div>
                    <div class="col-md-8">1234 5678 9012</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">PAN Number:</div>
                    <div class="col-md-8">ABCDE1234F</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Document Status:</div>
                    <div class="col-md-8">Verified</div>
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
        </div>
    </div>
</div>


<script>
    function showInitials(img) {
        const name = "John Doe"; // Replace dynamically from backend
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
@endsection