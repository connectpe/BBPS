@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details')

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
        text-align: center;
    }

    .doc-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .doc-card img {
        /* width: 100%; */
        height: 120px;
        /* object-fit: cover; */
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
                <div class="info-row">
                    <span class="label">Last Login at </span>
                    <span class="value">Jan-20-2026 11:40 am</span>
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
                    <span class="value">Private Limited</span>
                </div>
                <div class="info-row">
                    <span class="label">Industry</span>
                    <span class="value">IT Industry</span>
                </div>
                <div class="info-row">
                    <span class="label">GST No</span>
                    <span class="value">27ABCDE1234F1Z5</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Email</span>
                    <span class="value">business@abc.com</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Phone</span>
                    <span class="value">9876543210</span>
                </div>
                <div class="info-row">
                    <span class="label">State</span>
                    <span class="value">Uttar Pradesh</span>
                </div>
                <div class="info-row">
                    <span class="label">City</span>
                    <span class="value">Lucknow</span>
                </div>
                <div class="info-row">
                    <span class="label">Pin Code</span>
                    <span class="value">224955</span>
                </div>
                <div class="info-row">
                    <span class="label">Address</span>
                    <span class="value">Gomti Nagar Lucknow</span>
                </div>

                <div class="info-row">
                    <span class="label">Business Document</span>
                    <div class="document-images">
                        <img src="{{ asset('assets/image/aadhar-front.png') }}" alt="" class="img-fluid rounded m-1 doc-card" onclick="showImage(this.src)">
                        <img src="{{ asset('assets/image/aadhar-front.png') }}" alt="" class="img-fluid rounded m-1 doc-card" onclick="showImage(this.src)">
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">

                <!-- Enabled Services  -->
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-gear text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">Enabled Services</h6>
                </div>

                <div class="border rounded p-2">
                    <span class="badge buttonColor ms-auto">Service 1</span>
                    <span class="badge buttonColor ms-auto">Service 2</span>
                    <span class="badge buttonColor ms-auto">Service 3</span>
                    <span class="badge buttonColor ms-auto">Service 4</span>
                    <span class="badge buttonColor ms-auto">Service 5</span>
                </div>

                <hr class="my-4">

                <!-- Enabled Services  -->
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-gear text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">Services Request</h6>
                </div>
                <div class="info-row">
                    <span class="label">Service 1</span>
                    <span class="value"><button class="btn buttonColor btn-sm">Approve <i class="bi bi-check-circle"></i></button> </span>
                </div>
                <div class="info-row">
                    <span class="label">Service 2</span>
                    <span class="value"><button class="btn buttonColor btn-sm">Approve <i class="bi bi-check-circle"></i></button> </span>
                </div>

                <hr class="m-3">
                <!-- KYC Info -->
                <div class="d-flex align-items-center my-3">
                    <i class="bi bi-shield-check text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">KYC Details</h6>
                    <span class="badge bg-success ms-auto">Verified</span>
                </div>


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
                            <div class="doc-card">
                                <img src="{{asset('assets/image/aadhar-front.png')}}" class="img-fluid rounded" onclick="showImage(this.src,'Aadhaar Front')">
                                <small class="doc-label">Aadhaar Front</small>
                            </div>
                        </div>

                        <!-- Aadhaar Back -->
                        <div class="col-6">
                            <div class="doc-card">
                                <img src="{{asset('assets/image/aadhar-back.png')}}" class="img-fluid rounded" onclick="showImage(this.src,'Aadhaar Back')">
                                <small class="doc-label">Aadhaar Back</small>
                            </div>
                        </div>

                        <!-- PAN Card -->
                        <div class="col-12">
                            <div class="doc-card">
                                <img src="{{asset('assets/image/pan-card.png')}}" class="img-fluid rounded" onclick="showImage(this.src,'PAN Card')">
                                <small class="doc-label">PAN Card</small>
                            </div>
                        </div>

                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex align-items-center mt-3">
                    <i class="bi bi-bank text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">Bank Details</h6>
                </div>

                <div class="info-row">
                    <span class="label">Branch Name</span>
                    <span class="value">Kalyanpur Lucknow</span>
                </div>
                <div class="info-row">
                    <span class="label">Account No</span>
                    <span class="value">56785675678</span>
                </div>
                <div class="info-row">
                    <span class="label">IFSC</span>
                    <span class="value">HDFC0001234</span>
                </div>
                <div class="info-row">
                    <span class="label">Account Holder</span>
                    <span class="value">John Doe</span>
                </div>
                <div class="info-row">
                    <span class="label">Bank Document</span>
                    <span class="value"><a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" onclick="showImage('','Bank Document')">
                            <i class="bi bi-eye me-1"></i> View
                        </a></span>
                </div>

            </div>

        </div>
    </div>

</div>



@endsection