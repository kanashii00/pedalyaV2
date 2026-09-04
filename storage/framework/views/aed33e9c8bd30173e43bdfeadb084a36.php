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
<ul class="nav nav-tabs" id="reportTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="customer-tab" data-bs-toggle="tab" href="#customer" role="tab">
            <i class="bi bi-people me-1"></i>Customer Report
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="rental-tab" data-bs-toggle="tab" href="#rental" role="tab">
            <i class="bi bi-bicycle me-1"></i>Rental Report
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="accident-tab" data-bs-toggle="tab" href="#accident" role="tab">
            <i class="bi bi-activity me-1"></i>Accident Report
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
    <!-- Customer Report Tab -->
    <div class="tab-pane fade show active" id="customer" role="tabpanel">
        <div class="admin-card mt-3">
            <div class="admin-card__body">
                <form action="<?php echo e(route('admin.reports.customer')); ?>" method="POST" id="customerReportForm" onsubmit="return generateReport(event, this)">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="customerSearch" name="search"
                                placeholder="Name, email, phone, student ID...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="customerDateFrom" name="date_from">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="customerDateTo" name="date_to">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="customerStatus" name="status">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                                <option value="blacklisted">Blacklisted</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Verification</label>
                            <select class="form-select" id="customerVerified" name="verified">
                                <option value="">All</option>
                                <option value="1">Verified</option>
                                <option value="0">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm w-100" onclick="clearCustomerFilters()" title="Clear Filters">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn-admin btn-admin--primary">
                            <i class="bi bi-bar-chart me-1"></i>Generate Report
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printReport('customer')">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportReport('customer', 'pdf')">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportReport('customer', 'excel')">
                                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rental Report Tab -->
    <div class="tab-pane fade" id="rental" role="tabpanel">
        <div class="admin-card mt-3">
            <div class="admin-card__body">
                <form action="<?php echo e(route('admin.reports.rental')); ?>" method="POST" id="rentalReportForm" onsubmit="return generateReport(event, this)">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="rentalSearch" name="search"
                                placeholder="Rental ID, rider, bicycle...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="rentalDateFrom" name="date_from"
                                value="<?php echo e(now()->subDays(30)->format('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="rentalDateTo" name="date_to"
                                value="<?php echo e(now()->format('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rental Status</label>
                            <select class="form-select" id="rentalStatus" name="status">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="returned">Returned</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="expired">Expired</option>
                                <option value="overdue">Overdue</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" id="rentalPaymentStatus" name="payment_status">
                                <option value="">All</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="refunded">Refunded</option>
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
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-2">
                            <label class="form-label">Rider</label>
                            <select class="form-select" id="rentalRider" name="user_id">
                                <option value="">All</option>
                                <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm w-100" onclick="clearRentalFilters()" title="Clear Filters">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Clear Filters
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn-admin btn-admin--primary">
                            <i class="bi bi-bar-chart me-1"></i>Generate Report
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printReport('rental')">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
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

    <!-- Accident Report Tab -->
    <div class="tab-pane fade" id="accident" role="tabpanel">
        <div class="admin-card mt-3">
            <div class="admin-card__body">
                <form action="<?php echo e(route('admin.reports.accident')); ?>" method="POST" id="accidentReportForm" onsubmit="return generateReport(event, this)">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="accidentSearch" name="search"
                                placeholder="Description, action taken...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="accidentDateFrom" name="date_from"
                                value="<?php echo e(now()->subDays(30)->format('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="accidentDateTo" name="date_to"
                                value="<?php echo e(now()->format('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Severity</label>
                            <select class="form-select" id="accidentSeverity" name="severity">
                                <option value="">All</option>
                                <option value="minor">Minor</option>
                                <option value="moderate">Moderate</option>
                                <option value="major">Major</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="accidentStatus" name="status">
                                <option value="">All</option>
                                <option value="open">Open</option>
                                <option value="resolved">Resolved</option>
                                <option value="in_progress">In Progress</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bicycle</label>
                            <select class="form-select" id="accidentBicycle" name="bicycle_id">
                                <option value="">All</option>
                                <?php $__currentLoopData = $bicycles ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bicycle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($bicycle->id); ?>"><?php echo e($bicycle->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-2">
                            <label class="form-label">Rider</label>
                            <select class="form-select" id="accidentRider" name="user_id">
                                <option value="">All</option>
                                <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select class="form-select" id="accidentType" name="incident_type">
                                <option value="">All</option>
                                <option value="accident">Accident</option>
                                <option value="impact_detected">Impact Detected</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn-admin btn-admin--ghost btn-admin--sm w-100" onclick="clearAccidentFilters()" title="Clear Filters">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Clear Filters
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn-admin btn-admin--primary">
                            <i class="bi bi-bar-chart me-1"></i>Generate Report
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printReport('accident')">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportReport('accident', 'pdf')">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportReport('accident', 'excel')">
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

<!-- Customer Details Modal -->
<div class="modal fade" id="customerDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius); border: none;">
            <div class="modal-header" style="background: var(--brand); color: #fff; border-radius: var(--radius) var(--radius) 0 0;">
                <h6 class="modal-title fw-semibold"><i class="bi bi-person-circle me-2"></i>Customer Details</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerDetailBody"></div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-subtle);">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Rental Details Modal -->
<div class="modal fade" id="rentalDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius); border: none;">
            <div class="modal-header" style="background: var(--brand); color: #fff; border-radius: var(--radius) var(--radius) 0 0;">
                <h6 class="modal-title fw-semibold"><i class="bi bi-bicycle me-2"></i>Rental Details</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="rentalDetailBody"></div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-subtle);">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Accident Details Modal -->
