@extends('layouts.app')

@section('title', 'Complete KYC')
@section('page-title', 'Complete KYC')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm kyc-card">
                <div class="card-body text-center p-5">

                    <!-- Icon -->
                    <div class="kyc-icon mb-4">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>

                    <!-- Title -->
                    <h3 class="mb-3 text-dark fw-bold">
                        KYC Not Completed
                    </h3>

                    <!-- Description -->
                    <p class="text-muted mb-4">
                        Your account verification is pending.
                        Please complete your KYC to unlock all features and ensure secure transactions.
                    </p>

                    <!-- Status Badge -->
                    <span class="badge bg-warning text-dark px-3 py-2 mb-4 d-inline-block">
                        Verification Pending
                    </span>

                    <!-- Action Button -->
                    <div class="mt-4">
                        <a href="{{route('admin_profile',['user_id' => Auth::user()->id,'is_kyc' => 'Yes'])}}" class="btn buttonColor  btn-lg px-4">
                            Complete KYC Now
                        </a>
                    </div>

                    <!-- Footer Note -->
                    <p class="small text-muted mt-4 mb-0">
                        It takes only a few minutes to complete your verification.
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Page Specific Styles --}}
<style>
    body {
        background-color: #f8fafc;
    }

    .kyc-card {
        border-radius: 16px;
        background: #ffffff;
    }

    .kyc-icon {
        width: 90px;
        height: 90px;
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 40px;
    }

    .btn-primary {
        border-radius: 30px;
    }
</style>
@endsection