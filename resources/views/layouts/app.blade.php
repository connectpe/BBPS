<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'BBPS Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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


    <!-- Buttons extension JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <!-- JSZip & pdfmake for Excel/PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- Sweetalert  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            transition: all 0.3s ease;
            background: linear-gradient(to top, #445db8, #667eea);
        }


        .main-wrapper {
            flex: 1;
            transition: margin-left 0.3s ease;
        }

        .sidebar-active {
            background-color: #c3ccd8 !important;
        }


        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1050;
                background-color: #0e1e5a;
                transform: translateX(0);
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
    </style>
</head>

<body class="d-flex">

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    <div class="main-wrapper d-flex flex-column w-100">

        {{-- Header --}}
        @include('layouts.header')

        {{-- Main Content --}}
        <main class="flex-grow-1 p-4 bg-light">
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('layouts.footer')

    </div>

    @include('layouts.script')

</body>

</html>