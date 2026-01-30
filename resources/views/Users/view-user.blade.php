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

        .dropdown-menu {
            max-height: 250px;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item:active {
            background-color: transparent;
            color: inherit;
        }

        .form-check {
            margin-bottom: 5px;
        }

        .form-check-label {
            cursor: pointer;
        }

        .badge-provider {
            background-color: #6f42c1;
            color: white;
            margin-right: 5px;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
        }
    </style>

    @php
        use App\Facades\FileUpload;
    @endphp


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
                        <span class="value">{{ $userData->name ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value">{{ $userData->email ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Mobile</span>
                        <span class="value">{{ $userData->mobile ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Last Login at </span>
                        <span class="value"> </span>
                    </div>
                    <div class="info-row mb-3">
                        <span class="label">Status</span>

                        @if ($userData->status == 1)
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
                        <span class="value">{{ $businessInfo->business_name ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Pan Number</span>
                        <span class="value">{{ $businessInfo->business_pan_number ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Pan Name</span>
                        <span class="value">{{ $businessInfo->business_pan_name ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pan Owner Name</span>
                        <span class="value">{{ $businessInfo->pan_owner_name ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label"> Pan Number</span>
                        <span class="value">{{ $businessInfo->pan_number ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label"> Aadhaar Number</span>
                        <span class="value">{{ $businessInfo->aadhar_number ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Aadhaar Name</span>
                        <span class="value">{{ $businessInfo->aadhar_name ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Category</span>
                        <span class="value">{{ $businessInfo->business_category_id ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Type</span>
                        <span class="value">{{ $businessInfo->business_type ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">GST No</span>
                        <span class="value">{{ $businessInfo->gst_number ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Email</span>
                        <span class="value">{{ $businessInfo->business_email ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Phone</span>
                        <span class="value">{{ $businessInfo->business_phone ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">State</span>
                        <span class="value">{{ $businessInfo->state ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">City</span>
                        <span class="value">{{ $businessInfo->city ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pin Code</span>
                        <span class="value">{{ $businessInfo->pincode ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Address</span>
                        <span class="value">{{ $businessInfo->address ?? '' }}</span>
                    </div>

                    <div class="info-row">

                        @php
                            $docs = json_decode($businessInfo->business_document ?? '', true);

                        @endphp

                        <span class="label">Business Document</span>
                        <div class="document-images">
                            @foreach (optional($docs) as $doc)
                                <img src="{{ FileUpload::getFilePath($doc) }}" alt=""
                                    class="img-fluid rounded m-1 doc-card" height="150" width="150"
                                    onclick="showImage(this.src,'Business Document')">
                            @endforeach
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

                    {{-- Rooting --}}
                    {{-- <form action="">
                    @csrf
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-diagram-3 text-primary fs-4 me-2"></i>
                        <h6 class="fw-bold mb-0">Rooting</h6>
                    </div>
                    <div class="mb-3">
                        <label for="routingSelect" class="form-label">Services Name</label>
                        <select class="form-select" id="routingSelect" name="routing_option">
                            <option value="default" {{ $userData->routing_option == 'default' ? 'selected' : '' }}>Default
                            </option>
                            <option value="custom" {{ $userData->routing_option == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="priorityInput" class="form-label">Provider Name</label>
                        <select class="form-select" id="priorityInput" name="provider_name">
                            <option value="provider1" {{ $userData->provider_name == 'provider1' ? 'selected' : '' }}>Provider 1
                            </option>
                            <option value="provider2" {{ $userData->provider_name == 'provider2' ? 'selected' : '' }}>Provider 2
                            </option>
                            <option value="provider3" {{ $userData->provider_name == 'provider3' ? 'selected' : '' }}>Provider 3
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn buttonColor mb-3">Submit</button>

                </form> --}}

                    <form id="routingForm">
                        @csrf
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-diagram-3 text-primary fs-4 me-2"></i>
                            <h6 class="fw-bold mb-0">Routing Configuration</h6>
                        </div>

                        <div class="mb-3">
                            <label for="routingSelect" class="form-label fw-bold">Service Name</label>
                            <select class="form-select" id="routingSelect" name="routing_option">
                                <option value="default" {{ $userData->routing_option == 'default' ? 'selected' : '' }}>
                                    Default Service</option>
                                <option value="custom" {{ $userData->routing_option == 'custom' ? 'selected' : '' }}>
                                    Custom Service</option>
                                <option value="payout" {{ $userData->routing_option == 'payout' ? 'selected' : '' }}>
                                    Payout Service</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Assign Providers</label>
                            <div class="dropdown">
                                <button
                                    class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center"
                                    type="button" id="providerDropdown" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    Select Provider
                                </button>
                                <ul class="dropdown-menu w-100 p-3" aria-labelledby="providerDropdown">
                                    <li>
                                        <div class="form-check">
                                            <input class="form-check-input provider-chk" type="radio"
                                                name="provider_option" value="Provider 1" id="p1">
                                            <label class="form-check-label w-100" for="p1">Provider 1</label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-check">
                                            <input class="form-check-input provider-chk" type="radio"
                                                name="provider_option" value="Provider 2" id="p2">
                                            <label class="form-check-label w-100" for="p2">Provider 2</label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-check">
                                            <input class="form-check-input provider-chk" type="radio"
                                                name="provider_option" value="Provider 3" id="p3">
                                            <label class="form-check-label w-100" for="p3">Provider 3</label>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <button type="submit" class="btn buttonColor w-100 mb-3">Submit</button>
                        <div class="card bg-light mb-3">
                            <div class="card-body py-2">
                                <p class="mb-1 small text-muted text-uppercase fw-bold">Assignment Summary:</p>
                                <div id="assignmentSummary">
                                    <span class="text-secondary italic">No providers assigned yet.</span>
                                </div>
                            </div>
                        </div>
                    </form>

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
                                    onclick="handleServiceAction('{{ $service->id }}', 'reject')"></i>

                                <!-- Approve Icon -->
                                <i class="bi bi-check-circle text-success cursor-pointer ms-2"
                                    onclick="handleServiceAction('{{ $service->id }}', 'approve')"></i>
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
                        <span class="value"> {{ $businessInfo->pan_number ?? '' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="label">Aadhaar</span>
                        <span class="value">{{ $businessInfo->aadhar_number ?? '' }}</span>
                    </div>


                    <!-- Documents -->
                    <div class="mt-3">
                        <div class="row g-3">

                            <!-- Aadhaar Front -->
                            <div class="col-6">
                                <div class="doc-card">
                                    @if ($businessInfo?->aadhar_front_image)
                                        <img src="{{ FileUpload::getFilePath($businessInfo?->aadhar_front_image) }}"
                                            class="img-fluid rounded border" alt="Aadhaar Front" style="cursor:pointer"
                                            onclick="showImage(this.src,'Aadhaar Front')">
                                    @else
                                        <img src="{{ asset('assets/image/aadhar-front.png') }}" class="img-fluid rounded"
                                            onclick="showImage(this.src,'Aadhaar Front')">
                                    @endif
                                    <small class="doc-label">Aadhaar Front</small>
                                </div>
                            </div>

                            <!-- Aadhaar Back -->
                            <div class="col-6">
                                <div class="doc-card">
                                    @if ($businessInfo?->aadhar_back_image)
                                        <img src="{{ FileUpload::getFilePath($businessInfo?->aadhar_back_image) }}"
                                            class="img-fluid rounded border" alt="Aadhaar Back" style="cursor:pointer"
                                            onclick="showImage(this.src,'Aadhaar Back')">
                                    @else
                                        <img src="{{ asset('assets/image/aadhar-back.png') }}" class="img-fluid rounded"
                                            onclick="showImage(this.src,'Aadhaar Back')">
                                    @endif
                                    <small class="doc-label">Aadhaar Back</small>
                                </div>
                            </div>

                            <!-- PAN Card -->
                            <div class="col-12">
                                <div class="doc-card">
                                    @if ($businessInfo?->pancard_image)
                                        <img src="{{ FileUpload::getFilePath($businessInfo?->pancard_image) }}"
                                            class="img-fluid rounded border" alt="PAN Card" style="cursor:pointer"
                                            onclick="showImage(this.src,'PAN Card')">
                                    @else
                                        <img src="{{ asset('assets/image/pan-card.png') }}" class="img-fluid rounded"
                                            onclick="showImage(this.src,'PAN Card')">
                                    @endif

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
                        <span class="value">{{ $usersBank->bank_name ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Branch Name</span>
                        <span class="value">{{ $usersBank->branch_name ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Account No</span>
                        <span class="value">{{ $usersBank->account_number ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">IFSC</span>
                        <span class="value">{{ $usersBank->ifsc_code ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Account Holder</span>
                        <span class="value">{{ $usersBank->benificiary_name ?? '----' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Bank Document</span>
                        <span class="value">
                            <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm"
                                onclick="showImage('{{ FileUpload::getFilePath($usersBank?->bank_docs) }}','Bank Document')">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </span>
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


    <script>
    $(document).ready(function() {
        function updateSummary() {
            let serviceName = $("#routingSelect option:selected").text();
            // Sirf ek hi selected value milegi
            let selectedProvider = $(".provider-chk:checked").val();

            let summaryHtml = `<strong>Service:</strong> ${serviceName} <br> <strong>Assigned to:</strong> `;

            if (selectedProvider) {
                summaryHtml += `<span class="badge bg-primary text-uppercase">${selectedProvider}</span>`;
                $("#providerDropdown").text(selectedProvider); // Button par naam dikhayega
            } else {
                summaryHtml += `<span class="text-danger small">No provider selected</span>`;
                $("#providerDropdown").text("Select Provider");
            }

            $("#assignmentSummary").html(summaryHtml);
        }

        // Listen for changes
        $(document).on('change', '.provider-chk, #routingSelect', function() {
            updateSummary();
        });

        // Dropdown open rakhne ke liye jab radio click ho
        $('.dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });

        updateSummary();
    });
</script>
@endsection
