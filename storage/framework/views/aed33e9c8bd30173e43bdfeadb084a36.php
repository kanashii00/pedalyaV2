

<?php $__env->startSection('title', 'Reports'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Reports</h1>
    <p>Generate and export system reports</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
<button type="button" class="btn-admin btn-admin--secondary btn-admin--sm" onclick="clearResults()">
    <i class="bi bi-x-lg me-1"></i>Clear Results
</button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Report Type Tabs -->
<ul class="nav nav-tabs" id="reportTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="rental-tab" data-bs-toggle="tab" href="#rental" role="tab">
            <i class="bi bi-bicycle me-1"></i>Rental Report
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="revenue-tab" data-bs-toggle="tab" href="#revenue" role="tab">
            <i class="bi bi-currency-dollar me-1"></i>Revenue Report
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="incident-tab" data-bs-toggle="tab" href="#incident" role="tab">
            <i class="bi bi-exclamation-triangle me-1"></i>Incidents Report
        </a>
    </li>
</ul>

<div class="tab-content mb-4" id="reportTabContent">
    <!-- Rental Report Tab -->
    <div class="tab-pane fade show active" id="rental" role="tabpanel">
        <div class="admin-card mt-3">
            <div class="admin-card__body">
                <form action="<?php echo e(route('admin.reports.rental')); ?>" method="POST" id="rentalReportForm" onsubmit="return generateReport(event, this)">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="rentalDateFrom" name="date_from"
                                value="<?php echo e(old('date_from', now()->subDays(30)->format('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="rentalDateTo" name="date_to"
                                value="<?php echo e(old('date_to', now()->format('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="rentalStatus" name="status">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bicycle</label>
                            <select class="form-select" id="rentalBicycle" name="bicycle_id">
                                <option value="">All</option>
                                <?php $__currentLoopData = $bicycles ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bicycle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($bicycle->id); ?>"><?php echo e($bicycle->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rider</label>
                            <select class="form-select" id="rentalRider" name="user_id">
                                <option value="">All</option>
                                <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn-admin btn-admin--primary">
                            <i class="bi bi-bar-chart me-1"></i>Generate Report
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportReport('rental', 'pdf')">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportReport('rental', 'excel')">
                                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Revenue Report Tab -->
    <div class="tab-pane fade" id="revenue" role="tabpanel">
        <div class="admin-card mt-3">
            <div class="admin-card__body">
                <form action="<?php echo e(route('admin.reports.revenue')); ?>" method="POST" id="revenueReportForm" onsubmit="return generateReport(event, this)">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="revenueDateFrom" name="date_from"
                                value="<?php echo e(old('date_from', now()->subDays(30)->format('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="revenueDateTo" name="date_to"
                                value="<?php echo e(old('date_to', now()->format('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Group By</label>
                            <select class="form-select" id="revenueGroupBy" name="group_by" required>
                                <option value="day">Day</option>
                                <option value="week">Week</option>
                                <option value="month" selected>Month</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn-admin btn-admin--primary">
                            <i class="bi bi-graph-up me-1"></i>Generate Report
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportReport('revenue', 'pdf')">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportReport('revenue', 'excel')">
                                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Incident Report Tab -->
    <div class="tab-pane fade" id="incident" role="tabpanel">
        <div class="admin-card mt-3">
            <div class="admin-card__body">
                <form action="<?php echo e(route('admin.reports.incident')); ?>" method="POST" id="incidentReportForm" onsubmit="return generateReport(event, this)">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="incidentDateFrom" name="date_from"
                                value="<?php echo e(old('date_from', now()->subDays(30)->format('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="incidentDateTo" name="date_to"
                                value="<?php echo e(old('date_to', now()->format('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select class="form-select" id="incidentType" name="incident_type">
                                <option value="">All</option>
                                <option value="accident">Accident</option>
                                <option value="theft">Theft</option>
                                <option value="vandalism">Vandalism</option>
                                <option value="malfunction">Malfunction</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Severity</label>
                            <select class="form-select" id="incidentSeverity" name="severity">
                                <option value="">All</option>
                                <option value="minor">Minor</option>
                                <option value="moderate">Moderate</option>
                                <option value="major">Major</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn-admin btn-admin--primary">
                            <i class="bi bi-exclamation-circle me-1"></i>Generate Report
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportReport('incident', 'pdf')">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportReport('incident', 'excel')">
                                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Results Display Area -->
<div class="admin-card mb-4" id="resultsCard" style="display: none;">
    <div class="admin-card__head">
        <div class="admin-card__title"><i class="bi bi-pie-chart me-2"></i>Report Results</div>
        <div class="admin-card__tools"><span class="badge-admin badge-admin--neutral" id="resultCount">0</span></div>
    </div>
    <div class="admin-card__body">
        <div id="reportResults">
            <div class="text-center text-muted py-5">
                <i class="bi bi-bar-chart" style="font-size:3rem;"></i>
                <p class="mt-3">Generate a report to see results here.</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const _token = document.querySelector('meta[name="csrf-token"]')?.content || '<?php echo e(csrf_token()); ?>';

function generateReport(event, form) {
    event.preventDefault();
    const url = form.getAttribute('action');
    const formData = new FormData(form);
    const resultsDiv = document.getElementById('reportResults');
    const resultsCard = document.getElementById('resultsCard');
    const resultCount = document.getElementById('resultCount');

    resultsCard.style.display = 'block';
    resultsDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-pedalya" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Generating report...</p>
        </div>
    `;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': _token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        const records = data.data || data.rentals || data.accidents || [];
        if (records.length > 0) {
            resultCount.textContent = records.length + ' records';
            resultsDiv.innerHTML = buildReportTable(records);
        } else if (data.summary) {
            resultCount.textContent = 'Summary';
            resultsDiv.innerHTML = buildSummaryReport(data);
        } else {
            resultCount.textContent = '0 records';
            resultsDiv.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox" style="font-size:3rem;"></i>
                    <p class="mt-3">No data found for the selected criteria.</p>
                </div>
            `;
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = `
            <div class="alert alert-pedalya alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Error generating report: ${error.message}
            </div>
        `;
    });

    return false;
}