<div class="modal fade" id="accidentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius); border: none;">
            <div class="modal-header" style="background: var(--brand); color: #fff; border-radius: var(--radius) var(--radius) 0 0;">
                <h6 class="modal-title fw-semibold"><i class="bi bi-activity me-2"></i>Accident Details</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="accidentDetailBody"></div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-subtle);">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
var _token = document.querySelector('meta[name="csrf-token"]')?.content || '<?php echo e(csrf_token()); ?>';
var _baseUrl = '<?php echo e(url("/admin/reports/export/pdf")); ?>'.replace('/export/pdf', '');
var _reportData = { customer: [], rental: [], accident: [] };
var _reportPage = { customer: 1, rental: 1, accident: 1 };
var _pageSize = 10;

/* ─── Generate Report ─── */
function generateReport(event, form) {
    event.preventDefault();
    var url = form.getAttribute('action');
    var formData = new FormData(form);
    var resultsDiv = document.getElementById('reportResults');
    var resultsCard = document.getElementById('resultsCard');
    var resultCount = document.getElementById('resultCount');
    var type = detectReportType(form);

    resultsCard.style.display = 'block';
    resultsDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border" style="color:var(--brand);" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Generating report...</p></div>';

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': _token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) {
        if (!r.ok) {
            return r.json().catch(function () { return null; }).then(function (body) {
                var msg = (body && body.message) ? body.message : 'Server error (' + r.status + ')';
                throw new Error(msg);
            });
        }
        return r.json();
    })
    .then(function (data) {
        var records = data.data || [];
        var summary = data.summary || null;

        if (type === 'customer') { _reportData.customer = records; _reportPage.customer = 1; }
        else if (type === 'rental') { _reportData.rental = records; _reportPage.rental = 1; }
        else if (type === 'accident') { _reportData.accident = records; _reportPage.accident = 1; }

        if (records.length === 0 && !summary) {
            resultCount.textContent = '0 records';
            resultsDiv.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-inbox" style="font-size:3rem;"></i><p class="mt-3">No data found for the selected criteria.</p></div>';
            return;
        }

        var html = '';
        if (summary) html += renderSummaryCards(type, summary);

        if (records.length > 0) {
            resultCount.textContent = records.length + ' record' + (records.length !== 1 ? 's' : '');
            if (type === 'customer') html += renderCustomerTable(_reportData.customer, 1);
            else if (type === 'rental') html += renderRentalTable(_reportData.rental, 1);
            else if (type === 'accident') html += renderAccidentTable(_reportData.accident, 1);
            else html += renderTypedTable(type, records);
        } else {
            resultCount.textContent = summary ? 'Summary' : '0 records';
        }

        resultsDiv.innerHTML = html;
    })
    .catch(function (error) {
        resultCount.textContent = 'Error';
        resultsDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error generating report: ' + error.message + '</div>';
    });

    return false;
}

function detectReportType(form) {
    var id = form.id || '';
    if (id.indexOf('customer') !== -1) return 'customer';
    if (id.indexOf('rental') !== -1) return 'rental';
    if (id.indexOf('accident') !== -1) return 'accident';
    if (id.indexOf('revenue') !== -1) return 'revenue';
    if (id.indexOf('incident') !== -1) return 'incident';
    return 'rental';
}

/* ─── Summary Cards ─── */
function renderSummaryCards(type, summary) {
    var labels = {
        total: 'Total Customers', verified: 'Verified', pending: 'Pending Verification',
        blacklisted: 'Blacklisted', active: 'Active',
        completed: 'Completed', cancelled: 'Cancelled', overdue: 'Overdue',
        totalRevenue: 'Total Revenue', averageFee: 'Average Fee',
        averageRevenue: 'Avg Revenue', totalRentals: 'Total Rentals', periods: 'Periods',
        critical: 'Critical', high: 'High', moderate: 'Moderate', major: 'Major',
        minor: 'Minor', theftIncidents: 'Theft', acknowledged: 'Acknowledged',
        unacknowledged: 'Unacknowledged', pending_rental: 'Pending',
        resolved: 'Resolved', in_progress: 'In Progress', open: 'Open'
    };
    var icons = {
        total: 'bi-people-fill', verified: 'bi-patch-check-fill', pending: 'bi-hourglass-split',
        blacklisted: 'bi-shield-slash-fill', active: 'bi-play-circle-fill',
        completed: 'bi-check-circle-fill', cancelled: 'bi-x-circle-fill', overdue: 'bi-exclamation-triangle-fill',
        critical: 'bi-thermometer-high', major: 'bi-exclamation-diamond', acknowledged: 'bi-check2-circle',
        resolved: 'bi-check-circle-fill', in_progress: 'bi-gear', open: 'bi-hourglass-split'
    };
    var colors = {
        total: '#2563EB', verified: '#16A34A', pending: '#D97706',
        blacklisted: '#DC2626', active: '#2E7D32',
        completed: '#16A34A', cancelled: '#DC2626', overdue: '#D97706',
        critical: '#DC2626', major: '#D97706', acknowledged: '#0EA5E9',
        resolved: '#16A34A', in_progress: '#D97706', open: '#DC2626'
    };
    var money = { totalRevenue: true, averageFee: true, averageRevenue: true };

    var html = '<div class="row g-3 mb-4">';
    Object.entries(summary).forEach(function (entry) {
        var key = entry[0], val = entry[1];
        var label = labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
        var display = money[key] ? '\u20B1' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    : typeof val === 'number' ? val.toLocaleString() : val;
        var icon = icons[key] || 'bi-bar-chart';
        var color = colors[key] || 'var(--text-2)';
        html += '<div class="col-md-3 col-6"><div class="admin-card mb-0"><div class="admin-card__body text-center py-3">'
              + '<div style="color:' + color + ';font-size:1.5rem;margin-bottom:0.25rem;"><i class="bi ' + icon + '"></i></div>'
              + '<div class="fw-bold" style="font-size:1.3rem;color:var(--text-1);">' + display + '</div>'
              + '<div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">' + label + '</div>'
              + '</div></div></div>';
    });
    html += '</div>';
    return html;
}

