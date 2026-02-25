@extends('layouts.app')

@section('title', 'Payout Transaction')
@section('page-title', 'Payout Transaction')

<style>
    table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
        display: none !important;
    }

    .dt-plus-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0d6efd;
        color: #fff;
        font-weight: 900;
        font-size: 16px;
        cursor: pointer;
    }

    tr.shown .dt-plus-btn {
        background: #0b5ed7;
    }

    .child-wrap {
        padding: 10px;
    }

    .child-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid rgba(0, 0, 0, .10);
        border-radius: 10px;
        background: #fff;
    }

    .child-table th,
    .child-table td {
        padding: 10px 12px;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        font-size: 14px;
    }

    .child-table th {
        width: 180px;
        background: #f8fafc;
        font-weight: 800;
    }

    .child-table td {
        font-weight: 600;
    }

    .table.dataTable td.dt-control:before {
        display: none !important;
    }
</style>

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Filters --}}
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
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label">ConnectPe ID</label>
                        <input type="text" class="form-control" id="filterConnectpeId" placeholder="Enter ConnectPe ID">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Transaction No</label>
                        <input type="text" class="form-control" id="filterTransactionNo" placeholder="Enter Transaction No">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Client Txn ID</label>
                        <input type="text" class="form-control" id="filterClientTxnId" placeholder="Enter Client Txn ID">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">UTR No</label>
                        <input type="text" class="form-control" id="filterUtrNo" placeholder="Enter UTR No">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Mode</label>
                        <input type="text" class="form-control" id="filterMode" placeholder="Enter Mode">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">--select--</option>
                            <option value="queued">Queued</option>
                            <option value="processing">Processing</option>
                            <option value="processed">Processed</option>
                            <option value="failed">Failed</option>
                            <option value="reversed">Reversed</option>
                            <option value="hold">Hold</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Created Date (From)</label>
                        <input type="date" class="form-control" id="filterCreatedFrom">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Created Date (To)</label>
                        <input type="date" class="form-control" id="filterCreatedTo">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn buttonColor" id="applyFilter">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <button class="btn btn-secondary" id="resetFilter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="payoutTable" class="table table-striped table-bordered w-100 align-middle">
                <thead>
                <tr>
                    <th style="width:55px;">#</th>
                    <th>ConnectPe ID</th>
                    <th>Transaction No</th>
                    <th>Client Txn Id</th>
                    <th>UTR No</th>
                    <th>Mode</th>
                    <th>Amount</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    function safe(v) {
        return (v === null || v === undefined || v === '') ? '----' : v;
    }

    function yesNo(v) {
        return String(v) === '1' ? 'YES' : 'NO';
    }

    function statusBadge(status) {
        if (!status) return '----';
        var color = 'secondary';
        if (status === 'processed') color = 'success';
        if (status === 'failed') color = 'danger';
        if (status === 'processing') color = 'warning';
        if (status === 'queued') color = 'primary';
        return '<span class="badge bg-' + color + ' text-uppercase">' + status + '</span>';
    }

    function formatRowDetails(d) {
        var html = '';
        html += '<div class="child-wrap">';
        html += '<table class="child-table"><tbody>';

        html += '<tr>';
        html += '<th>User Name</th><td>' + safe(d.user ? d.user.name : "") + '</td>';
        html += '<th>Service Name</th><td>' + safe(d.service ? d.service.service_name : "") + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Provider Name</th><td>' + safe(d.provider ? d.provider.provider_name : "") + '</td>';
        html += '<th>Updated By</th><td>' + safe(d.updatedBy ? d.updatedBy.name : "") + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>ConnectPe ID</th><td>' + safe(d.connectpe_id) + '</td>';
        html += '<th>Transaction No</th><td>' + safe(d.transaction_no) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Client Txn ID</th><td>' + safe(d.client_txn_id) + '</td>';
        html += '<th>UTR No</th><td>' + safe(d.utr_no) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Amount</th><td>' + safe(d.amount) + '</td>';
        html += '<th>Total Amount</th><td>' + safe(d.total_amount) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Fee</th><td>' + safe(d.fee) + '</td>';
        html += '<th>Tax</th><td>' + safe(d.tax) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Status</th><td>' + statusBadge(d.status) + '</td>';
        html += '<th>Status Code</th><td>' + safe(d.status_code) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Mode</th><td>' + safe(d.mode) + '</td>';
        html += '<th>Currency</th><td>' + safe(d.currency) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>API Call</th><td>' + yesNo(d.is_api_call) + '</td>';
        html += '<th>Cron</th><td>' + yesNo(d.is_cron) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Cron Date</th><td>' + safe(d.cron_date) + '</td>';
        html += '<th>Fee Type</th><td>' + safe(d.fee_type) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Purpose</th><td colspan="3">' + safe(d.purpose) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Remark</th><td colspan="3">' + safe(d.remark) + '</td>';
        html += '</tr>';

        html += '<tr>';
        html += '<th>Failed Message</th><td colspan="3">' + safe(d.failed_msg) + '</td>';
        html += '</tr>';

        html += '</tbody></table></div>';
        return html;
    }

    var table = $('#payoutTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        ajax: {
            url: "{{ url('fetch/orders/0/all') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function(d) {
                d.connectpe_id = $('#filterConnectpeId').val();
                d.transaction_no = $('#filterTransactionNo').val();
                d.client_txn_id = $('#filterClientTxnId').val();
                d.utr_no = $('#filterUtrNo').val();
                d.mode = $('#filterMode').val();
                d.status = $('#filterStatus').val();
                d.date_from = $('#filterCreatedFrom').val();
                d.date_to = $('#filterCreatedTo').val();
            }
        },
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                searchable: false,
                data: null,
                render: function() {
                    return '<span class="dt-plus-btn">+</span>';
                }
            },
            { data: 'connectpe_id' },
            { data: 'transaction_no' },
            { data: 'client_txn_id' },
            { data: 'utr_no' },
            { data: 'mode' },
            { data: 'amount' },
            { data: 'total_amount' },
            { data: 'status', render: function(d){ return statusBadge(d); } },
            { data: 'created_at' }
        ]
    });

    $('#payoutTable tbody').on('click', 'td.dt-control', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('.dt-plus-btn').text('+');
        } else {
            row.child(formatRowDetails(row.data())).show();
            tr.addClass('shown');
            $(this).find('.dt-plus-btn').text('−');
        }
    });

    $('#applyFilter').on('click', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $('#resetFilter').on('click', function(e) {
        e.preventDefault();
        $('#filterConnectpeId').val('');
        $('#filterTransactionNo').val('');
        $('#filterClientTxnId').val('');
        $('#filterUtrNo').val('');
        $('#filterMode').val('');
        $('#filterStatus').val('');
        $('#filterCreatedFrom').val('');
        $('#filterCreatedTo').val('');
        table.ajax.reload();
    });

    $('#filterCreatedFrom').on('change', function () {
        var from = $(this).val();
        $('#filterCreatedTo').attr('min', from);
        if ($('#filterCreatedTo').val() && $('#filterCreatedTo').val() < from) {
            $('#filterCreatedTo').val('');
        }
    });

});
</script>

@endsection