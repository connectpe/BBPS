@extends('layouts.app')

@section('title', 'Complaint Report')
@section('page-title', 'Complaint Report')



@section('content')
    <div class="accordion mb-3" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter">
                    Filter Complaints
                </button>
            </h2>

            <div id="collapseFilter" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">Reference No</label>
                            <input type="text" id="filterReference" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">User Name</label>
                            <input type="text" id="filterUser" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Priority</label>
                            <select id="filterPriority" class="form-select">
                                <option value="">All</option>
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="filterStatus" class="form-select">
                                <option value="">All</option>
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <div class="col-md-12 d-flex gap-2 mt-2">
                            <button class="btn buttonColor" id="applyFilter">Filter</button>
                            <button class="btn btn-secondary" id="resetFilter">Reset</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body pt-4">
                <div class="table-responsive">
                    <table id="complaintTable" class="table table-striped table-bordered table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Reference No</th>
                                <th>User Name</th>
                                <th>Service Name</th>
                                <th>Transaction ID</th>
                                <th>Complaint Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>COMP-1001</td>
                                <td>John Doe</td>
                                <td>DMT</td>
                                <td>TXN123456</td>
                                <td>Transaction Failed</td>
                                <td><span class="badge bg-danger">High</span></td>
                                <td><span class="badge bg-warning">Open</span></td>
                                <td>
                                    <a href="javascript:void(0)" class="text-dark view-description"
                                        data-description="Transaction failed but amount debited from customer account. Please resolve urgently.">
                                       <i class="fa-regular fa-eye"></i>
                                    </a>
                                </td>
                                <td>22-01-2026</td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td>COMP-1002</td>
                                <td>Rahul Sharma</td>
                                <td>AEPS</td>
                                <td>TXN789654</td>
                                <td>Amount Debited</td>
                                <td><span class="badge bg-warning">Medium</span></td>
                                <td><span class="badge bg-info">In Progress</span></td>
                                <td>
                                    <a href="javascript:void(0)" class="text-dark view-description"
                                        data-description="AEPS amount debited but cash not received by customer.">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                </td>
                                <td>21-01-2026</td>
                            </tr>
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="descriptionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p id="descriptionText" class="mb-0"></p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>




    <script>
        $(document).ready(function() {

            let table = $('#complaintTable').DataTable({
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
                    searchPlaceholder: "Search complaints..."
                }
            });
            $('#applyFilter').on('click', function() {
                table.column(1).search($('#filterReference').val());
                table.column(2).search($('#filterUser').val());
                table.column(6).search($('#filterPriority').val());
                table.column(7).search($('#filterStatus').val());
                table.draw();
            });

            $('#resetFilter').on('click', function() {
                $('#filterReference, #filterUser').val('');
                $('#filterPriority, #filterStatus').val('');
                table.search('').columns().search('').draw();
            });


            $(document).on('click', '.view-description', function() {
                let description = $(this).data('description');
                $('#descriptionText').text(description);
                $('#descriptionModal').modal('show');
            });

        });
    </script>


@endsection
