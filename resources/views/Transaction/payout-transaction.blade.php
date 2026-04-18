@extends('layouts.app')

@section('title', 'Payout Transaction')
@section('page-title', 'Payout Transaction')

@section('content')

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

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFilter">
                Filter
            </button>
        </h2>

        <div id="collapseFilter" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">

                    @if (auth()->user()->role_id == 1)
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select class="form-select form-select2" id="filterUserId">
                            <option value="">--Select User--</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select form-select2" id="filterStatus">
                            <option value="">--select status--</option>
                            <option value="queued">Queued</option>
                            <option value="pending">Pending</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                            <option value="reversed">Reversed</option>
                        </select>
                    </div>

                    @if (auth()->user()->role_id == 1)
                    <div class="col-md-3">
                        <label class="form-label">Provider</label>
                        <select class="form-select form-select2" id="filterProviderId">
                            <option value="">--Select Provider--</option>
                            @foreach ($providers as $p)
                                <option value="{{ $p->id }}">{{ $p->provider_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label">Any Key</label>
                        <input type="text" class="form-control" id="filterAnyKey"
                            placeholder="ConnectPe / Order Ref ID / Client Ref ID">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">UTR No</label>
                        <input type="text" class="form-control" id="filterUtrNo" placeholder="Enter UTR No">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterCreatedFrom">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterCreatedTo">
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

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="payoutTable" class="table table-striped table-bordered w-100 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sr. No</th>
                        <th>User Name</th>
                        <th>ConnectPe ID</th>
                        <th>Order Ref ID</th>
                        <th>Client Ref ID</th>
                        <th>UTR No</th>
                        <th>Mode</th>
                        @if (auth()->user()->role_id == 1)
                            <th>Provider Name</th>
                        @endif
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    const isAdmin = {{ auth()->user()->role_id == 1 ? 'true' : 'false' }};

    function safe(v) {
        return (v === null || v === undefined || v === '') ? '----' : v;
    }

    function statusBadge(status) {
        if (!status) return '----';
        var color = 'secondary';
        if (status === 'success') color = 'success';
        if (status === 'failed') color = 'danger';
        if (status === 'pending') color = 'warning';
        if (status === 'queued') color = 'primary';
        return '<span class="badge bg-' + color + ' text-uppercase">' + status + '</span>';
    }

    function formatRowDetails(d) {
        return `
            <div class="child-wrap">
                <table class="child-table">
                    <tbody>
                        <tr><th>Total Amount</th><td>${safe(d.total_amount)}</td><th>Remark</th><td>${safe(d.remark)}</td></tr>
                        <tr><th>Fee</th><td>${safe(d.fee)}</td><th>Tax</th><td>${safe(d.tax)}</td></tr>
                        <tr><th>Purpose</th><td>${safe(d.purpose)}</td><th>Failed Message</th><td>${safe(d.failed_message)}</td></tr>
                    </tbody>
                </table>
            </div>
        `;
    }

    var columns = isAdmin ? [
        { data: null, className:'dt-control', orderable:false, searchable:false, render:()=>'<span class="dt-plus-btn buttonColor">+</span>' },
        { data: null, render:(data,type,row,meta)=>meta.row + meta.settings._iDisplayStart + 1 },
        { data: null, render:function(row){
            let url = "{{ route('view_user', ':id') }}".replace(':id', row.user_id);
            return '<a href="'+url+'" class="text-primary fw-semibold text-decoration-none">'+(row?.user?.name || '----')+'<br/>['+(row?.user?.email || '----')+']</a>';
        }},
        { data: 'connectpe_id' },
        { data: 'order_ref_id' },
        { data: 'client_ref_id' },
        { data: 'utr_no' },
        { data: 'mode' },
        { data: 'provider', render:data => data ? data.provider_name : '----' },
        { data: 'amount' },
        { data: 'status', render:d => statusBadge(d) },
        { data: 'created_at', render:data => formatDateTime(data) },
        {
            data:null,
            orderable:false,
            searchable:false,
            render:function(data,type,row){
                if(row.status === 'success'){
                    let url = "{{ route('download_payout_invoice', ':id') }}".replace(':id', row.id);
                    return `<a href="${url}" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Invoice</a>`;
                }
                return `<span class="text-muted">----</span>`;
            }
        }
    ] : [
        { data: null, className:'dt-control', orderable:false, searchable:false, render:()=>'<span class="dt-plus-btn buttonColor">+</span>' },
        { data: null, render:(data,type,row,meta)=>meta.row + meta.settings._iDisplayStart + 1 },
        { data: null, render:function(row){
            let url = "{{ route('view_user', ':id') }}".replace(':id', row.user_id);
            return '<a href="'+url+'" class="text-primary fw-semibold text-decoration-none">'+(row?.user?.name || '----')+'<br/>['+(row?.user?.email || '----')+']</a>';
        }},
        { data: 'connectpe_id' },
        { data: 'order_ref_id' },
        { data: 'client_ref_id' },
        { data: 'utr_no' },
        { data: 'mode' },
        { data: 'amount' },
        { data: 'status', render:d => statusBadge(d) },
        { data: 'created_at', render:data => formatDateTime(data) },
        {
            data:null,
            orderable:false,
            searchable:false,
            render:function(data,type,row){
                if(row.status === 'success'){
                    let url = "{{ route('download_payout_invoice', ':id') }}".replace(':id', row.id);
                    return `<a href="${url}" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Invoice</a>`;
                }
                return `<span class="text-muted">----</span>`;
            }
        }
    ];

    var table = $('#payoutTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:{
            url:"{{ url('fetch/orders/0/all') }}",
            type:"POST",
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data:function(d){
                d.utr_no = $('#filterUtrNo').val();
                d.status = $('#filterStatus').val();
                d.date_from = $('#filterCreatedFrom').val();
                d.date_to = $('#filterCreatedTo').val();
                d.any_key = $('#filterAnyKey').val();

                if(isAdmin){
                    d.user_id = $('#filterUserId').val();
                    d.provider_id = $('#filterProviderId').val();
                }
            }
        },
        columns:columns
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

    $('#applyFilter').on('click', function(e){
        e.preventDefault();
        table.ajax.reload();
    });

    $('#resetFilter').on('click', function(e){
        e.preventDefault();

        $('#filterAnyKey,#filterUtrNo,#filterCreatedFrom,#filterCreatedTo').val('');
        $('#filterStatus').val('').trigger('change');

        if(isAdmin){
            $('#filterUserId,#filterProviderId').val('').trigger('change');
        }

        table.ajax.reload();
    });

});
</script>

@endsection