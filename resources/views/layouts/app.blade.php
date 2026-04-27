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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-wrapper {
            flex: 1;
            transition: margin-left 0.3s ease;
            min-width: 0;
            overflow-x: hidden;
        }

        .select2Width {
            width: 150px !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: right !important;
            text-align: right !important;
            margin-top: 10px;
        }

        .dataTables_wrapper .dataTables_info {
            float: left !important;
            text-align: left !important;
            margin-top: 10px;
        }

        .dataTables_wrapper .dataTables_bottom {
            display: flex !important;
            justify-content: space-between;
            flex-wrap: wrap;
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

        .table-responsive {
            width: 100% !important;
            overflow-x: auto !important;
            display: block !important;
            scrollbar-width: thin !important;
            scrollbar-color: #4962c0 #dcdde0;
        }

        table.dataTable {
            width: 100% !important;
            min-width: 900px !important;
            border-collapse: collapse !important;
        }

        table.dataTable th,
        table.dataTable td {
            white-space: nowrap !important;
            padding: 10px !important;
        }

        /* Loader */
        .loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loader-hidden {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>

<body class="d-flex">

    @include('layouts.sidebar')

    <div class="main-wrapper d-flex flex-column">
        @include('layouts.header')

        <main class="flex-grow-1 p-3 bg-light">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="mb-0">@yield('page-title')</h2>
                <div class="ms-lg-0 ms-5">
                    @yield('page-button')
                </div>
            </div>

            @yield('content')
        </main>

        @include('layouts.footer')
    </div>

    @include('layouts.script')

</body>
</html>