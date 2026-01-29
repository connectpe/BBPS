@extends('layouts.app')

@section('title', 'Report')
@section('page-title', 'Report')

@section('content')

<style>
    .chart-canvas {
        width: 100%;
        max-width: 100%;
        height: 30px;
        cursor: pointer;
    }
</style>

<div class="accordion mb-3" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFilter">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                Filter Transactions
            </button>
        </h2>
        <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-3 align-items-end">


                    <div class="col-md-3">
                        <label for="filterreferenceId" class="form-label">ReferenceId</label>
                        <input type="text" class="form-control" id="filterreferenceId" placeholder="Enter ReferenceId">
                    </div>

                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
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
                        <select id="reportType" class="form-select">
                            <option value="recharge">Recharge</option>
                            <option value="bill">Bill</option>
                            <option value="utility">Utility</option>
                            <option value="banking">Banking</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="fromDate" class="form-label">From</label>
                        <input type="date" id="fromDate" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label for="fromDate" class="form-label">To</label>
                        <input type="date" id="toDate" class="form-control">
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


<div class="card">
    <div class="card-body d-flex justify-content-center">
        <div class="chart-wrapper" style="position: relative; width: 100%; max-width: 600px;">
            <canvas id="reportChart" class="chart-canvas"></canvas>
            <div id="customTooltip" style="position:absolute; background:#fff; border:1px solid #ccc; padding:10px; max-height:150px; overflow-y:auto; display:none;"></div>
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

<script>
    let chartInstance = null;

    $(document).ready(function() {

        $('#applyFilter').on('click', function() {

            let url = "{{url('fetch')}}/transactions/0"
            let payload = {};

            const reportType = $('#reportType').val();
            const fromDate = $('#fromDate').val();
            const toDate = $('#toDate').val();

            switch (reportType) {
                case 'recharge':
                    url = "{{url('fetch')}}/transactions/0";
                    payload = {
                        type: reportType,
                        from_date: fromDate,
                        to_date: toDate
                    };
                    break;

                case 'banking':
                    url = "{{url('fetch')}}/banking/0";
                    payload = {
                        type: reportType,
                        from_date: fromDate,
                        to_date: toDate
                    };
                    break;

                default:
                    console.warn('Unknown report type:', reportType);
                    break;
            }

            console.log(url, payload);

            $.ajax({
                url: url,
                type: "POST",
                dataType: "json",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    payload
                    // type: reportType,
                    // from_date: fromDate,
                    // to_date: toDate
                },
                success: function(response) {
                    console.log('Full response:', response);
                    renderChartByType('recharge', response.data);
                    // renderChartByType(reportType, response.data);
                }
            });
        });

        $('#resetFilter').on('click', function() {
            $('#reportType').val('');
            $('#fromDate').val('');
            $('#toDate').val('');
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
            status: item.status ?? 'unknown',
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

                            const listHTML = items.map(i =>
                                `<div>${i.reference} | ${i.date} | ${i.status}</div>`
                            ).join('');

                            tooltipEl.innerHTML = listHTML;
                            tooltipEl.style.display = 'block';
                            tooltipEl.style.left = tooltipModel.caretX + 'px';
                            tooltipEl.style.top = tooltipModel.caretY + 'px';
                        }
                    }
                },
                onClick: function(evt, elements) {
                    if (!elements.length) return;
                    const index = elements[0].index;
                    const label = this.data.labels[index];
                    const items = mapData[label];
                    openModalWithItems(items, columnsForModal);
                }
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
</script>

@endsection