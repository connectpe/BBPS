@extends('layouts.app')

@section('title', 'View User')
@section('page-title', 'View User')

@section('content')

<style>
    .card-height {
        height: 400px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px dashed #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .label {
        color: #6c757d;
        font-size: 0.85rem;
    }

    .value {
        font-weight: 500;
    }

    .doc-card {
        position: relative;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8f9fa;
    }

    .doc-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .doc-card img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 6px;
    }

    .doc-label {
        display: block;
        text-align: center;
        margin-top: 6px;
        font-size: 0.8rem;
        color: #6c757d;
    }
</style>


<div class="row mt-3 g-4">

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">

                <!-- User Profile Section -->
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-person-circle text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">User Profile</h6>
                </div>

                <div class="info-row">
                    <span class="label">Name</span>
                    <span class="value">John Doe</span>
                </div>
                <div class="info-row">
                    <span class="label">Email</span>
                    <span class="value">john@example.com</span>
                </div>
                <div class="info-row">
                    <span class="label">Mobile</span>
                    <span class="value">+91 9876543210</span>
                </div>
                <div class="info-row mb-3">
                    <span class="label">Status</span>
                    <span class="badge bg-success">Active</span>
                </div>

                <!-- Divider -->
                <hr class="my-3">

                <!-- Business Details Section -->
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-briefcase text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">Business Details</h6>
                </div>

                <div class="info-row">
                    <span class="label">Business Name</span>
                    <span class="value">ABC Pvt Ltd</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Type</span>
                    <span class="value">IT Services</span>
                </div>
                <div class="info-row">
                    <span class="label">GST No</span>
                    <span class="value">27ABCDE1234F1Z5</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Email</span>
                    <span class="value">business@abc.com</span>
                </div>

            </div>
        </div>
    </div>


    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-shield-check text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">KYC Details</h6>
                    <span class="badge bg-success ms-auto">Verified</span>
                </div>

                <!-- KYC Info -->
                <div class="info-row">
                    <span class="label">PAN Number</span>
                    <span class="value">ABCDE1234F</span>
                </div>

                <div class="info-row">
                    <span class="label">Aadhaar</span>
                    <span class="value">XXXX-XXXX-1234</span>
                </div>

                <!-- Documents -->
                <div class="mt-3">
                    <div class="row g-3">

                        <!-- Aadhaar Front -->
                        <div class="col-6">
                            <div class="doc-card" onclick="showImage('/images/aadhar-front.jpg','Aadhaar Front')">
                                <img src="/images/aadhar-front.jpg" class="img-fluid rounded">
                                <small class="doc-label">Aadhaar Front</small>
                            </div>
                        </div>

                        <!-- Aadhaar Back -->
                        <div class="col-6">
                            <div class="doc-card" onclick="showImage('/images/aadhar-back.jpg','Aadhaar Back')">
                                <img src="/images/aadhar-back.jpg" class="img-fluid rounded">
                                <small class="doc-label">Aadhaar Back</small>
                            </div>
                        </div>

                        <!-- PAN Card -->
                        <div class="col-12">
                            <div class="doc-card" onclick="showImage('/images/pan-card.jpg','PAN Card')">
                                <img src="/images/pan-card.jpg" class="img-fluid rounded">
                                <small class="doc-label">PAN Card</small>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>



    <!-- Bank Details -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-bank text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">Bank Details</h6>
                </div>

                <div class="info-row">
                    <span class="label">Bank Name</span>
                    <span class="value">HDFC Bank</span>
                </div>
                <div class="info-row">
                    <span class="label">Account No</span>
                    <span class="value">****5678</span>
                </div>
                <div class="info-row">
                    <span class="label">IFSC</span>
                    <span class="value">HDFC0001234</span>
                </div>
                <div class="info-row">
                    <span class="label">Account Holder</span>
                    <span class="value">John Doe</span>
                </div>
            </div>
        </div>
    </div>

</div>



@endsection