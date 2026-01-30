@extends('layouts.app')

@section('title', 'Ledger')
@section('page-title', 'Ledger')

@section('content')

    {{-- FILTER SECTION (Same as Transactions style) --}}
    <div class="accordion mb-3" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter">
                    Filter Ledger
                </button>
            </h2>

            <div id="collapseFilter" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-4">
                            <label class="form-label">Order Reference ID</label>
                            <input type="text" id="filterReference" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">User Name</label>
                            <input type="text" id="filterUser" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Order ID</label>
                            <input type="text" id="filterOrderId" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" id="filterDateFrom" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" id="filterDateTo" class="form-control">
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
                    <table id="ladgerTable" class="table table-striped table-bordered table-hover w-100">

                        <thead class="table-light">
                            <tr>
                                <th>Id</th>
                                <th>Order Reference ID</th>
                                <th>User Name</th>
                                <th>Order Id</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Narration</th>
                                <th>Opening</th>
                                <th>Closing</th>
                                <th>Remark</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>REF12345</td>
                                <td>John Doe</td>
                                <td>9876543210</td>
                                <td>₹500</td>
                                <td>22-01-2026</td>
                                <td>
                                    <span class="badge bg-success">Credit</span>
                                </td>
                                <td>Wallet Topup</td>
                                <td>₹1,000</td>
                                <td>₹1,500</td>
                                <td>Success</td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        $(document).ready(function() {
            function parseDMY(dateStr) {
                if (!dateStr) return null;
                const parts = dateStr.trim().split('-');
                if (parts.length !== 3) return null;

                const dd = parseInt(parts[0], 10);
                const mm = parseInt(parts[1], 10) - 1;
                const yyyy = parseInt(parts[2], 10);

                const d = new Date(yyyy, mm, dd);
                return isNaN(d.getTime()) ? null : d;
            }

            let ledgerDateFilterFn = null;

            let table = $('#ladgerTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                responsive: true,

                dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +
                     "<'row'<'col-12'tr>>" +
                     "<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",

                buttons: [
                    {
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
                    searchPlaceholder: "Search ledger..."
                }
            });


            $('#applyFilter').on('click', function () {
                table.column(1).search($('#filterReference').val());
                table.column(2).search($('#filterUser').val());
                table.column(3).search($('#filterOrderId').val());

                const dateFromVal = $('#filterDateFrom').val();
                const dateToVal   = $('#filterDateTo').val(); 

                const fromDate = dateFromVal ? new Date(dateFromVal) : null;
                const toDate   = dateToVal ? new Date(dateToVal) : null;
                if (toDate) toDate.setHours(23, 59, 59, 999);
                if (ledgerDateFilterFn) {
                    const idx = $.fn.dataTable.ext.search.indexOf(ledgerDateFilterFn);
                    if (idx !== -1) $.fn.dataTable.ext.search.splice(idx, 1);
                }

                ledgerDateFilterFn = function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'ladgerTable') return true;

                    const dateCell = data[5];
                    const cellDate = parseDMY(dateCell);

                    if (!cellDate) return true;

                    if (fromDate && cellDate < fromDate) return false;
                    if (toDate && cellDate > toDate) return false;

                    return true;
                };

                $.fn.dataTable.ext.search.push(ledgerDateFilterFn);

                table.draw();
            });
            $('#resetFilter').on('click', function () {
                $('#filterReference').val('');
                $('#filterUser').val('');
                $('#filterOrderId').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                if (ledgerDateFilterFn) {
                    const idx = $.fn.dataTable.ext.search.indexOf(ledgerDateFilterFn);
                    if (idx !== -1) $.fn.dataTable.ext.search.splice(idx, 1);
                    ledgerDateFilterFn = null;
                }

                table.search('').columns().search('').draw();
            });

        });
    </script>

@endsection
