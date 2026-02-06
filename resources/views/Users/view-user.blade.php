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

                <form id="routingForm">
                    @csrf
                    <input type="hidden" id="userId" value="{{ $userData->id }}">

                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-diagram-3 text-primary fs-4 me-2"></i>
                        <h6 class="fw-bold mb-0">Routing Configuration</h6>
                    </div>

                    {{-- Service Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Service Name</label>
                        <select class="form-select" id="serviceSelect" name="service_id">
                            <option value="">-- Select Service --</option>
                            @foreach ($globalServices as $svc)
                            <option value="{{ $svc->id }}" data-service-name="{{ $svc->service_name }}">
                                {{ $svc->service_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Provider Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Assign Provider</label>
                        <div class="dropdown">
                            <button
                                class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center"
                                type="button" id="providerDropdown" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Select Provider
                            </button>
                            <ul class="dropdown-menu w-100 p-3" aria-labelledby="providerDropdown"
                                id="providersList">
                                <li class="text-muted small">Select a service first</li>
                            </ul>
                        </div>
                    </div>

                    <button type="submit" class="btn buttonColor w-100 mb-3 text-white"
                        style="background-color: #5e72e4;">Submit</button>

                    <div class="card bg-light mb-3">
                        <div class="card-body py-2">
                            <p class="mb-1 small text-muted text-uppercase fw-bold">Assignment Summary:</p>
                            <div id="assignmentSummary">
                                <span class="text-secondary fst-italic">No providers assigned yet.</span>
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

                    @php
                    $kyc = $businessInfo->is_kyc == '1' ? true : false;
                    @endphp

                    <div class="ms-auto d-flex flex-column align-items-center">
                        <span class="fw-semibold mb-1 fs-6 fw-bold badge bg-{{$kyc ? 'success' : 'danger'}}"> {{$kyc ? 'Verified' : 'Not Verified'}} </span>
                        <input class="form-check-input cursor-pointer fs-3" type="checkbox" {{$kyc ? 'checked' : ''}} onchange="changeKycStatus('{{$businessInfo->id}}','{{$businessInfo->user_id}}')">
                    </div>
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
        const userRoutings = @json($userRootings->pluck('provider_slug', 'service_id'));
        const saveUrl = "{{ route('admin.users.routing.save', encrypt($userData->id)) }}";

        function refreshSummary() {
            let html = '';
            const serviceOptions = $("#serviceSelect option:not([value=''])");

            serviceOptions.each(function() {
                const sId = $(this).val();
                const sName = $(this).text();
                if (userRoutings[sId]) {
                    html += `
                    <div class="mb-2">
                        <small class="text-muted d-block">${sName}: <span class="badge bg-primary text-uppercase">${userRoutings[sId]}</span></small>
                        
                    </div>`;
                }
            });

            $("#assignmentSummary").html(html ||
                '<span class="text-secondary fst-italic">No providers assigned yet.</span>');
        }
        refreshSummary();

        $("#serviceSelect").on("change", function() {
            const serviceId = $(this).val();
            const list = $("#providersList");
            const dropdownBtn = $("#providerDropdown");

            if (!serviceId) {
                list.html('<li class="text-muted small p-2">Select a service first</li>');
                dropdownBtn.text("Select Provider");
                return;
            }

            list.html(
                '<li class="text-muted small p-2 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></li>'
            );

            $.get(`/services/${serviceId}/providers`, {
                user_id: $("#userId").val()
            }, function(res) {
                if (res.status && res.data.length > 0) {
                    let html = '';
                    let savedSlug = userRoutings[serviceId] || null;

                    res.data.forEach(p => {
                        let isChecked = (savedSlug === p.slug) ? 'checked' : '';
                        html += `
                        <li class="p-1">
                            <div class="form-check">
                                <input class="form-check-input provider-chk" type="radio" name="provider_slug" 
                                       value="${p.slug}" id="p_${p.id}" data-name="${p.name}" ${isChecked}>
                                <label class="form-check-label w-100 cursor-pointer" for="p_${p.id}">${p.name}</label>
                            </div>
                        </li>`;
                    });
                    list.html(html);

                    let active = res.data.find(p => p.slug === savedSlug);
                    dropdownBtn.text(active ? active.name : "Select Provider");
                } else {
                    list.html('<li class="text-muted small p-2">No providers found</li>');
                }
            });
        });

        $(document).on("change", ".provider-chk", function() {
            $("#providerDropdown").text($(this).data('name'));
        });

        $("#routingForm").on("submit", function(e) {
            e.preventDefault();
            const serviceId = $("#serviceSelect").val();
            const providerSlug = $("input[name='provider_slug']:checked").val();
            const submitBtn = $(this).find("button[type='submit']");

            if (!serviceId || !providerSlug) return alert("Select both service and provider.");

            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Saving...');

            $.post(saveUrl, {
                    _token: "{{ csrf_token() }}",
                    service_id: serviceId,
                    provider_slug: providerSlug
                })
                .done(res => {
                    if (res.status) {
                        userRoutings[serviceId] = providerSlug;
                        refreshSummary();
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                })
                .fail(xhr => {
                    let msg = xhr.responseJSON?.message || "Error saving data";
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: msg
                    });
                })
                .always(() => submitBtn.prop('disabled', false).text('Submit'));
        });
    });


    function changeKycStatus(id, userId) {
        Swal.fire({
            title: 'Are you sure to change status of',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('change_ekyc_status') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        userId: userId,
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message ?? 'Status updated successfully',
                            timer: 2000,
                            showConfirmButton: true
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        let title = 'Error';
                        let message = 'Something went wrong!';

                        if (xhr.status === 422) {
                            title = 'Validation Error';

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                message = xhr.responseJSON.errors[firstKey][0];
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: title,
                            html: message,
                            timer: 2000,
                            showConfirmButton: true
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 3000);
                    }

                });
            }
        });
    }
</script>
@endsection