function buildReportTable(data) {
    if (!data || data.length === 0) return '';
    let headers = Object.keys(data[0]);
    let html = '<div class="table-responsive"><table class="table admin-table">';
    html += '<thead><tr>';
    headers.forEach(h => {
        html += '<th>' + h.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</th>';
    });
    html += '</tr></thead><tbody>';
    data.forEach(row => {
        html += '<tr>';
        headers.forEach(h => {
            let val = row[h];
            if (typeof val === 'object' && val !== null) val = JSON.stringify(val);
            if (val === null || val === undefined) val = '-';
            html += '<td>' + val + '</td>';
        });
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
}

function buildSummaryReport(data) {
    let html = '<div class="row g-3">';
    if (data.summary) {
        Object.entries(data.summary).forEach(([key, value]) => {
            html += `
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value" style="font-size:1.4rem;">${typeof value === 'number' ? value.toLocaleString() : value}</div>
                        <div class="stat-label">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
                    </div>
                </div>
            `;
        });
    }
    html += '</div>';
    const records = data.data || data.rentals || data.accidents || [];
    if (records.length > 0) {
        html += buildReportTable(records);
    }
    return html;
}

function exportReport(type, format) {
    var form = document.getElementById(type + 'ReportForm');
    var params = new URLSearchParams();
    params.append('type', type);

    var fields = form.querySelectorAll('input, select');
    fields.forEach(function (field) {
        if (field.name && field.value) {
            params.append(field.name, field.value);
        }
    });

    var url = <?php echo json_encode(route('admin.reports.export.pdf'), 15, 512) ?> + '?' + params.toString();
    url = url.replace('/export/pdf', '/export/' + format);

    window.location.href = url;
}

function clearResults() {
    const resultsDiv = document.getElementById('reportResults');
    const resultsCard = document.getElementById('resultsCard');
    const resultCount = document.getElementById('resultCount');
    resultsCard.style.display = 'none';
    resultsDiv.innerHTML = '';
    resultCount.textContent = '0';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\reports.blade.php ENDPATH**/ ?>