function renderTypedTable(type, records) {
    if (type === 'revenue') return renderRevenueTable(records);
    if (type === 'incident') return renderIncidentTable(records);
    return renderRentalTable(records, 1);
}

/* ─── Customer Table ─── */
function renderCustomerTable(records, page) {
    _reportPage.customer = page || 1;
    var total = records.length, totalPages = Math.ceil(total / _pageSize);
    var start = (_reportPage.customer - 1) * _pageSize;
    var pageRecords = records.slice(start, start + _pageSize);
    var html = '<div class="table-responsive"><table class="admin-table"><thead><tr>'
        + '<th>Name</th><th>Student ID</th><th>Email</th><th>Phone</th><th>Status</th>'
        + '<th>Verified</th><th>Rentals</th><th>Total Spent</th><th>Joined</th><th>Action</th>'
        + '</tr></thead><tbody>';
    if (pageRecords.length === 0) html += '<tr><td colspan="10" class="text-center text-muted py-4">No customers found</td></tr>';
    pageRecords.forEach(function (c, idx) {
        var statusClass = 'secondary';
        if (c.status === 'active') statusClass = 'success';
        else if (c.status === 'inactive') statusClass = 'info';
        else if (c.status === 'suspended') statusClass = 'warning';
        else if (c.status === 'blacklisted') statusClass = 'danger';
        var verBadge = c.verified ? '<span class="badge bg-success"><i class="bi bi-check-lg"></i> Verified</span>' : '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>';
        var joined = c.createdAt || c.created_at;
        var joinedStr = joined ? new Date(joined).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
        var spent = Number(c.totalSpent || 0);
        html += '<tr>'
            + '<td class="fw-semibold">' + esc(c.name || '-') + '</td>'
            + '<td><code>' + esc(c.studentId || '-') + '</code></td>'
            + '<td>' + esc(c.email || '-') + '</td>'
            + '<td>' + esc(c.phoneNumber || '-') + '</td>'
            + '<td><span class="badge bg-' + statusClass + '">' + esc(c.status) + '</span></td>'
            + '<td>' + verBadge + '</td>'
            + '<td class="text-center">' + (c.rentals_count ?? c.totalRentals ?? 0) + '</td>'
            + '<td class="fw-bold" style="color:var(--success);">\u20B1' + spent.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>'
            + '<td>' + joinedStr + '</td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCustomerDetail(' + start + '+' + idx + ')"><i class="bi bi-eye"></i></button></td>'
            + '</tr>';
    });
    html += '</tbody></table></div>';
    html += buildPagination('customer', total, totalPages);
    return html;
}

