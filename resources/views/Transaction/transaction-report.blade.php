@extends('layouts.app')

@section('title', 'Transactions')
@section('page-title', 'Transactions')

@section('content')

    {{-- FILTER SECTION --}}
    <div class="accordion mb-3" id="filterAccordion">
        {{-- <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#serviceModal">
    ADD
</button> --}}

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter">
                    Filter Transactions
                </button>
            </h2>


            <div id="collapseFilter" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">Reference Number</label>
                            <input type="text" id="filterReference" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">User ID</label>
                            <input type="text" id="filterUserId" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="filterStatus" class="form-select">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="processed">Processed</option>
                                <option value="failed">Failed</option>
                                <option value="reversed">Reversed</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn buttonColor" id="applyFilter">Filter</button>
                            <button class="btn btn-secondary" id="resetFilter">Reset</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body pt-4">
                <div class="table-responsive">
                    <table id="transactionTable" class="table table-striped table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>Operator ID</th>
                                <th>Circle ID</th>
                                <th>Amount</th>
                                <th>Transaction Type</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody></tbody> {{-- frontend only --}}
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- SCRIPT --}}
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#transactionTable')) {
                $('#transactionTable').DataTable().destroy();
            }

            $('#transactionTable').DataTable({

                serverSide: false,
                processing: false,

                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                responsive: true,

                dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",

                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Excel',
                        className: 'btn buttonColor btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        className: 'btn buttonColor btn-sm'
                    }
                ],

                language: {
                    searchPlaceholder: "Search transactions..."
                }

            });

        });
    </script>


@endsection
