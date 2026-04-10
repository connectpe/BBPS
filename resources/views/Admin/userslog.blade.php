@extends('layouts.app')

@section('title', 'Users Log')
@section('page-title', 'Users Log')

@section('content')

    {{-- FILTER --}}
    <div class="accordion mb-3" id="filterAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter">
                    Filter
                </button>
            </h2>

            <div id="collapseFilter" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="row g-3 align-items-end">

                        {{-- User Filter --}}
                        <div class="col-md-4">
                            <label class="form-label">User</label>
                            <select id="filterUser" class="form-select form-select2">
                                <option value="">--Select User--</option>
                                @foreach ($users as $log)
                                    @if ($log->user)
                                        <option value="{{ $log->user->id }}">
                                            {{ $log->user->name }} ({{ $log->user->email }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="filterDateFrom" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="filterDateFrom">
                        </div>

                        <div class="col-md-3">
                            <label for="filterDateTo" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="filterDateTo">
                        </div>

                        {{-- Buttons --}}
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn buttonColor w-100" id="applyFilter">Filter</button>
                            <button class="btn btn-secondary w-100" id="resetFilter">Reset</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table id="usersLogTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Logged At</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- VIEW MODAL --}}
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="modalContent" style="word-break: break-all;"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        $(document).ready(function() {

            let table = $('#usersLogTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[5, 'desc']],
                ajax: {
                    url: "{{ url('fetch') }}/users-log/0",
                    type: "POST",
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.user_id = $('#filterUser').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                    }
                },

                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],

                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {
                            return `
                        <strong>${row?.user?.name ?? '----'}</strong><br>
                        <small>${row?.user?.email ?? ''}</small>
                    `;
                        }
                    },
                    {
                        data: 'action',
                        render: function(data) {
                            return data ?? '----';
                        }
                    },
                    {
                        data: 'ip_address',
                        render: function(data) {
                            return data ?? '----';
                        }
                    },
                    {

                        data: 'user_agent',
                        orderable: true,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function(data) {
                            if (!data) return '----';

                            let safeData = data
                                .replace(/&/g, '&amp;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#39;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;');

                            return `
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fas fa-eye text-dark cursor-pointer viewModalBtn" title="Click to view full user agent" data-title="User Agent"
                                data-content="${safeData}">
                                </i>
                            </div>
                            `;

                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            return formatDateTime(data);
                        }
                    }
                ]
            });

            $('#applyFilter').click(function() {
                table.ajax.reload();
            });

            $('#filterDateFrom').on('change', function() {
                let from = $(this).val();
                $('#filterDateTo').attr('min', from);
                if ($('#filterDateTo').val() && $('#filterDateTo').val() < from) {
                    $('#filterDateTo').val('');
                }
            });
            $('#resetFilter').click(function() {
                $('#filterUser').val('').trigger('change');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.ajax.reload();
            });

            $(document).off('click', '.viewModalBtn').on('click', '.viewModalBtn', function() {
                $('#modalTitle').text($(this).data('title'));
                $('#modalContent').text($(this).data('content'));
                $('#viewModal').modal('show');
            });

        });
    </script>

@endsection