/* ─── Rental Table ─── */
function renderRentalTable(records, page) {
    _reportPage.rental = page || 1;
    var total = records.length, totalPages = Math.ceil(total / _pageSize);
    var start = (_reportPage.rental - 1) * _pageSize;
    var pageRecords = records.slice(start, start + _pageSize);
    var html = '<div class="table-responsive"><table class="admin-table"><thead><tr>'
        + '<th>Rental ID</th><th>Rider</th><th>Bicycle</th><th>Start</th><th>End</th>'
        + '<th>Duration</th><th>Rate/Hr</th><th>Fee</th><th>Payment</th><th>Pay Status</th><th>Status</th><th></th>'
        + '</tr></thead><tbody>';
    if (pageRecords.length === 0) html += '<tr><td colspan="12" class="text-center text-muted py-4">No rentals found</td></tr>';
    pageRecords.forEach(function (r, idx) {
        var startT = r.startTime ? new Date(r.startTime).toLocaleString() : '-';
        var endT = r.endTime ? new Date(r.endTime).toLocaleString() : '-';
        var dur = r.durationMinutes ? Math.floor(r.durationMinutes / 60) + 'h ' + (r.durationMinutes % 60) + 'm' : '-';
        var rate = '\u20B1' + Number(r.ratePerHour || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
        var fee = '\u20B1' + Number(r.totalFee || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
        var statusClass = 'secondary';
        if (r.status === 'active') statusClass = 'success';
        else if (r.status === 'completed') statusClass = 'primary';
        else if (r.status === 'cancelled') statusClass = 'danger';
        else if (r.status === 'overdue') statusClass = 'warning';
        else if (r.status === 'pending') statusClass = 'info';
        var payClass = r.paymentStatus === 'paid' ? 'success' : (r.paymentStatus === 'pending' ? 'warning' : (r.paymentStatus === 'refunded' ? 'info' : 'danger'));
        html += '<tr>'
            + '<td><code>' + esc(r.rentalId || r.id) + '</code></td>'
            + '<td>' + esc((r.rider && r.rider.name) || r.riderName || '-') + '</td>'
            + '<td>' + esc((r.bicycle && r.bicycle.name) || r.bicycleName || '-') + '</td>'
            + '<td>' + startT + '</td>'
            + '<td>' + endT + '</td>'
            + '<td>' + dur + '</td>'
            + '<td>' + rate + '</td>'
            + '<td class="fw-bold">' + fee + '</td>'
            + '<td>' + esc(r.paymentMethod || '-') + '</td>'
            + '<td><span class="badge bg-' + payClass + '">' + esc(r.paymentStatus || '-') + '</span></td>'
            + '<td><span class="badge bg-' + statusClass + '">' + esc(r.status) + '</span></td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-primary" onclick="viewRentalDetail(' + start + '+' + idx + ')"><i class="bi bi-eye"></i></button></td>'
            + '</tr>';
    });
    html += '</tbody></table></div>';
    html += buildPagination('rental', total, totalPages);
    return html;
}

/* ─── Accident Table ─── */
function renderAccidentTable(records, page) {
    _reportPage.accident = page || 1;
    var total = records.length, totalPages = Math.ceil(total / _pageSize);
    var start = (_reportPage.accident - 1) * _pageSize;
    var pageRecords = records.slice(start, start + _pageSize);
    var html = '<div class="table-responsive"><table class="admin-table"><thead><tr>'
        + '<th>Accident ID</th><th>Rider</th><th>Bicycle</th><th>Location</th><th>Date/Time</th>'
        + '<th>Severity</th><th>Status</th><th>Action Taken</th><th>Actions</th>'
        + '</tr></thead><tbody>';
    if (pageRecords.length === 0) html += '<tr><td colspan="10" class="text-center text-muted py-4">No accidents found</td></tr>';
    pageRecords.forEach(function (a, idx) {
        var sevClass = 'secondary';
        if (a.severity === 'critical') sevClass = 'danger';
        else if (a.severity === 'major') sevClass = 'warning';
        else if (a.severity === 'moderate') sevClass = 'info';
        else if (a.severity === 'minor') sevClass = 'light text-dark';
        var statusClass = 'secondary';
        if (a.status === 'open') statusClass = 'danger';
        else if (a.status === 'resolved' || a.status === 'closed') statusClass = 'success';
        else if (a.status === 'in_progress') statusClass = 'warning';
        var ts = a.createdAt || a.created_at;
        var tsStr = ts ? new Date(ts).toLocaleString() : '-';
        var loc = formatLocation(a);
        var coords = getLocationCoords(a);
        var viewMapBtn = coords
            ? '<a class="btn btn-sm btn-outline-success me-1" href="https://www.google.com/maps?q=' + coords.lat + ',' + coords.lng + '" target="_blank" rel="noopener" title="View Location"><i class="bi bi-geo-alt"></i></a>'
            : '<span class="btn btn-sm btn-outline-secondary me-1 disabled" title="No location"><i class="bi bi-geo-alt"></i></span>';

        html += '<tr>'
            + '<td><code>#' + esc(a.id) + '</code></td>'
            + '<td class="fw-semibold">' + esc((a.rider && a.rider.name) || a.reportedBy || '-') + '</td>'
            + '<td>' + esc((a.bicycle && a.bicycle.name) || a.bicycleId || '-') + '</td>'
            + '<td class="text-truncate" style="max-width:140px;">' + loc + '</td>'
            + '<td>' + tsStr + '</td>'
            + '<td><span class="badge bg-' + sevClass + '">' + esc(a.severity || '-') + '</span></td>'
            + '<td><span class="badge bg-' + statusClass + '">' + esc(a.status || '-') + '</span></td>'
            + '<td class="text-truncate" style="max-width:160px;" title="' + esc(a.actionTaken || '') + '">' + esc(a.actionTaken || '-') + '</td>'
            + '<td><div class="d-flex">' + viewMapBtn + '<button type="button" class="btn btn-sm btn-outline-primary" onclick="viewAccidentDetail(' + start + '+' + idx + ')"><i class="bi bi-eye"></i></button></div></td>'
            + '</tr>';
    });
    html += '</tbody></table></div>';
    html += buildPagination('accident', total, totalPages);
    return html;
}

/* ─── Pagination Builder ─── */
function buildPagination(type, total, totalPages) {
    if (totalPages <= 1) return '';
    var currentPage = _reportPage[type];
    var html = '<div class="d-flex justify-content-between align-items-center mt-3">';
    html += '<div class="text-muted" style="font-size:0.85rem;">Showing ' + ((currentPage - 1) * _pageSize + 1) + '-' + Math.min(currentPage * _pageSize, total) + ' of ' + total + '</div>';
    html += '<nav><ul class="pagination pagination-sm mb-0">';
    html += '<li class="page-item' + (currentPage === 1 ? ' disabled' : '') + '">'
          + '<a class="page-link" href="#" onclick="event.preventDefault();paginateTable(\'' + type + '\',' + (currentPage - 1) + ')">&laquo;</a></li>';
    var maxPages = 7;
    var pStart = Math.max(1, currentPage - 3);
    var pEnd = Math.min(totalPages, pStart + maxPages - 1);
    if (pEnd - pStart < maxPages - 1) pStart = Math.max(1, pEnd - maxPages + 1);
    if (pStart > 1) {
        html += '<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault();paginateTable(\'' + type + '\',1)">1</a></li>';
        if (pStart > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    for (var p = pStart; p <= pEnd; p++) {
        html += '<li class="page-item' + (p === currentPage ? ' active' : '') + '">'
              + '<a class="page-link" href="#" onclick="event.preventDefault();paginateTable(\'' + type + '\',' + p + ')">' + p + '</a></li>';
    }
    if (pEnd < totalPages) {
        if (pEnd < totalPages - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        html += '<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault();paginateTable(\'' + type + '\',' + totalPages + ')">' + totalPages + '</a></li>';
    }
    html += '<li class="page-item' + (currentPage === totalPages ? ' disabled' : '') + '">'
          + '<a class="page-link" href="#" onclick="event.preventDefault();paginateTable(\'' + type + '\',' + (currentPage + 1) + ')">&raquo;</a></li>';
    html += '</ul></nav></div>';
    return html;
}

function paginateTable(type, page) {
    var container = document.getElementById('reportResults');
    var summaryEl = container.querySelector('.row.g-3.mb-4');
    var summarySection = summaryEl ? summaryEl.parentElement.outerHTML : '';
    var newHtml = '';
    if (type === 'customer') newHtml = renderCustomerTable(_reportData.customer, page);
    else if (type === 'rental') newHtml = renderRentalTable(_reportData.rental, page);
    else if (type === 'accident') newHtml = renderAccidentTable(_reportData.accident, page);
    container.innerHTML = summarySection + newHtml;
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function formatLocation(a) {
    var loc = a.gpsLocation || a.location;
    if (loc && loc.lat && loc.lng) {
        return Number(loc.lat).toFixed(5) + ', ' + Number(loc.lng).toFixed(5);
    }
    return a.location || '-';
}

function getLocationCoords(a) {
    var loc = a.gpsLocation || a.location;
    if (loc && loc.lat && loc.lng) return loc;
    return null;
}

/* ─── View Customer Detail ─── */
function viewCustomerDetail(index) {
    var c = _reportData.customer[index];
    if (!c) return;
    var statusClass = 'secondary';
    if (c.status === 'active') statusClass = 'success';
    else if (c.status === 'inactive') statusClass = 'info';
    else if (c.status === 'suspended') statusClass = 'warning';
    else if (c.status === 'blacklisted') statusClass = 'danger';
    var verBadge = c.verified ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning text-dark">Pending Verification</span>';
    var rentalsCount = c.rentals_count ?? c.totalRentals ?? 0;
    var spent = Number(c.totalSpent || 0);
    var joined = c.createdAt || c.created_at;
    var joinedStr = joined ? new Date(joined).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
    var lastLogin = c.last_login_at ? new Date(c.last_login_at).toLocaleString() : 'Never';
    var html = '<div class="row g-3">'
        + '<div class="col-md-4 text-center">'
        + '<div style="width:80px;height:80px;border-radius:50%;background:var(--brand-soft);display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:var(--brand);">' + esc((c.name || '').split(' ').map(function(w){return w[0]}).join('').toUpperCase().substring(0,2)) + '</div>'
        + '<div class="fw-bold mt-2" style="font-size:1.1rem;">' + esc(c.name) + '</div>'
        + '<div class="text-muted mb-2">' + esc(c.email) + '</div>'
        + verBadge + ' <span class="badge bg-' + statusClass + '">' + esc(c.status) + '</span>'
        + '</div>'
        + '<div class="col-md-8"><table class="table table-borderless mb-0" style="font-size:0.9rem;">'
        + '<tr><td class="text-muted" style="width:40%;">Student ID</td><td class="fw-semibold">' + esc(c.studentId || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Phone</td><td>' + esc(c.phoneNumber || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Address</td><td>' + esc(c.address || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Date Joined</td><td>' + joinedStr + '</td></tr>'
        + '<tr><td class="text-muted">Last Login</td><td>' + lastLogin + '</td></tr>'
        + '<tr><td class="text-muted">Total Rentals</td><td class="fw-semibold">' + rentalsCount + '</td></tr>'
        + '<tr><td class="text-muted">Total Spent</td><td class="fw-bold" style="color:var(--success);">\u20B1' + spent.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td></tr>'
        + (c.blacklistReason ? '<tr><td class="text-muted">Blacklist Reason</td><td class="text-danger">' + esc(c.blacklistReason) + '</td></tr>' : '')
        + '</table></div></div>';
    document.getElementById('customerDetailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('customerDetailModal')).show();
}

/* ─── View Rental Detail ─── */
function viewRentalDetail(index) {
    var r = _reportData.rental[index];
    if (!r) return;
    var statusClass = 'secondary';
    if (r.status === 'active') statusClass = 'success';
    else if (r.status === 'completed') statusClass = 'primary';
    else if (r.status === 'cancelled') statusClass = 'danger';
    else if (r.status === 'overdue') statusClass = 'warning';
    else if (r.status === 'pending') statusClass = 'info';
    var payClass = r.paymentStatus === 'paid' ? 'success' : (r.paymentStatus === 'pending' ? 'warning' : 'danger');
    var startT = r.startTime ? new Date(r.startTime).toLocaleString() : '-';
    var endT = r.endTime ? new Date(r.endTime).toLocaleString() : '-';
    var dur = r.durationMinutes ? Math.floor(r.durationMinutes / 60) + 'h ' + (r.durationMinutes % 60) + 'm' : '-';
    var rate = '\u20B1' + Number(r.ratePerHour || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    var fee = '\u20B1' + Number(r.totalFee || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    var dist = r.totalDistance ? Number(r.totalDistance).toFixed(2) + ' km' : '-';
    var html = '<div class="row g-3">'
        + '<div class="col-md-5">'
        + '<div class="d-flex align-items-center mb-3">'
        + '<div style="width:48px;height:48px;border-radius:var(--radius-sm);background:var(--brand-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--brand);"><i class="bi bi-bicycle"></i></div>'
        + '<div class="ms-3"><div class="fw-bold" style="font-size:1rem;"><code>' + esc(r.rentalId || r.id) + '</code></div>'
        + '<span class="badge bg-' + statusClass + '">' + esc(r.status) + '</span> '
        + '<span class="badge bg-' + payClass + '">' + esc(r.paymentStatus || '-') + '</span></div>'
        + '</div>'
        + '<table class="table table-borderless mb-0" style="font-size:0.9rem;">'
        + '<tr><td class="text-muted" style="width:45%;">Rider</td><td class="fw-semibold">' + esc((r.rider && r.rider.name) || r.riderName || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Rider Email</td><td>' + esc((r.rider && r.rider.email) || r.riderEmail || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Bicycle</td><td>' + esc((r.bicycle && r.bicycle.name) || r.bicycleName || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Start Time</td><td>' + startT + '</td></tr>'
        + '<tr><td class="text-muted">End Time</td><td>' + endT + '</td></tr>'
        + '</table></div>'
        + '<div class="col-md-7"><table class="table table-borderless mb-0" style="font-size:0.9rem;">'
        + '<tr><td class="text-muted" style="width:40%;">Duration</td><td class="fw-semibold">' + dur + '</td></tr>'
        + '<tr><td class="text-muted">Hourly Rate</td><td class="fw-bold">' + rate + '</td></tr>'
        + '<tr><td class="text-muted">Total Fee</td><td class="fw-bold" style="color:var(--success);font-size:1.05rem;">' + fee + '</td></tr>'
        + '<tr><td class="text-muted">Distance</td><td>' + dist + '</td></tr>'
        + '<tr><td class="text-muted">Payment Method</td><td>' + esc(r.paymentMethod || '-') + '</td></tr>'
        + (r.notes ? '<tr><td class="text-muted">Notes</td><td>' + esc(r.notes) + '</td></tr>' : '')
        + '</table></div></div>';
    document.getElementById('rentalDetailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('rentalDetailModal')).show();
}

/* ─── View Accident Detail ─── */
function viewAccidentDetail(index) {
    var a = _reportData.accident[index];
    if (!a) return;
    var sevClass = 'secondary';
    if (a.severity === 'critical') sevClass = 'danger';
    else if (a.severity === 'major') sevClass = 'warning';
    else if (a.severity === 'moderate') sevClass = 'info';
    else if (a.severity === 'minor') sevClass = 'light text-dark';
    var statusClass = 'secondary';
    if (a.status === 'open') statusClass = 'danger';
    else if (a.status === 'resolved' || a.status === 'closed') statusClass = 'success';
    else if (a.status === 'in_progress') statusClass = 'warning';
    var ts = a.createdAt || a.created_at;
    var tsStr = ts ? new Date(ts).toLocaleString() : '-';
    var loc = formatLocation(a);
    var coords = getLocationCoords(a);
    var impact = a.impactForce ? Number(a.impactForce).toFixed(2) + ' N' : '-';

    var mapBtn = coords
        ? '<a class="btn btn-sm btn-outline-primary mt-2" href="https://www.google.com/maps?q=' + coords.lat + ',' + coords.lng + '" target="_blank" rel="noopener"><i class="bi bi-geo-alt me-1"></i>View on Map</a>'
        : '';

    var html = '<div class="row g-3">'
        + '<div class="col-md-6">'
        + '<div class="d-flex align-items-center mb-3">'
        + '<div style="width:48px;height:48px;border-radius:var(--radius-sm);background:var(--brand-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--brand);"><i class="bi bi-activity"></i></div>'
        + '<div class="ms-3"><div class="fw-bold" style="font-size:1rem;">Accident <code>#' + esc(a.id) + '</code></div>'
        + '<span class="badge bg-' + sevClass + '">' + esc(a.severity || '-') + '</span> '
        + '<span class="badge bg-' + statusClass + '">' + esc(a.status || '-') + '</span></div>'
        + '</div>'
        + '<table class="table table-borderless mb-0" style="font-size:0.9rem;">'
        + '<tr><td class="text-muted" style="width:42%;">Rider</td><td class="fw-semibold">' + esc((a.rider && a.rider.name) || a.reportedBy || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Bicycle</td><td>' + esc((a.bicycle && a.bicycle.name) || a.bicycleId || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Type</td><td>' + esc(a.type || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Date / Time</td><td>' + tsStr + '</td></tr>'
        + '<tr><td class="text-muted">Impact Force</td><td>' + impact + '</td></tr>'
        + '<tr><td class="text-muted">Location</td><td>' + loc + '</td></tr>'
        + '</table>'
        + mapBtn
        + '</div>'
        + '<div class="col-md-6"><table class="table table-borderless mb-0" style="font-size:0.9rem;">'
        + '<tr><td class="text-muted" style="width:38%;">Acknowledged</td><td>' + (a.acknowledged ? '<i class="bi bi-check-circle-fill text-success"></i> Yes' : '<i class="bi bi-x-circle text-muted"></i> No') + '</td></tr>'
        + '<tr><td class="text-muted">Status</td><td><span class="badge bg-' + statusClass + '">' + esc(a.status || '-') + '</span></td></tr>'
        + '<tr><td class="text-muted">Description</td><td>' + esc(a.description || '-') + '</td></tr>'
        + '<tr><td class="text-muted">Action Taken</td><td class="fw-semibold">' + esc(a.actionTaken || '-') + '</td></tr>'
        + (a.warningLevel ? '<tr><td class="text-muted">Warning Level</td><td>' + esc(a.warningLevel) + '</td></tr>' : '')
        + (a.imageUrl ? '<tr><td class="text-muted">Image</td><td><a href="' + esc(a.imageUrl) + '" target="_blank" rel="noopener"><i class="bi bi-image me-1"></i>View</a></td></tr>' : '')
        + '</table></div></div>';

    document.getElementById('accidentDetailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('accidentDetailModal')).show();
}

/* ─── Revenue Table ─── */
function renderRevenueTable(records) {
    var html = '<div class="table-responsive"><table class="admin-table"><thead><tr>'
        + '<th>Period</th><th>Rentals</th><th>Total Revenue</th><th>Avg Revenue</th><th>Duration (min)</th>'
        + '</tr></thead><tbody>';
    records.forEach(function (r) {
        html += '<tr>'
            + '<td class="fw-bold">' + esc(r.period) + '</td>'
            + '<td>' + Number(r.total_rentals || 0).toLocaleString() + '</td>'
            + '<td class="fw-bold" style="color:var(--success);">\u20B1' + Number(r.total_revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</td>'
            + '<td>\u20B1' + Number(r.avg_revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</td>'
            + '<td>' + Number(r.total_duration_minutes || 0).toLocaleString() + '</td>'
            + '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
}

/* ─── Incident Table ─── */
function renderIncidentTable(records) {
    var html = '<div class="table-responsive"><table class="admin-table"><thead><tr>'
        + '<th>ID</th><th>Type</th><th>Severity</th><th>Bicycle</th><th>Description</th>'
        + '<th>Status</th><th>Acknowledged</th><th>Timestamp</th>'
        + '</tr></thead><tbody>';
    records.forEach(function (r) {
        var sevClass = 'secondary';
        if (r.severity === 'critical') sevClass = 'danger';
        else if (r.severity === 'major') sevClass = 'warning';
        else if (r.severity === 'moderate') sevClass = 'info';
        else if (r.severity === 'minor') sevClass = 'light text-dark';
        var ts = r.createdAt ? new Date(r.createdAt).toLocaleString() : (r.created_at ? new Date(r.created_at).toLocaleString() : '-');
        html += '<tr>'
            + '<td><code>#' + esc(r.id) + '</code></td>'
            + '<td>' + esc(r.type || '-') + '</td>'
            + '<td><span class="badge bg-' + sevClass + '">' + esc(r.severity || '-') + '</span></td>'
            + '<td>' + esc((r.bicycle && r.bicycle.name) || r.bicycleId || '-') + '</td>'
            + '<td>' + esc(r.description || '-') + '</td>'
            + '<td>' + esc(r.status || '-') + '</td>'
            + '<td>' + (r.acknowledged ? '<i class="bi bi-check-circle-fill text-success"></i> Yes' : '<i class="bi bi-x-circle text-muted"></i> No') + '</td>'
            + '<td>' + ts + '</td>'
            + '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
}

function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s || '')); return d.innerHTML; }

/* ─── Export ─── */
function exportReport(type, format) {
    var form = document.getElementById(type + 'ReportForm');
    var params = new URLSearchParams();
    params.append('type', type);
    var fields = form.querySelectorAll('input, select');
    fields.forEach(function (field) {
        if (field.name && field.value) params.append(field.name, field.value);
    });
    window.location.href = _baseUrl + '/export/' + format + '?' + params.toString();
}

/* ─── Print ─── */
function printReport(type) {
    var records = _reportData[type] || [];
    if (records.length === 0) { alert('Please generate a report first before printing.'); return; }

    var win = window.open('', '_blank', 'width=1200,height=700');
    var titles = { customer: 'Customer Report', rental: 'Rental Report', accident: 'Accident Report', revenue: 'Revenue Report', incident: 'Incidents Report' };
    var title = titles[type] || 'Report';
    var rows = '';
    var headers;

    if (type === 'customer') {
        headers = '<th>Name</th><th>Student ID</th><th>Email</th><th>Phone</th><th>Status</th><th>Verified</th><th>Rentals</th><th>Total Spent</th>';
        records.forEach(function (c) {
            var spent = Number(c.totalSpent || 0);
            rows += '<tr><td>' + esc(c.name) + '</td><td>' + esc(c.studentId || '-') + '</td><td>' + esc(c.email) + '</td>'
                + '<td>' + esc(c.phoneNumber || '-') + '</td><td>' + esc(c.status) + '</td><td>' + (c.verified ? 'Yes' : 'No') + '</td>'
                + '<td>' + (c.rentals_count ?? c.totalRentals ?? 0) + '</td>'
                + '<td>\u20B1' + spent.toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</td></tr>';
        });
    } else if (type === 'rental') {
        headers = '<th>Rental ID</th><th>Rider</th><th>Bicycle</th><th>Start</th><th>End</th><th>Duration</th><th>Rate/Hr</th><th>Fee</th><th>Payment</th><th>Pay Status</th><th>Status</th>';
        records.forEach(function (r) {
            var dur = r.durationMinutes ? Math.floor(r.durationMinutes / 60) + 'h ' + (r.durationMinutes % 60) + 'm' : '-';
            rows += '<tr><td>' + esc(r.rentalId || r.id) + '</td><td>' + esc((r.rider && r.rider.name) || r.riderName || '-') + '</td>'
                + '<td>' + esc((r.bicycle && r.bicycle.name) || r.bicycleName || '-') + '</td>'
                + '<td>' + (r.startTime ? new Date(r.startTime).toLocaleString() : '-') + '</td>'
                + '<td>' + (r.endTime ? new Date(r.endTime).toLocaleString() : '-') + '</td>'
                + '<td>' + dur + '</td>'
                + '<td>\u20B1' + Number(r.ratePerHour || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</td>'
                + '<td>\u20B1' + Number(r.totalFee || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</td>'
                + '<td>' + esc(r.paymentMethod || '-') + '</td>'
                + '<td>' + esc(r.paymentStatus || '-') + '</td>'
                + '<td>' + esc(r.status) + '</td></tr>';
        });
    } else if (type === 'accident') {
        headers = '<th>Accident ID</th><th>Rider</th><th>Bicycle</th><th>Location</th><th>Date/Time</th><th>Severity</th><th>Status</th><th>Acknowledged</th><th>Action Taken</th>';
        records.forEach(function (a) {
            var ts = a.createdAt || a.created_at;
            rows += '<tr><td>#' + esc(a.id) + '</td><td>' + esc((a.rider && a.rider.name) || a.reportedBy || '-') + '</td>'
                + '<td>' + esc((a.bicycle && a.bicycle.name) || a.bicycleId || '-') + '</td>'
                + '<td>' + esc(formatLocation(a)) + '</td>'
                + '<td>' + (ts ? new Date(ts).toLocaleString() : '-') + '</td>'
                + '<td>' + esc(a.severity || '-') + '</td>'
                + '<td>' + esc(a.status || '-') + '</td>'
                + '<td>' + (a.acknowledged ? 'Yes' : 'No') + '</td>'
                + '<td>' + esc(a.actionTaken || '-') + '</td></tr>';
        });
    }

    win.document.write('<!DOCTYPE html><html><head><title>Pedalya - ' + title + '</title>'
        + '<style>body{font-family:Arial,sans-serif;font-size:11px;color:#222;margin:20px;}'
        + 'h1{font-size:18px;color:#14532d;margin:0 0 4px;}'
        + '.muted{color:#666;font-size:11px;margin-bottom:12px;}'
        + 'table{width:100%;border-collapse:collapse;margin-top:10px;}'
        + 'th{background:#14532d;color:#fff;padding:5px 6px;text-align:left;font-size:10px;}'
        + 'td{padding:4px 6px;border:1px solid #ddd;font-size:10px;}'
        + 'tr:nth-child(even) td{background:#f9fafb;}'
        + '@media print{body{margin:10px;}}</style></head><body>'
        + '<h1>Pedalya Bicycle Rental &mdash; ' + title + '</h1>'
        + '<div class="muted">Generated: ' + new Date().toLocaleString() + ' | Total: ' + records.length + ' records</div>'
        + '<table><thead><tr>' + headers + '</tr></thead><tbody>' + rows + '</tbody></table>'
        + '<div style="margin-top:16px;text-align:center;color:#888;font-size:9px;">Pedalya IoT Bicycle Rental Management System &bull; ' + new Date().getFullYear() + '</div>'
        + '</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function () { win.print(); }, 500);
}

/* ─── Clear ─── */
function clearResults() {
    document.getElementById('resultsCard').style.display = 'none';
    document.getElementById('reportResults').innerHTML = '';
    document.getElementById('resultCount').textContent = '0';
    _reportData = { customer: [], rental: [], accident: [] };
    _reportPage = { customer: 1, rental: 1, accident: 1 };
}

function clearCustomerFilters() {
    document.getElementById('customerSearch').value = '';
    document.getElementById('customerDateFrom').value = '';
    document.getElementById('customerDateTo').value = '';
    document.getElementById('customerStatus').value = '';
    document.getElementById('customerVerified').value = '';
}

function clearRentalFilters() {
    document.getElementById('rentalSearch').value = '';
    document.getElementById('rentalDateFrom').value = '<?php echo e(now()->subDays(30)->format("Y-m-d")); ?>';
    document.getElementById('rentalDateTo').value = '<?php echo e(now()->format("Y-m-d")); ?>';
    document.getElementById('rentalStatus').value = '';
    document.getElementById('rentalPaymentStatus').value = '';
    document.getElementById('rentalBicycle').value = '';
    document.getElementById('rentalRider').value = '';
}

function clearAccidentFilters() {
    document.getElementById('accidentSearch').value = '';
    document.getElementById('accidentDateFrom').value = '<?php echo e(now()->subDays(30)->format("Y-m-d")); ?>';
    document.getElementById('accidentDateTo').value = '<?php echo e(now()->format("Y-m-d")); ?>';
    document.getElementById('accidentSeverity').value = '';
    document.getElementById('accidentStatus').value = '';
    document.getElementById('accidentBicycle').value = '';
    document.getElementById('accidentRider').value = '';
    var typeEl = document.getElementById('accidentType');
    if (typeEl) typeEl.value = '';
}

/* ─── Tab sync with URL ─── */
(function () {
    var params = new URLSearchParams(window.location.search);
    var tab = params.get('tab');
    if (tab) {
        var tabMap = { customer: 'customer-tab', rental: 'rental-tab', accident: 'accident-tab', revenue: 'revenue-tab', incident: 'incident-tab' };
        var tabId = tabMap[tab];
        if (tabId) {
            var trigger = document.getElementById(tabId);
            if (trigger) new bootstrap.Tab(trigger).show();
        }
    }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\reports.blade.php ENDPATH**/ ?>