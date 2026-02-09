@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="row g-4">

    @php
    use Illuminate\Support\Facades\Auth;
    @endphp

    <!-- Card 2 : Our Services -->
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Our Services</h6>

                <div class="row">
                    {{-- <div class="row g-2 align-items-end">


                        <div class="col-md-3">
                            <label for="filterreferenceId" class="form-label">ReferenceId</label>
                            <input type="text" class="form-control" id="filterreferenceId" placeholder="Enter ReferenceId">
                        </div>

                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select class="form-select form-select2" id="filterStatus">
                                <option value="">--select--</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="processed">Processed</option>
                                <option value="failed">Failed</option>
                                <option value="reversed">Reversed</option>
                            </select>
                        </div>



                        <div class="col-md-3">
                            <label for="reportType" class="form-label">Transaction Type</label>
                            <select id="reportType" class="form-select form-select2">
                                <option value="">--Select Transaction Type--</option>
                                <option value="recharge">Recharge</option>
                                <option value="bill">Bill</option>
                                <option value="utility">Utility</option>
                                <option value="banking">Banking</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="fromDate" class="form-label">From</label>
                            <input type="date" id="fromDate" name="fromDate" class="form-control" value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-3">
                    <label for="toDate" class="form-label">To</label>
                    <input type="date" id="toDate" name="toDate" class="form-control" value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn buttonColor " id="applyFilter"> Filter</button>
                    <button class="btn btn-secondary" id="resetFilter">Reset</button>
                </div>
            </div> --}}
        </div>

        <div class="row g-3 text-center">

            @php
            $services = [
            ['name' => 'Bill Pay', 'icon' => 'bi-receipt'],
            ['name' => 'Cash Collection', 'icon' => 'bi-cash-stack'],
            ['name' => 'Digital Wallet', 'icon' => 'bi-wallet2'],
            ['name' => 'DTH Recharge', 'icon' => 'bi-tv'],
            ['name' => 'OTT', 'icon' => 'bi-play-btn'],
            ['name' => 'OTH Recharge', 'icon' => 'bi-phone'],
            ];

            $colors = ['#f94144','#f3722c','#f8961e','#f9c74f','#90be6d','#43aa8b','#577590','#277da1','#9d4edd','#ff6d00','#1982c4','#6a4c93'];
            @endphp

            @foreach($services as $service)
            @php
            $randColor = $colors[array_rand($colors)];
            @endphp

            <div class="col-6">
                <div class="border rounded p-2 h-100 service-box bg-light">
                    <i class="bi {{ $service['icon'] }} fs-4" style="color: {{ $randColor }}"></i>
                    <a href="{{ route('utility_service') }}"
                        class="text-decoration-none text-dark small fw-semibold mt-1 d-block text-center">
                        {{ $service['name'] }}
                    </a>
                </div>
            </div>
            @endforeach

            <!-- <div class="chart-wrapper mt-4" style="position: relative; width: 100%; max-width: 250px; margin: 0 auto;">
                        <canvas id="reportChart" class="chart-canvas"></canvas>
                        <div id="customTooltip" style="position:absolute; background:#fff; border:1px solid #ccc; padding:10px; max-height:100px; overflow-y:auto; display:none;"></div>
                    </div> -->
        </div>
    </div>
</div>
</div>

<!-- Card 3 : Welcome + User Details + Actions -->
<div class="col-md-4">
    <div class="card shadow-sm h-100 position-relative text-white"
        style="background: linear-gradient(to top, #6b83ec, #485050);">

        <div class="card-body d-flex flex-column align-items-center justify-content-between py-4">
            @php
            $user = Auth::user();
            @endphp
            <!-- Top Spacer -->
            <div></div>

            <!-- Heading -->
            <h5 class="fw-bold mb-4 text-center">Welcome <strong class="text-success">{{$user->name}}</strong></h5>

            <!-- User Profile Image -->
            <img src="{{asset('assets\image\user.jpg')}}" alt="User Profile"
                class="rounded-circle mb-3"
                style="width:75px; height:75px; object-fit:cover;">

            <!-- Profile Details -->

            <div class="row g-2 small w-100" style="max-width: 300px;">
                <div class="col-6 opacity-75">User Type</div>
                <div class="col-6 fw-semibold">E-mail</div>

                <div class="col-6 opacity-75">User</div>
                <div class="col-6 fw-semibold">{{$user->email}}</div>

                <div class="col-6 opacity-75">Entity Type</div>
                <div class="col-6 fw-semibold">Contact</div>

                <div class="col-6 opacity-75">Individual</div>
                <div class="col-6 fw-semibold">{{$user->email ?? ''}}</div>
            </div>
            <!-- Bottom Spacer -->
            <div></div>
        </div>
    </div>
</div>
</div>

