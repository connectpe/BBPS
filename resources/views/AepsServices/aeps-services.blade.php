@extends('layouts.app')

@section('title', 'AEPS Services')
@section('page-title', 'AEPS Services')

@section('content')

<style>
    .aeps-card{background: #fff;border: 1px solid #e5e7eb;border-radius: 15px;
        /* box-shadow: 0 2px 10px rgba(0,0,0,.08); */}
    .aeps-card .card-body{padding: 30px 20px;}
    .service-item{text-align: center;margin-bottom: 20px;}
    .service-circle{width: 75px;height: 75px;border-radius: 50%;background: #eef3fb;border: 2px solid #dbe4f0;
        display: flex;align-items: center;justify-content: center;margin: 0 auto 12px;
        text-decoration: none;transition: all .3s ease;}
    .service-circle i{font-size: 28px;}
    .service-item p{margin: 0;font-size: 15px;font-weight: 600;
        color: #333;line-height: 22px;}
    .service-circle:hover{background: #0d6efd;border-color: #0d6efd;
        transform: translateY(-5px);box-shadow: 0 8px 18px rgba(13,110,253,.25);}
    .service-circle:hover i{color: #fff !important;}
    @media(max-width:768px){
        .service-circle{width:75px; height:75px;}
        .service-circle i{font-size:28px;}
        .service-item p{font-size:14px;}
    }
    /* balance enquiry */
    .modal-content{border-radius:18px;}
    .form-control-lg,.form-select-lg{border-radius:12px;}
    .form-control:focus,
    .form-select:focus{box-shadow:0 0 0 .15rem rgba(13,110,253,.15);border-color:#0d6efd; }
    .modal-header{ border-radius:18px 18px 0 0;}
    #captureFingerprint{border-radius:10px;}
    .alert-info{border-radius:12px;}
    .modal-footer{padding:18px 24px;}
</style>

<div class="container-fluid px-0">
    <div class="card aeps-card">
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-lg-3 col-md-3 col-6">
                    <div class="service-item">
                        <a href="#" class="service-circle">
                            <i class="bi bi-cash-stack text-success"></i>
                        </a>
                        <p>Cash Withdrawal</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-6">
                    <div class="service-item">
                        <a href="javascript:void(0)" class="service-circle" data-bs-toggle="modal"data-bs-target="#balanceEnquiryModal">
                          <i class="bi bi-wallet2 text-primary"></i> 
                        </a>
                        <p>Balance Enquiry</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-6">
                    <div class="service-item">
                        <a href="#" class="service-circle">
                            <i class="bi bi-receipt text-warning"></i>
                        </a>
                        <p>Mini Statement</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-6">
                    <div class="service-item">
                        <a href="#" class="service-circle">
                            <i class="bi bi-credit-card text-danger"></i>
                        </a>
                        <p>Aadhaar Pay</p>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>

{{-- balance enquiry  --}}
<div class="modal fade" id="balanceEnquiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('aeps.balance.enquiry') }}" method="POST">
                @csrf
                <div class="modal-header text-dark border-bottom-0">
                    <button type="button" class="btn-close btn-close-dark"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                {{-- <i class="bi bi-phone me-1 text-primary"></i> --}}
                                Mobile Number
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   name="mobileNumber"
                                   placeholder="Enter Mobile Number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                {{-- <i class="bi bi-credit-card-2-front me-1 text-primary"></i> --}}
                                Aadhaar Number
                            </label>

                            <input type="text"
                                   class="form-control form-control-lg"
                                   name="adhaarNumber"
                                   placeholder="Enter Aadhaar Number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                {{-- <i class="bi bi-bank me-1 text-primary"></i> --}}
                                Select Bank
                            </label>

                            <select class="form-select form-select-lg" name="iinno" required>
                                <option value="">Select Bank</option>
                                @foreach($bankName as $bank)
                                    <option value="{{ $bank['iinno'] }}">
                                        {{ $bank['bankName'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                {{-- <i class="bi bi-fingerprint me-1 text-primary"></i> --}}
                                Fingerprint
                            </label>

                            <input type="hidden"name="txtPidData"id="txtPidData">
                            <div class="border rounded-3 p-3 text-center bg-light">
                                <i class="bi bi-fingerprint display-5 text-success"></i>
                                <p class="mb-3 mt-2 text-muted">
                                    Capture fingerprint using RD Service
                                </p>
                                <button type="button" id="captureFingerprint" class="btn btn-success">
                                    <i class="bi bi-fingerprint"></i>Capture Fingerprint
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-4 mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        Please verify Aadhaar number and capture a valid fingerprint before proceeding.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn buttonColor px-4">
                        Check Balance
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

@endsection