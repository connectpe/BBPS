@extends('layouts.app')

@section('title', 'Payin API Documentation - User')
@section('page-title', 'Payin API Documentation - User')

@section('content')

<style>
.copy-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #4BB543;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
    z-index: 9999;
}
.copy-toast.show {
    opacity: 1;
    transform: translateY(0);
}
pre {
    white-space: pre-wrap;
    word-break: break-word;
    margin: 0;
    font-size: 13px;
}
</style>

<div class="card shadow-sm">
    <div class="card-body">

        <h4 class="fw-bold">PayIn API Documentation</h4>
        <p class="text-muted">
            Integrate seamless payment collection into your application with our PayIn API
        </p>

        {{-- Header --}}
        <div class="card mb-4 border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-bold">Header</h6>

                <pre class="bg-white p-3 border rounded" id="headerCode">
{{ $docs->request_header ?? '' }}
                </pre>

                <button class="btn btn-sm btn-primary mt-2" onclick="copyCode('headerCode')">
                    Copy Code
                </button>
            </div>
        </div>

        {{-- Authorization --}}
        <div class="card mb-4 border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-bold">Authentication</h6>

                <pre class="bg-white p-3 border rounded" id="authCode">
{!! $docs->authorization ?? '' !!}
                </pre>

                <button class="btn btn-sm btn-primary mt-2" onclick="copyCode('authCode')">
                    Copy Code
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <h6 class="fw-bold mb-3">API Endpoints</h6>

        <div class="mb-3">
            <button class="btn btn-primary me-2 tab-btn active" data-tab="generate">Generate Payment</button>
            <button class="btn btn-outline-secondary me-2 tab-btn" data-tab="status">Check Status</button>
            <button class="btn btn-outline-secondary tab-btn" data-tab="callback">Callback</button>
        </div>

        <div class="card bg-light border-0">
            <div class="card-body">

                {{-- Generate --}}
                <div class="tab-content active" id="generate">
                    <h6>// Response</h6>

                    <pre class="bg-white p-3 border rounded" id="genRes">
{!! $docs->generate_payment_response ?? '' !!}
                    </pre>

                    <button class="btn btn-sm btn-primary mt-2 mb-3" onclick="copyCode('genRes')">Copy Code</button>

                    <h6>Description</h6>
                    <p class="text-muted">
                        {!! $docs->generate_payment_description ?? '' !!}
                    </p>
                </div>

                {{-- Status --}}
                <div class="tab-content d-none" id="status">
                    <h6>// Response</h6>

                    <pre class="bg-white p-3 border rounded" id="statusRes">
{!! $docs->check_status_response ?? '' !!}
                    </pre>

                    <button class="btn btn-sm btn-primary mt-2 mb-3" onclick="copyCode('statusRes')">Copy Code</button>

                    <h6>Description</h6>
                    <p class="text-muted">
                        {!! $docs->check_status_description ?? '' !!}
                    </p>
                </div>

                {{-- Callback --}}
                <div class="tab-content d-none" id="callback">
                    <h6>// Response</h6>

                    <pre class="bg-white p-3 border rounded" id="callbackRes">
{!! $docs->callback_examples_response ?? '' !!}
                    </pre>

                    <button class="btn btn-sm btn-primary mt-2 mb-3" onclick="copyCode('callbackRes')">Copy Code</button>

                    <h6>Description</h6>
                    <p class="text-muted">
                        {!! $docs->callback_examples_description ?? '' !!}
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- Toast --}}
<div id="copyToast" class="copy-toast">
    ✔ Copied to clipboard
</div>

<script>
// Tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('btn-primary','active');
            b.classList.add('btn-outline-secondary');
        });

        this.classList.add('btn-primary','active');
        this.classList.remove('btn-outline-secondary');

        let tab = this.getAttribute('data-tab');

        document.querySelectorAll('.tab-content').forEach(c => {
            c.classList.add('d-none');
        });

        document.getElementById(tab).classList.remove('d-none');
    });
});

// Copy
function copyCode(id) {
    let text = document.getElementById(id).innerText.trim();
    navigator.clipboard.writeText(text);

    let toast = document.getElementById('copyToast');
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}
</script>

@endsection