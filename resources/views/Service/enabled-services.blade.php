@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter Users
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="filterName" class="form-label">Service</label>
                        <select name="service" id="service" class="form-control">
                            <option value="">--Selecte Service--</option>
                            @foreach($services as $service)
                            <option value="{{$service->id}}">{{$service?->service?->service_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filterName" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">--Selecte Status--</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn buttonColor " id="applyFilter"> Filter</button>
                        <button class="btn btn-secondary" id="resetFilter">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-md-10 col-lg-12">
    <div class="card shadow-sm">

        <div class="card-body pt-4">
            <!-- Table -->
            <div class="table-responsive">
                <table id="serviceTable" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Created at</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        var table = $('#serviceTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{url('fetch')}}/serviceRequest/0",
                type: 'POST',

                data: function(d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.service_id = $('#service').val();
                    d.status = $('#status').val();
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
                searchPlaceholder: "Search Services..."
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'service.service_name',
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return formatDateTime(data);
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        const color = {
                            pending: 'warning',
                            approved: 'success',
                            rejected: 'danger',
                        }

                        status = formatStatus(data)
                        return `<span class="fw-bold text-${color[data]}">${status}</span>`
                    }
                }
            ]
        });

        // Apply filter
        $('#applyFilter').on('click', function() {
            table.ajax.reload();
        });

        // Reset filter
        $('#resetFilter').on('click', function() {
            $('#service').val('');
            $('#status').val('');
            table.ajax.reload();
        });
    });
</script>

@endsection