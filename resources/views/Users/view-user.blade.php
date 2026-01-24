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
                    <span class="value">{{$userData->name ?? '----'}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email</span>
                    <span class="value">{{$userData->email ?? '----'}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Mobile</span>
                    <span class="value">{{$userData->mobile ?? '----'}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Last Login at </span>
                    <span class="value"> </span>
                </div>
                <div class="info-row mb-3">
                    <span class="label">Status</span>

                    @if($userData->status == 1)
                    <span class="badge bg-success">ACTIVE</span>
                    @else
                    <span class="badge bg-danger">INACTIVE</span>
                    @endif
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
                    <span class="value">{{$businessInfo->business_name ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Pan Number</span>
                    <span class="value">{{$businessInfo->business_pan_number ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Pan Name</span>
                    <span class="value">{{$businessInfo->business_pan_name ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pan Owner Name</span>
                    <span class="value">{{$businessInfo->pan_owner_name ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label"> Pan Number</span>
                    <span class="value">{{$businessInfo->pan_number ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label"> Aadhaar Number</span>
                    <span class="value">{{$businessInfo->aadhar_number ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Aadhaar Name</span>
                    <span class="value">{{$businessInfo->aadhar_name ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Category</span>
                    <span class="value">{{$businessInfo->business_category_id ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Type</span>
                    <span class="value">{{$businessInfo->business_type ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">GST No</span>
                    <span class="value">{{$businessInfo->gst_number ?? ''}}</span>
                </div>
                <!-- <div class="info-row">
                    <span class="label">Business Email</span>
                    <span class="value">{{$businessInfo->business_name ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Phone</span>
                    <span class="value">{{$businessInfo->business_name ?? ''}}</span>
                </div> -->
                <div class="info-row">
                    <span class="label">State</span>
                    <span class="value">{{$businessInfo->state ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">City</span>
                    <span class="value">{{$businessInfo->city ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pin Code</span>
                    <span class="value">{{$businessInfo->pincode ?? ''}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Address</span>
                    <span class="value">{{$businessInfo->address ?? ''}}</span>
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
                    @forelse($serviceEnabled as $service)
                    <span class="badge buttonColor ms-auto">{{ $service->service?->service_name ?? '----' }}</span>
                    @empty
                    <span class="label text-muted">No services found.</span>
                    @endforelse
                </div>

                <hr class="my-4">

                <!-- Enabled Services  -->
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-gear text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0">Services Request</h6>
                </div>

                @forelse($serviceRequest as $service)
                <div class="info-row">
                    <span class="label">{{ $service->service?->service_name ?? '----' }}</span>
                    <span class="value">
                        <!-- Reject Icon -->
                        <i class="bi bi-x-circle text-danger cursor-pointer"
                            onclick="handleServiceAction('{{ $service->id }}', '{{ $service->user_id }}', 'reject')"></i>

                        <!-- Approve Icon -->
                        <i class="bi bi-check-circle text-success cursor-pointer ms-2"
                            onclick="handleServiceAction('{{ $service->id }}', '{{ $service->user_id }}', 'approve')"></i>
                    </span>
                </div>
                @empty
                <div class="info-row">
                    <span class="label text-muted">No service request found.</span>
                </div>
                @endforelse


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
                    <span class="label">Bank Name</span>
                    <span class="value">{{$usersBank->bank_name ?? '----'}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Branch Name</span>
                    <span class="value">{{$usersBank->branch_name ?? '----'}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Account No</span>
                    <span class="value">{{$usersBank->account_number ?? '----'}}</span>
                </div>
                <div class="info-row">
                    <span class="label">IFSC</span>
                    <span class="value">{{$usersBank->ifsc_code ?? '----'}}</span>
                </div>
                <div class="info-row">
                    <span class="label">Account Holder</span>
                    <span class="value">{{$usersBank->account_holder ?? '----'}}</span>
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


<script>
    function handleServiceAction(serviceId, userId, action) {
        Swal.fire({
            title: `Are you sure you want to ${action}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/service/action',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        service_id: serviceId,
                        user_id: userId,
                        action: action
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message ?? 'Action completed successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let message = 'Something went wrong!';

                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                            message = xhr.responseJSON.errors[firstKey][0];
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message
                        });
                    }
                });
            }
        });
    }
</script>

@endsection