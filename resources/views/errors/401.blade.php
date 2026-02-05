@extends('layouts.app')

@section('title', 'Unauthorized Access')
@section('page-title', 'Unauthorized')

@section('content')
<div class="container">
    <div class="row align-items-center min-vh-100">

        <!-- Left Content -->
        <div class="col-md-6">
            <h1 class="fw-bold display-5 text-dark mb-3">
                401! Hold up!
            </h1>

            <p class="text-muted mb-4">
                Sorry, but you are not authorized to view this page.
                <br>Please check your permissions or contact support.
            </p>

            <div class="d-flex gap-3">
                <a href="{{ route('dashboard') }}"
                    class="btn px-4 text-white"
                    style="background:#6b83ec;">
                    Back To Home Page
                </a>

                <a href="{{ route('contact') ?? '#' }}"
                    class="btn btn-outline-secondary px-4">
                    Contact Us
                </a>
            </div>
        </div>

        <!-- Right Lottie Animation -->
        <div class="col-md-6 text-center">
            <div id="unauthorizedLottie" style="max-width:420px; margin:auto;"></div>
        </div>

    </div>
</div>

<!-- Lottie Script -->
<script src="https://unpkg.com/lottie-web@5.12.2/build/player/lottie.min.js"></script>
<script>
    lottie.loadAnimation({
        container: document.getElementById('unauthorizedLottie'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: 'https://assets9.lottiefiles.com/packages/lf20_jcikwtux.json'
        // Security / Unauthorized animation
    });
</script>
@endsection