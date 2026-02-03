@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="card shadow-sm mb-5">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0">List of Created Schemes</h5>
                <button class="btn btn-primary btn-sm btn-add-new" data-bs-toggle="modal" data-bs-target="#schemeModal">
                    Add New Scheme
                </button>
            </div>
            <div class="card-body">
                <table id="schemeTable" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>SCHEME NAME</th>
                            <th>STATUS</th>
                            <th>CREATED AT</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td class="text-primary scheme-name-text">Mahendra Pratap Singh</td>
                            <td><span class="badge bg-dark text-white">Assigned</span></td>
                            <td>2025-06-03 15:15:40</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-scheme" data-bs-toggle="modal"
                                    data-bs-target="#schemeModal">
                                    <i class="fas fa-list"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <hr>

        <div class="card shadow-sm mt-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0">Scheme and User Relations</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                    Assign Scheme to User
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label small font-weight-bold">User:</label>
                        <select class="form-control form-select">
                            <option value="">-- Select user --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small font-weight-bold">Scheme Name:</label>
                        <select class="form-control form-select">
                            <option value="">-- Select --</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-primary me-2 px-4">Search</button>
                        <button class="btn btn-warning text-white px-4">Reset</button>
                    </div>
                </div>

                <table id="relationTable" class="table table-bordered table-striped w-100">
                    <thead class="bg-light">
                        <tr>
                            <th>S.N.</th>
                            <th>USER NAME</th>
                            <th>EMAIL</th>
                            <th>SCHEME NAME</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>VAIBHAV SHRIMAL</td>
                            <td>vaibhav@quantamcraft.com</td>
                            <td>QUANTAMCRAFT PRIVATE LIMITED</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="schemeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add & Update Scheme Rules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="schemeForm">
                    <div class="modal-body">
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Scheme Name:</label>
                            <input type="text" name="scheme_name" id="scheme_name" class="form-control"
                                placeholder="Enter scheme name here" required>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center" id="rulesTable">
                                <thead class="bg-light text-uppercase" style="font-size: 11px;">
                                    <tr>
                                        <th style="min-width: 140px;">SERVICE</th>
                                        <th style="min-width: 100px;">PRODUCT</th>
                                        <th>FEE TYPE</th>
                                        <th>STATUS</th>
                                        <th>START VALUE</th>
                                        <th>END VALUE</th>
                                        <th>FEE</th>
                                        <th>MIN FEE</th>
                                        <th>MAX FEE</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <button type="button" id="addMoreRules" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-plus"></i> Add More Rules
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Scheme to User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignUserForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Select User <span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select" required>
                                    <option value="">-- Select --</option>
                                    <option value="1">Demo - demo.1@gmail.com</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold">Select Scheme <span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select" required>
                                    <option value="">-- Select --</option>
                                    <option value="101">APPHANY TECHNOLOGIES PVT LTD</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark btn-sm px-4" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#schemeTable').DataTable({
                dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +  "<'row'<'col-12'tr>>" +"<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
                buttons: [{extend: 'excelHtml5',text: 'Excel',className: 'btn buttonColor btn-sm'},
                    {extend: 'pdfHtml5',text: 'PDF',className: 'btn buttonColor btn-sm'}],
            });
            $('#relationTable').DataTable({
                dom: "<'row mb-2'<'col-sm-4'l><'col-sm-4'f><'col-sm-4 text-end'B>>" +"<'row'<'col-12'tr>>" +"<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
                buttons: [{ extend: 'excelHtml5',text: 'Excel',className: 'btn buttonColor btn-sm'},
                    {extend: 'pdfHtml5',text: 'PDF',className: 'btn buttonColor btn-sm'}],
            });

            function getRowHtml(data = {}) {
                return `
                <tr>
                    <td>
                        <select name="service[]" class="form-control form-control-sm">
                            <option value="">--Select--</option>
                            <option value="recharge" ${data.service == 'recharge' ? 'selected' : ''}>Recharge</option>
                        </select>
                    </td>
                    <td><input type="text" name="product[]" class="form-control form-control-sm" value="${data.product || '--'}"></td>
                    <td><select name="fee_type[]" class="form-control form-control-sm"><option value="fixed">Fixed</option><option value="percentage">Percentage</option></select></td>
                    <td><select name="status[]" class="form-control form-control-sm"><option value="active">Active</option><option value="inactive">Inactive</option></select></td>
                    <td><input type="number" name="start_val[]" class="form-control form-control-sm" placeholder="Start Val"></td>
                    <td><input type="number" name="end_val[]" class="form-control form-control-sm" placeholder="End Val"></td>
                    <td><input type="number" name="fee[]" class="form-control form-control-sm" placeholder="Fee"></td>
                    <td><input type="number" name="min_fee[]" class="form-control form-control-sm" placeholder="Min Fee"></td>
                    <td><input type="number" name="max_fee[]" class="form-control form-control-sm" placeholder="Max Fee"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-times"></i></button></td>
                </tr>`;
            }

            $('.btn-add-new').click(function() {
                $('#modalTitle').text('Add New Scheme Rules');
                $('#schemeForm')[0].reset();
                $('#rulesTable tbody').empty();
            });

            $(document).on('click', '.edit-scheme', function() {
                $('#modalTitle').text('Update Scheme Rules');
                $('#rulesTable tbody').empty();
                let name = $(this).closest('tr').find('.scheme-name-text').text();
                $('#scheme_name').val(name);
                $('#rulesTable tbody').append(getRowHtml({
                    service: 'recharge',
                    product: 'Airtel'
                }));
            });

            $('#addMoreRules').click(function() {
                $('#rulesTable tbody').append(getRowHtml());
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
@endsection
