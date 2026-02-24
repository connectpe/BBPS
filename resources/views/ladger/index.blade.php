@extends('layouts.app')

@section('title', 'Ledger')
@section('page-title', 'Ledger')

@section('content')

@php
$role = Auth::user()->role_id;
@endphp

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter"
            data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-2 align-items-end">
                    @if($role == 1)
                    <div class="col-md-3">
                        <label for="filterUser" class="form-label">User</label>
                        <select name="filterUser" id="filterUser" class="form-control form-select2">
                            <option value="">--Select User--</option>
                            @foreach($users as $value)
                            <option value="{{$value->id}}">{{$value->name}} ({{ $value->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif


                    <div class="col-md-3">
                        <label for="referenceNo" class="form-label">Reference No</label>
                        <input type="text" class="form-control" id="referenceNo" placeholder="Reference No">
                    </div>
                    <div class="col-md-3">
                        <label for="requestId" class="form-label">Request Id</label>
                        <input type="text" class="form-control" id="requestId" placeholder="Request Id">
                    </div>
                    <div class="col-md-3">
                        <label for="connectPeId" class="form-label">ConnectPe Id</label>
                        <input type="text" class="form-control" id="connectPeId" placeholder="ConnectPe Id">
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>

                    <div class="col-md-3">
                        <label for="filterDateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <!-- Buttons aligned with input fields -->
                        <button class="btn buttonColor " id="applyFilter"> Filter</button>

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
                <table id="usersTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            @if($role == 1)
                            <th>Organization Name</th>
                            @else
                            <th> </th>
                            @endif
                            <th>Service</th>
                            <th>Reference No.</th>
                            <th>Request Id</th>
                            <th>ConnectPe Id</th>
                            <th>Txn Amt.</th>
                            <th>Total Txn Amt.</th>
                            <th>Txn Type</th>
                            <th>Opening Balance</th>
                            <th>Closing Balance</th>
                            <th>Date</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let role = "{{$role}}";
        var table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            columnDefs: [{
                targets: 1,
                visible: role == 1
            }],
            ajax: {
                url: "{{url('fetch')}}/ledger/0",
                type: 'POST',
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.user_id = $('#filterUser').val();
                    d.reference_no = $('#referenceNo').val();
                    d.request_id = $('#requestId').val();
                    d.connectpe_id = $('#connectPeId').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },

            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
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
                searchPlaceholder: "Search ledgers..."
            },

            columns: [{
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    let url = "{{ route('view_user', ['id' => 'id']) }}".replace('id', row.user_id);
                    const userName = row?.user?.name;
                    const businessName = row?.user?.business?.business_name;

                    return `
                                <a href="${url}" class="text-primary fw-semibold text-decoration-none">
                                    ${userName ?? '----'} <br/>
                                    [${businessName ?? '----'}]
                                </a>
                            `;
                }
            },

            {
                data: 'service.service_name',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'reference_no',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'request_id',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'connectpe_id',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'txn_amount',
                render: function (data) {
                    const amount = new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: 'INR',
                    }).format(data || 0);

                    return amount;
                }
            },

            {
                data: 'total_txn_amount',
                render: function (data) {
                    const amount = new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: 'INR',
                    }).format(data || 0);

                    return amount;
                }
            },
            {
                data: 'txn_type',
                render: function (data) {
                    return data || '----'
                }
            },
            {
                data: 'opening_balance',
                render: function (data) {
                    const amount = new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: 'INR',
                    }).format(data || 0);

                    return amount;
                }
            },
            {
                data: 'closing_balanace',
                render: function (data) {
                    const amount = new Intl.NumberFormat('en-IN', {
                        style: 'currency',
                        currency: 'INR',
                    }).format(data || 0);

                    return amount;
                }
            },
            {
                data: 'txn_date',
                render: function (data) {
                    return formatDateTime(data)
                }
            },
            {
                data: 'remarks',
                render: function (data) {
                    return `<i class="fas fa-eye cursor-pointer viewModalBtn"  data-title="Remark" data-content='${data}'></i>`;
                }
            },
            ]
        });
        $('#filterDateFrom').on('change', function () {
            let from = $(this).val();
            $('#filterDateTo').attr('min', from);
            if ($('#filterDateTo').val() && $('#filterDateTo').val() < from) {
                $('#filterDateTo').val('');
            }
        });


        // Apply filter
        $('#applyFilter').on('click', function () {
            table.ajax.reload();
        });

        // Reset filter
        $('#resetFilter').on('click', function () {
            $('#filterUser').val('').trigger('change');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#filterDateTo').val('').removeAttr('min');
            $('#referenceNo').val('');
            $('#requestId').val('');
            $('#connectPeId').val('');
            table.ajax.reload();
        });
    });


    function changeStatusDropdown(selectElement, id) {
        const newStatus = selectElement.value;
        const prevStatus = selectElement.getAttribute('data-prev');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to change the status?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (!result.isConfirmed) {
                selectElement.value = prevStatus;
                return;
            }

            $.ajax({
                url: "{{ route('admin.user_status.change') }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: newStatus,
                    id: id
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message ?? 'Status updated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    // update previous value after success
                    selectElement.setAttribute('data-prev', newStatus);
                },
                error: function (xhr) {
                    // rollback on error
                    selectElement.value = prevStatus;

                    let message = 'Something went wrong!';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                        message = xhr.responseJSON.errors[firstKey][0];
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });
    }
</script>

@endsection