<!-- Row: Date & Text Inputs + Search Button -->
<div class="row g-2 my-4 align-items-end">

    <!-- From Date -->
    <div class="col-md-2">
        <label class="form-label small">From Date</label>
        <input type="date" class="form-control"
            value="{{ now()->format('Y-m-d') }}"
            max="{{ now()->format('Y-m-d') }}">
    </div>

    <!-- To Date -->
    <div class="col-md-2">
        <label class="form-label small">To Date</label>
        <input type="date" class="form-control"
            value="{{ now()->format('Y-m-d') }}"
            max="{{ now()->format('Y-m-d') }}">
    </div>


    <!-- Category Select -->
    <div class="col-md-3">
        <label class="form-label small">Category</label>
        <select class="form-select form-select2">
            <option selected disabled>--Select Category--</option>
            <option value="billing">Billing</option>
            <option value="recharge">Recharge</option>
            <option value="wallet">Digital Wallet</option>
            <option value="others">Others</option>
        </select>
    </div>

    <!-- Service Select -->
    <div class="col-md-3">
        <label class="form-label small">Service</label>
        <select class="form-select form-select2">
            <option selected disabled>--Select Service--</option>
            <option value="billpay">Bill Pay</option>
            <option value="cashcollection">Cash Collection</option>
            <option value="dth">DTH Recharge</option>
            <option value="ott">OTT</option>
        </select>
    </div>

    <!-- Search Button -->
    <div class="col-md-2">
        <button type="button" class="btn font-semibold text-white rounded hover:opacity-90 transition">
            Search
        </button>
    </div>
</div>



<!-- Row: Transaction Cards -->
<div class="row g-3 mt-4">

    <!-- Card 1: Success vs Failure Transaction -->
    <div class="col-md-6">
        <div class="card shadow-sm p-3" style="height:400px;">
            <h5 class="card-title fw-bold">Success Vs Failure Transaction</h5>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <p class="text-muted">No data found</p>
            </div>
        </div>
    </div>

    <!-- Card 2: Success Transaction By Service Wise -->
    <div class="col-md-6">
        <div class="card shadow-sm p-3" style="height:400px;">
            <h5 class="card-title fw-bold">Success Transaction By Service Wise</h5>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <p class="text-muted">No data found</p>
            </div>
        </div>
    </div>
</div>

