@extends('layouts.app')

@section('title', 'API Partner Services')
@section('page-title', 'API Partner Services')
<style>
    .svc-card {border: 1px solid rgba(0, 0, 0, .08); border-radius: 16px; padding: 22px;background: #fff;box-shadow: 0 8px 18px rgba(0, 0, 0, .06);height: 100%;}
    .svc-top {display: flex;gap: 12px;align-items: center;margin-bottom: 16px;}
    .svc-icon {width: 44px;height: 44px;border-radius: 12px;display: inline-flex;align-items: center;justify-content: center;background: #f3f4f6;font-size: 20px;}
    .svc-title {
        font-weight: 800;
        font-size: 18px;
        margin: 0;
        color: #0f172a;
    }
</style>
@section('content')

    @php
        $serviceIcons = [
            'payout' => 'fa-solid fa-money-bill-transfer',
            'payin' => 'fa-solid fa-hand-holding-dollar',
            'service-2' => 'fa-solid fa-link',
            'banking-services' => 'fa-solid fa-building-columns',
            'pay-bill' => 'fa-solid fa-file-invoice-dollar',
            'test' => 'fa-solid fa-vial',
            'test3' => 'fa-solid fa-file-signature',
            'postpaid-recharge' => 'fa-solid fa-mobile-screen-button',
            'prepaid-recharge' => 'fa-solid fa-sim-card',
            'testing' => 'fa-solid fa-flask',
        ];

        $serviceDescriptions = [
            'payout' => 'Send payments instantly to bank accounts with fast and secure payout processing.',
            'payin' => 'Collect customer payments via secure pay-in methods with real-time status updates.',
            'service-2' => 'Access additional integrations and features related to service enablement.',
            'banking-services' =>'Enable banking-related services like transfers, settlements, and account operations.',
            'pay-bill' => 'Pay utility or service bills quickly with instant confirmation and transaction tracking.',
            'test' => 'Use this service for internal checks and feature validation before production.',
            'test3' => 'Document signing/verification service for secure transaction workflows.',
            'postpaid-recharge' => 'Recharge postpaid mobile numbers with instant bill payment confirmation.',
            'prepaid-recharge' => 'Recharge prepaid mobile numbers with fast and reliable API integration.',
            'testing' => 'Sandbox testing service for API validation and safe environment verification.',
        ];

        $defaultIcon = 'fa-solid fa-layer-group';
        $defaultDescription = 'Manage and request this service for your account.';
        $isAdmin = auth()->check() && auth()->user()->role_id == 1;
        $raiseRoute = 'service.request';
    @endphp



    <div class="row g-3">
        @forelse($services as $service)
            @php
                $key = $service->slug ?? \Illuminate\Support\Str::slug($service->service_name);
                $iconClass = $serviceIcons[$key] ?? $defaultIcon;

                $reqObj = $requestedServices->get($service->id);
                $status = $reqObj ? strtolower((string) $reqObj->status) : null;
            @endphp

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="svc-card">
                    <div class="svc-top">
                        <div class="svc-icon">
                            <i class="{{ $iconClass }}"></i>
                        </div>
                        <div>
                            <h5 class="svc-title">{{ $service->service_name }}</h5>
                        </div>

                    </div>
                    <p class="text-muted small mt-1">
                        {{ $serviceDescriptions[$key] ?? $defaultDescription }}
                    </p>
                    @if ($userKycStatus || $isAdmin)
                        @if ($reqObj && $status === 'approved')
                            <button class="btn btn-success btn-sm px-3">Activated</button>
                        @elseif ($reqObj && $status === 'pending')
                            <button class="btn btn-secondary btn-sm px-3" disabled>Requested</button>
                        @else
                            <form action="{{ route($raiseRoute) }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <button class="btn btn-primary btn-sm px-3" type="submit">Raise Request</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('admin_profile', ['user_id' => Auth::user()->id, 'is_kyc' => 'Yes']) }}"
                            class="text-danger small text-decoration-none">
                            Complete Profile
                        </a>
                    @endif

                </div>
            </div>

        @empty
            <div class="col-12">
                <div class="alert alert-warning mb-0">No active services found.</div>
            </div>
        @endforelse
    </div>

@endsection
