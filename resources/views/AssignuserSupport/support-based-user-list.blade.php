@extends('layouts.app')
@section('title', 'Support Based User')
@section('page-title')
Support Based User ( {{ $support->name ?? '' }} )
@endsection

@section('page-button')
<div class="row align-items-center mb-2">
    <div class="col-auto ms-auto">
        <a href="{{route('support_details')}}" class="btn buttonColor"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="supportTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Organization Name</th>
                        <th>Email</th>
                        <th>PanNO.</th>
                        <th>Aadhar NO.</th>
                        <th>Bussiness Wallet</th>
                        <th>Created at</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>




<script>
    $(document).ready(function() {
        const supportId = "{{$support->id}}";
        var table = $('#supportTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {

                url: "{{url('fetch')}}/support-based-user-list/0",
                type: 'POST',

                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.assined_to = supportId;
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
                searchPlaceholder: "Search users..."
            },

            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.settings._iDisplayStart + meta.row + 1;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let url = "{{ route('view_user', ['id' => 'id']) }}".replace('id', row.user?.id);
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
                    data: 'user.email'
                },
                {
                    data: 'user.business.pan_number'
                },
                {
                    data: 'user.business.aadhar_number'
                },
                {
                    data: 'user.transaction_amount'
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return formatDateTime(data)
                    }
                },
                {
                    data: 'user.status',
                    render: function(data, type, row) {

                        const statusOptions = {
                            0: {
                                label: 'INITIATED',
                                class: 'bg-secondary'
                            },
                            1: {
                                label: 'ACTIVE',
                                class: 'bg-success'
                            },
                            2: {
                                label: 'INACTIVE',
                                class: 'bg-dark'
                            },
                            3: {
                                label: 'PENDING',
                                class: 'bg-warning text-dark'
                            },
                            4: {
                                label: 'SUSPENDED',
                                class: 'bg-danger'
                            }
                        };

                        let status = statusOptions[data] || {
                            label: 'UNKNOWN',
                            class: 'bg-light text-dark'
                        };

                        return `<span class="badge ${status.class}">
                    ${status.label}
                </span>`;
                    },
                    orderable: false,
                    searchable: false
                }


            ]

        });
        $('#filterDateFrom').on('change', function() {
            let from = $(this).val();
            $('#filterDateTo').attr('min', from);
            if ($('#filterDateTo').val() && $('#filterDateTo').val() < from) {
                $('#filterDateTo').val('');
            }
        });

        // Apply filter
        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        // Reset filter
        $('#resetFilter').on('click', function() {
            $('#filterName').val('').trigger('change');
            $('#filterEmail').val('');
            $('#filterStatus').val('').trigger('change');
            $('#filterDateTo').val('').removeAttr('min');
            table.ajax.reload();
        });
    });
</script>
@endsection