<!-- Reusable chart details modal -->
<div class="modal fade" id="chartDetailModal" tabindex="-1" aria-labelledby="chartDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chartDetailModalLabel">Chart Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="chartDetailTable">
                        <thead>

                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    let chartInstance = null;

    $(document).ready(function() {

        filterTransaction();

        $('#applyFilter').on('click', function() {
            filterTransaction();
        });

        $('#resetFilter').on('click', function() {
            $('#fromDate').val('{{now()->toDateString()}}');
            $('#toDate').val('{{now()->toDateString()}}');
            $('#filterreferenceId').val('');
            $('#filterStatus').val('');
            filterTransaction();
        });
    });

    function renderChartByType(type, data) {
        if (chartInstance) chartInstance.destroy();

        switch (type) {
            case 'recharge':
                chartInstance = rechargeChart(data);
                break;
            case 'bill':
                chartInstance = billChart(data);
                break;
            case 'utility':
                chartInstance = utilityChart(data);
                break;
            case 'banking':
                chartInstance = bankingChart(data);
                break;
            default:
                chartInstance = rechargeChart(data);
                break;
        }
    }

    function preprocessChartData(data) {
        if (!Array.isArray(data)) {
            console.error('Expected array, got:', data);
            return [];
        }

        return data.map(item => ({
            reference: item.reference_number ?? 'N/A',
            date: item.created_at ? formatDateTime(item.created_at) : '-',
            status: formatStatus(item.status) ?? 'unknown',
            service_name: item.service?.service_name ?? 'Unknown',
            is_api_enable: item.is_api_enable ?? '0',
            is_active: item.is_active ?? '0',
            amount: item.amount ?? '0',
            ...item
        }));
    }

    // ------------------- Chart Functions -------------------

    function createChartWithTooltip(labels, mapData, backgroundColors, columnsForModal) {
        return new Chart(document.getElementById('reportChart'), {
            // type: labels.length > 3 ? 'pie' : 'doughnut', // optional, you can choose type
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: labels.map(label => mapData[label].length),
                    backgroundColor: backgroundColors
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        enabled: false, // disable default tooltip
                        external: function(context) {
                            const tooltipModel = context.tooltip;
                            const tooltipEl = document.getElementById('customTooltip');
                            if (!tooltipModel.opacity) {
                                tooltipEl.style.display = 'none';
                                return;
                            }

                            const label = tooltipModel.dataPoints[0].label;
                            const items = mapData[label];

                            // const listHTML = items.map(i =>
                            //     `<div>${i.reference} | ${i.date} | ${formatStatus(i.status)}</div>`
                            // ).join('');
                            // tooltipEl.innerHTML = listHTML;

                            const count = items.length;
                            tooltipEl.innerHTML = `<div>${label}: ${count} </div>`;

                            tooltipEl.style.display = 'block';
                            tooltipEl.style.left = tooltipModel.caretX + 'px';
                            tooltipEl.style.top = tooltipModel.caretY + 'px';
                        }
                    }
                },
                // onClick: function(evt, elements) {
                //     if (!elements.length) return;
                //     const index = elements[0].index;
                //     const label = this.data.labels[index];
                //     const items = mapData[label];
                //     openModalWithItems(items, columnsForModal);
                // }
            }
        });
    }

    function rechargeChart(rawData) {
        const data = preprocessChartData(rawData);

        const statusMap = {};
        data.forEach(item => {
            if (!statusMap[item.status]) statusMap[item.status] = [];
            statusMap[item.status].push(item);
        });


        const labels = Object.keys(statusMap);
        const columns = [{
                key: 'reference',
                label: 'Reference ID'
            },
            {
                key: 'date',
                label: 'Date'
            },
            {
                key: 'status',
                label: 'Status'
            },
            {
                key: 'amount',
                label: 'Amount'
            }
        ];

        return createChartWithTooltip(labels, statusMap, ['#FFA500', '#007BFF', '#28A745', '#DC3545', '#6C757D'], columns);
    }

    function billChart(rawData) {
        const data = preprocessChartData(rawData);

        const serviceMap = {};
        data.forEach(item => {
            const name = item.service_name;
            if (!serviceMap[name]) serviceMap[name] = [];
            serviceMap[name].push(item);
        });

        const labels = Object.keys(serviceMap);
        const columns = [{
                key: 'reference',
                label: 'Reference ID'
            },
            {
                key: 'date',
                label: 'Date'
            },
            {
                key: 'status',
                label: 'Status'
            },
            {
                key: 'service_name',
                label: 'Service Name'
            },
            {
                key: 'amount',
                label: 'Amount'
            }
        ];

        return createChartWithTooltip(labels, serviceMap, ['#0d6efd', '#6610f2', '#20c997', '#ffc107', '#dc3545'], columns);
    }

    function utilityChart(rawData) {
        const data = preprocessChartData(rawData);

        const apiMap = {
            'API Enabled': [],
            'API Disabled': []
        };
        data.forEach(item => {
            if (item.is_api_enable == "1") apiMap['API Enabled'].push(item);
            else apiMap['API Disabled'].push(item);
        });

        const labels = Object.keys(apiMap);
        const columns = [{
                key: 'reference',
                label: 'Reference ID'
            },
            {
                key: 'date',
                label: 'Date'
            },
            {
                key: 'status',
                label: 'Status'
            },
            {
                key: 'is_api_enable',
                label: 'API Enabled'
            },
            {
                key: 'amount',
                label: 'Amount'
            }
        ];

        return createChartWithTooltip(labels, apiMap, ['#198754', '#dc3545'], columns);
    }

    function bankingChart(rawData) {
        const data = preprocessChartData(rawData);

        const activeMap = {
            'Active': [],
            'Inactive': []
        };

        data.forEach(item => {
            if (item.is_active == "1") activeMap['Active'].push(item);
            else activeMap['Inactive'].push(item);
        });

        const labels = Object.keys(activeMap);
        const columns = [{
                key: 'reference',
                label: 'Reference ID'
            },
            {
                key: 'date',
                label: 'Date'
            },
            {
                key: 'status',
                label: 'Status'
            },
            {
                key: 'is_active',
                label: 'Active'
            },
            {
                key: 'amount',
                label: 'Amount'
            }
        ];

        return createChartWithTooltip(labels, activeMap, ['#0dcaf0', '#adb5bd'], columns);
    }

    // ------------------- Modal Function -------------------

    function openModalWithItems(items, columns = []) {
        if (!items || !items.length) return;

        const table = document.getElementById('chartDetailTable');
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');

        thead.innerHTML = '';
        tbody.innerHTML = '';

        // Header
        const headerRow = document.createElement('tr');
        columns.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col.label ?? col.key;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);

        // Body
        items.forEach(item => {
            const row = document.createElement('tr');
            columns.forEach(col => {
                const td = document.createElement('td');
                td.textContent = item[col.key] ?? '-';
                row.appendChild(td);
            });
            tbody.appendChild(row);
        });

        const modal = new bootstrap.Modal(document.getElementById('chartDetailModal'));
        modal.show();
    }

    function filterTransaction() {
        let url = "{{url('fetch')}}/transactions/0"
        let payload = {};

        const reportType = $('#reportType').val();
        const fromDate = $('#fromDate').val();
        const toDate = $('#toDate').val();
        const referenceNumber = $('#filterreferenceId').val();
        const status = $('#filterStatus').val();

        switch (reportType) {
            case 'recharge':
                url = "{{url('fetch')}}/transactions/0";
                break;
            case 'banking':
                url = "{{url('fetch')}}/banking/0";
                break;
            default:
                console.warn('Unknown report type:', reportType);
                break;
        }
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                type: reportType,
                date_from: fromDate,
                date_to: toDate,
                reference_number: referenceNumber,
                status: status
            },
            success: function(response) {
                console.log('Full response:', response);
                // renderChartByType('recharge', response.data);
                renderChartByType(reportType, response.data);
            }
        });
    }
</script>


@endsection