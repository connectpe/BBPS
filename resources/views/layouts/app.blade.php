<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'BBPS Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


    <!-- jQuery (must come BEFORE DataTables JS) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" crossorigin="anonymous"></script>

    <!-- Bootstrap 5 JS (already included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS for Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <!-- Buttons extension JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <!-- JSZip & pdfmake for Excel/PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Sweetalert  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart Cdn -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <!-- Quill Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-wrapper {
            flex: 1;
            transition: margin-left 0.3s ease;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            transition: all 0.3s ease;
            background: linear-gradient(to top, #445db8, #667eea);
        }

        .sidebar-active {
            background-color: #c3ccd8 !important;
        }


        @media (max-width: 768px) {
            /* .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1050;
                background-color: #0e1e5a;
                transform: translateX(0);
            } */

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1050;
                height: 100vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                /* smooth iOS scroll */
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
            }

            .main-wrapper {
                margin-left: 0 !important;
            }
        }


        /* Force DataTables pagination to stay right-aligned on small screens */
        .dataTables_wrapper .dataTables_paginate {
            float: right !important;
            /* always float right */
            text-align: right !important;
            margin-top: 10px;
            /* optional spacing */
        }

        /* Optional: info text on left */
        .dataTables_wrapper .dataTables_info {
            float: left !important;
            text-align: left !important;
            margin-top: 10px;
        }

        /* Ensure bottom container is flex for small screens */
        .dataTables_wrapper .dataTables_bottom {
            display: flex !important;
            justify-content: space-between;
            flex-wrap: wrap;
            /* wrap for very small screens */
        }

        .buttonColor {
            background-color: #3c5be4;
            color: white;
        }

        .buttonColor:hover {
            background-color: #3c5be4;
            color: white;
        }

        .cursor-pointer {
            cursor: pointer !important;
        }


        /* Allow the main wrapper to handle flex-overflow properly */
        .main-wrapper {
            min-width: 0;
            overflow-x: hidden;
        }

        /* Make every responsive wrapper actually scrollable */
        .table-responsive {
            width: 100% !important;
            overflow-x: auto !important;
            display: block !important;
        }

        /* Target ANY DataTables table globally */
        table.dataTable {
            width: 100% !important;
            min-width: 900px !important;
            /* Forces scrollbar on all tables */
            border-collapse: collapse !important;
        }

        /* Prevent text wrapping for all tables to keep them clean */
        table.dataTable th,
        table.dataTable td {
            white-space: nowrap !important;
            padding: 10px !important;
        }


        /* Styling for Firefox */
        .table-responsive {
            scrollbar-width: thin !important;
            /* Options: auto, thin, none */
            scrollbar-color: #4962c0 #dcdde0;
            /* handle color, track color */
        }

        /* Full Screen Loader Styles */
        /* Brand Loader Styles */
        .loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .brand-loader {
            display: flex;
            gap: 2px;
        }

        .letter {
            font-family: 'Inter', sans-serif;
            font-size: 3rem;
            /* Large font size */
            font-weight: 400;
            color: #445db8;
            display: inline-block;
            animation: letter-focus 1.8s infinite;
            /* The main animation */
        }

        /* Staggering the animation for each letter */
        .letter:nth-child(1) {
            animation-delay: 0.1s;
        }

        .letter:nth-child(2) {
            animation-delay: 0.2s;
        }

        .letter:nth-child(3) {
            animation-delay: 0.3s;
        }

        .letter:nth-child(4) {
            animation-delay: 0.4s;
        }

        .letter:nth-child(5) {
            animation-delay: 0.5s;
        }

        .letter:nth-child(6) {
            animation-delay: 0.6s;
        }

        .letter:nth-child(7) {
            animation-delay: 0.7s;
        }

        .letter:nth-child(8) {
            animation-delay: 0.8s;
            font-weight: 700;
        }

        /* Make 'P' naturally bolder */
        .letter:nth-child(9) {
            animation-delay: 0.9s;
        }

        @keyframes letter-focus {

            0%,
            100% {
                transform: scale(1);
                font-weight: 400;
                text-shadow: none;
            }

            50% {
                transform: scale(1.2);
                font-weight: 800;
                color: #3c5be4;
                text-shadow: 0 0 20px rgba(85, 105, 177, 0.5);
            }
        }

        /* Animating the dots at the end */
        .loader-dots span {
            font-size: 2rem;
            color: #445db8;
            animation: dots 1.5s infinite;
        }

        .loader-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loader-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes dots {

            0%,
            100% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }
        }

        .loader-hidden {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>

<body class="d-flex">

    <div id="page-loader" class="loader-wrapper">
        <div class="brand-loader">
            <span class="letter">C</span>
            <span class="letter">o</span>
            <span class="letter">n</span>
            <span class="letter">n</span>
            <span class="letter">e</span>
            <span class="letter">c</span>
            <span class="letter">t</span>
            <span class="letter">P</span>
            <span class="letter">e</span>
        </div>
        <div class="loader-dots">
            @for ($i = 0; $i < 5; $i++) <span>.</span>
                @endfor

        </div>
    </div>
    {{-- Sidebar --}}
    @include('layouts.sidebar')

    <div class="main-wrapper d-flex flex-column w-100">

        {{-- Header --}}
        @include('layouts.header')

        {{-- Main Content --}}
        <main class="flex-grow-1 p-4 bg-light">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="mb-0">@yield('page-title')</h2>
                <!-- Button placeholder, will be injected by child if exists -->
                <div class="ms-lg-0 ms-5">
                    @yield('page-button')
                </div>
            </div>

            @yield('content')
        </main>


        {{-- Footer --}}
        @include('layouts.footer')

    </div>

    @include('layouts.script')

    <!-- Global Notification -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
        <div id="globalToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="globalToastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script>
        window.notify = function (message, type = "danger") {

            let toastEl = document.getElementById('globalToast');
            let toastMsg = document.getElementById('globalToastMessage');

            // Fully reset classes safely
            toastEl.className = "toast align-items-center text-white border-0";

            // Add dynamic color
            toastEl.classList.add("bg-" + type);

            toastMsg.innerText = message;

            // Dispose old instance if exists
            let existingToast = bootstrap.Toast.getInstance(toastEl);
            if (existingToast) {
                existingToast.dispose();
            }

            let toast = new bootstrap.Toast(toastEl, {
                delay: 3000
            });

            toast.show();
        }
    </script>

    <script>
        window.addEventListener("load", function () {
            const loader = document.getElementById("page-loader");
            // Add the hidden class to fade it out
            loader.classList.add("loader-hidden");

            // Optional: Remove from DOM after transition for performance
            setTimeout(() => {
                loader.style.display = "none";
            }, 500);
        });

        // Also handle Laravel-specific actions (like form submissions)
        window.addEventListener("beforeunload", function () {
            document.getElementById("page-loader").classList.remove("loader-hidden");
            document.getElementById("page-loader").style.display = "flex";
        });
    </script>

    <script>
        $('#clearCacheBtn').on('click', function () {

            Swal.fire({
                title: 'Are you sure?',
                text: "All cache (Laravel + Redis) will be cleared!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('clear_cache') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {

                            if (res.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Done!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Optional reload
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);

                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    });

                }
            });
        });
    </script>


    <script>
        $(document).ready(function () {

            $('.blink-radio').on('click', function () {

                let baseAmount = parseFloat($(this).data('amount')) || 0;

                let gst = baseAmount * 0.18;
                let total = baseAmount + gst;

                // Optional: format display for user
                function formatINR(num) {
                    return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2 }).format(num);
                }

                $('#amount').text(formatINR(baseAmount));
                $('#gst').text(formatINR(gst));
                $('#total').text(formatINR(total));

                let modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();

            });

        });
    </script>
</body>

</html>