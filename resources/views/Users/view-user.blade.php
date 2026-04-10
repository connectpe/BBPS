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

    .img-fixed {
        width: 100%;
        height: 100px;
        object-fit: cover;
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
                    <span class="label">User Id </span>
                    <span class="value">{{ $userData->id ?? '----' }} </span>
                </div>

                <div class="info-row">
                    <span class="label">Wallet Amount </span>
                    <span class="value">₹{{ number_format($userData->transaction_amount ?? 0,2) }} </span>
                </div>

                <div class="info-row mb-3">
                    <span class="label">Status</span>
                    @php
                    $statusArray = [
                    '0' => 'Initiated',
                    '1' => 'Active',
                    '2' => 'InActive',
                    '3' => 'Pending',
                    '4' => 'Suspended',
                    ];
                    $statusClass = ['1' => 'success', '2' => 'danger', '3' => 'warning', '4' => 'secondary'];
                    $statusLabel = $statusArray[$userData->status] ?? 'NA';
                    @endphp

                    <span class="badge bg-{{ $statusClass[$userData->status] ?? 'dark' }}">{{ $statusLabel }}</span>
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
                    <span class="value">{{ $businessInfo->business_name ?? '----' }}</span>
                </div>

                <div class="info-row">
                    <span class="label">Business Email</span>
                    <span class="value">{{ $businessInfo->business_email ?? '----' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Phone</span>
                    <span class="value">{{ $businessInfo->business_phone ?? '----' }}</span>
                </div>

                <div class="info-row">
                    <span class="label">Business Pan Number</span>
                    <span class="value">{{ $businessInfo->business_pan_number ?? '----' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Pan Name</span>
                    <span class="value">{{ $businessInfo->business_pan_name ?? '----' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pan Owner Name</span>
                    <span class="value">{{ $businessInfo->pan_owner_name ?? '----' }}</span>
                </div>

                <div class="info-row">
                    <span class="label">GST No</span>
                    <span class="value">{{ $businessInfo->gst_number ?? '----' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Business Category</span>
                    <span class="value">{{ $businessInfo->business_category_id ?? '----' }}</span>
                </div>

                <div class="info-row">
                    <span class="label">Business Type</span>
                    <span class="value">{{ $businessInfo->business_type ?? '----' }}</span>
                </div>


                <div class="info-row">
                    <span class="label">ITR Filled</span>
                    <span class="value">{{ $businessInfo?->itr_filled == '1' ? 'Yes' :
                        ($businessInfo?->itr_filled == '0' ? 'No' : '----') }}</span>
                </div>

                @if($businessInfo?->itr_filled == '0')
                <div class="info-row">
                    <span class="label">ITR Not Filled Reason</span>
                    <span class="value">{{ $businessInfo->itr_not_filed_reason ?? '----' }}</span>
                </div>
                @endif

                <div class="info-row">
                    <span class="label">State</span>
                    <span class="value">{{ $businessInfo->state ?? '----' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">City</span>
                    <span class="value">{{ $businessInfo->city ?? '----' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Pin Code</span>
                    <span class="value">{{ $businessInfo->pincode ?? '----' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Address</span>
                    <span class="value">{{ $businessInfo->address ?? '----' }}</span>
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


    <div class="col-lg-6">

        <!--  Setup Cost -->
        <div class="card shadow-sm border mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-currency-rupee text-primary fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0">Setup Cost</h6>
                </div>

                <div class="d-flex align-items-center gap-2">

                    @if($userData?->setup_cost_paid == '1')

                    <span class="badge bg-success">Paid</span>
                    <span class="fw-bold text-success">
                        ₹{{ number_format($userData->setup_cost, 2) }}
                    </span>

                    @else
                    <div class="d-flex flex-column gap-1">

                        <!-- Row 1 -->
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:100px;">Status:</span>

                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" id="markAsPaid"
                                    onclick="markAsPaidSetupCost('{{ $userData->id }}', this)">
                                <label class="form-check-label" for="markAsPaid">
                                    Mark as Paid
                                </label>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:95px;">Setup Cost:</span>

                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                id="setupCostInput" value="{{ $userData->setup_cost ?? 0 }}" style="width:90px;">

                            <button id="submitSetupCost" class="btn btn-sm buttonColor"
                                onclick="updateSetupCost('{{ $userData->id }}')">
                                <span class="text">Update</span>
                                <span class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </div>

                    </div>

                    @endif

                </div>
            </div>
        </div>


        <!--  Enabled Services -->
        <div class="card shadow-sm border mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-gear text-primary fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0">Enabled Services</h6>
                </div>

                <div class="border rounded p-2">
                    @forelse($serviceEnabled as $service)
                    <span class="badge buttonColor me-1 mb-1">
                        {{ $service->service?->service_name ?? '----' }}
                    </span>
                    @empty
                    <span class="text-muted">No services found.</span>
                    @endforelse
                </div>
            </div>
        </div>


        <!--  Route Configuration -->
        <div class="card shadow-sm border mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-diagram-3 text-primary fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0">Route Configuration</h6>
                </div>

                <form id="routingForm">
                    @csrf
                    <input type="hidden" id="userId" value="{{ $userData->id }}">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Service Name</label>
                        <select class="form-select form-select2" id="serviceSelect" name="service_id">
                            <option value="">-- Select Service --</option>
                            @foreach ($globalServices as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->service_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Assign Provider</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                                data-bs-toggle="dropdown">
                                Select Provider
                            </button>
                            <ul class="dropdown-menu w-100 p-2" id="providersList">
                                <li class="text-muted small">Select a service first</li>
                            </ul>
                        </div>
                    </div>

                    <button type="submit" class="btn text-white w-100" style="background-color:#5e72e4;">
                        Submit
                    </button>

                    <div class="bg-light mt-3 p-2 rounded">
                        <small class="text-muted fw-bold">Assignment Summary:</small>
                        <div id="assignmentSummary">
                            <span class="text-secondary fst-italic">No providers assigned yet.</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!--  Services Request -->
        <div class="card shadow-sm border mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-gear text-primary fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0">Services Request</h6>
                </div>

                @forelse($serviceRequest as $service)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>{{ $service->service?->service_name ?? '----' }}</span>

                    <i class="bi bi-check-circle text-success cursor-pointer"
                        onclick="handleServiceAction('{{ $service->id }}','approve')"></i>
                </div>
                @empty
                <span class="text-muted">No service request found.</span>
                @endforelse
            </div>
        </div>


        <!--  KYC Details -->
        <div class="card shadow-sm border mb-3">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-shield-check text-primary fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0">KYC Details</h6>

                    @php
                    $kyc = $businessInfo?->is_kyc == '1';
                    @endphp

                    <div class="ms-auto text-center">
                        <span class="badge bg-{{ $kyc ? 'success' : 'danger' }}">
                            {{ $kyc ? 'Verified' : 'Not Verified' }}
                        </span><br>

                        <input class="form-check-input mt-1" type="checkbox" {{ $kyc ? 'checked' : '' }}
                            onclick="changeKycStatus('{{ $businessInfo?->id }}','{{ $businessInfo?->user_id }}',this)">
                    </div>
                </div>

                <div class="mb-2"><strong>Aadhaar:</strong> {{ $businessInfo->aadhar_number ?? '----' }}</div>
                <div class="mb-2"><strong>Individual PAN:</strong> {{ $businessInfo->pan_number ?? '----' }}</div>
                <div class="mb-2"><strong>Business PAN:</strong> {{ $businessInfo->business_pan_number ?? '----' }}
                </div>
                <div class="mb-2"><strong>GSTIN:</strong> {{ $businessInfo->gst_number ?? '----' }}</div>

            </div>
        </div>


        <!--  Documents -->
        <div class="card shadow-sm border mb-3">
            <div class="card-body">

                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-file-earmark text-primary fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0">Documents</h6>
                </div>

                <div class="row">

                    @php
                    $docs = [
                    ['label'=>'Aadhaar Front','file'=>$businessInfo->aadhar_front_image],
                    ['label'=>'Aadhaar Back','file'=>$businessInfo->aadhar_back_image],
                    ['label'=>'PAN Card','file'=>$businessInfo->pancard_image],
                    ['label'=>'Individual Photo','file'=>$businessInfo->individual_photo],
                    ['label'=>'Business PAN','file'=>$businessInfo->business_pan_image],
                    ['label'=>'Registration Certificate','file'=>$businessInfo->registration_certificate_image],
                    ['label'=>'GST Certificate','file'=>$businessInfo->gst_registration_certificate_image],
                    ['label'=>'Business Address','file'=>$businessInfo->business_address_proof_image],
                    ['label'=>'Inside Image','file'=>$businessInfo->inside_image],
                    ['label'=>'Outside Image','file'=>$businessInfo->outside_image],
                    ['label'=>'Signed MOA','file'=>$businessInfo->signed_moa_image],
                    ['label'=>'Signed AOA','file'=>$businessInfo->signed_aoa_image],
                    ['label'=>'Board Resolution','file'=>$businessInfo->board_resoultion_image],
                    ['label'=>'Declaration','file'=>$businessInfo->nsdl_declaration_image],
                    ['label'=>'ITR','file'=>$businessInfo->itr_file_image],
                    ];
                    @endphp

                    @foreach($docs as $doc)
                    <div class="col-md-4 mb-2">
                        <div class="border rounded p-2 text-center small">
                            {{ $doc['label'] }} <br>

                            @if(!empty($doc['file']))
                            <i class="fa fa-eye text-primary cursor-pointer"
                                onclick="showImage('{{ FileUpload::getFilePath($doc['file']) }}','{{ $doc['label'] }}')"></i>
                            @else
                            <i class="fa fa-eye-slash text-muted"></i>
                            @endif
                        </div>
                    </div>
                    @endforeach

                </div>

            </div>
        </div>

    </div>
</div>


<script>
    function handleServiceAction(serviceId) {
        Swal.fire({
            title: `Are you sure you want to approve?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            reverseButtons: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('service_request_approve_reject')}}",
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        serviceId: serviceId,
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message ?? 'Action completed successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
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
    $(document).ready(function () {
        const userRoutings = @json($userRootings -> pluck('provider_slug', 'service_id'));
        const saveUrl = "{{ route('admin.users.routing.save', encrypt($userData->id)) }}";

        function refreshSummary() {
            let html = '';
            const serviceOptions = $("#serviceSelect option:not([value=''])");

            serviceOptions.each(function () {
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

        $("#serviceSelect").on("change", function () {
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

            $.get(`/admin/services/${serviceId}/providers`, {
                user_id: $("#userId").val()
            }, function (res) {
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

        $(document).on("change", ".provider-chk", function () {
            $("#providerDropdown").text($(this).data('name'));
        });

        $("#routingForm").on("submit", function (e) {
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


    function changeKycStatus(id, userId, checkbox) {

        let newState = checkbox.checked;
        checkbox.checked = !newState;

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to change KYC status",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ route('change_ekyc_status') }}",
                    type: "POST",
                    data: {
                        id: id,
                        userId: userId,
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        if (response.status === true) {
                            checkbox.checked = newState;
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                            });
                        } else {
                            checkbox.checked = !newState;
                            Swal.fire({
                                icon: "warning",
                                title: "Incomplete Verification",
                                text: response.message,
                            });
                        }
                    },

                    error: function () {
                        checkbox.checked = !newState;

                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Something went wrong!"
                        });
                    }
                });

            } else {
                checkbox.checked = !newState;
            }

        });
    }


    function updateSetupCost(userId) {

        let btn = $('#submitSetupCost');
        let newAmount = parseFloat($('#setupCostInput').val()) || 0;

        if (newAmount <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Amount',
                text: 'Please enter a valid setup cost.'
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to update the setup cost?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        }).then((result) => {

            if (!result.isConfirmed) return;
            btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: "{{route('update_setup_cost')}}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    setupCost: newAmount,
                    userId: userId
                },

                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message ?? 'Setup cost updated successfully'
                        });

                        setTimeout(() => location.reload(), 1500);

                    } else {
                        Swal.fire({
                            icon: "error",
                            title: 'Error',
                            text: response.message ?? 'Something went wrong'
                        });
                    }
                },

                error: function (xhr) {

                    let message = "Something went wrong!";
                    let title = "Error";

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        title = "Validation Error";
                        message = Object.values(xhr.responseJSON.errors)[0][0];

                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: "error",
                        title: title,
                        text: message,
                    });
                },

                complete: function () {
                    btn.prop('disabled', false).text('Update');
                }
            });

        });
    }

    function markAsPaidSetupCost(userId, checkbox) {

        if (checkbox.checked) {
            checkbox.checked = false;
        }

        Swal.fire({
            title: 'Are you sure to mark as Paid?',
            text: "Once marked as paid, this cannot be changed",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ route('marked_paid_setup_cost') }}",
                    type: "POST",
                    data: {
                        userId: userId,
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {

                        if (response.status) {
                            checkbox.checked = true;

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message ?? 'Marked as Paid'
                            });

                            setTimeout(() => location.reload(), 1500);

                        } else {
                            checkbox.checked = false;

                            Swal.fire({
                                icon: "error",
                                title: 'Error',
                                text: response.message ?? 'Something went wrong'
                            });
                        }
                    },
                    error: function (xhr) {

                        checkbox.checked = false;

                        let message = "Something went wrong!";
                        let title = "Error";

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            title = "Validation Error";
                            message = Object.values(xhr.responseJSON.errors)[0][0];
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: "error",
                            title: title,
                            text: message,
                        });
                    }
                });

            } else {
                checkbox.checked = false;
            }

        });
    }
</script>
@endsection