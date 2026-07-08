@extends('layouts.app')

@section('title', 'AEPS Services')
@section('page-title', 'AEPS Services')

@section('content')

<style>
    .aeps-card{
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
    }

    .aeps-card .card-body{
        padding: 30px 20px;
    }

    .service-item{
        text-align: center;
        margin-bottom: 20px;
    }

    .service-circle{
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #eef3fb;
        border: 2px solid #dbe4f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        text-decoration: none;
        transition: all .3s ease;
    }

    .service-circle i{
        font-size: 34px;
    }

    .service-item p{
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #333;
        line-height: 22px;
    }

    .service-circle:hover{
        background: #0d6efd;
        border-color: #0d6efd;
        transform: translateY(-5px);
        box-shadow: 0 8px 18px rgba(13,110,253,.25);
    }

    .service-circle:hover i{
        color: #fff !important;
    }

    @media(max-width:768px){
        .service-circle{
            width:75px;
            height:75px;
        }

        .service-circle i{
            font-size:28px;
        }

        .service-item p{
            font-size:14px;
        }
    }
</style>

<div class="container-fluid">

    <div class="card aeps-card">
        {{-- <div class="card-header bg-white">
            <h5 class="mb-0">AEPS Services</h5>
        </div> --}}

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
                        <a href="#" class="service-circle">
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

@endsection