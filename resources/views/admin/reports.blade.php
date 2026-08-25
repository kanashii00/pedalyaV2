@extends('layouts.admin')

@section('title', 'Reports')

@section('page-header')
    <h1>Reports</h1>
    <p>Generate and export system reports</p>
@endsection

@section('actions')
<button type="button" class="btn-admin btn-admin--secondary btn-admin--sm" onclick="clearResults()">
    <i class="bi bi-x-lg me-1"></i>Clear Results
</button>
@endsection

@section('content')
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
                <form action="{{ route('admin.reports.rental') }}" method="POST" id="rentalReportForm" onsubmit="return generateReport(event, this)">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="rentalDateFrom" name="date_from"
                                value="{{ old('date_from', now()->subDays(30)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="rentalDateTo" name="date_to"
                                value="{{ old('date_to', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
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
                            <label class="form-label">Bicycle</label>
                            <select class="form-select" id="rentalBicycle" name="bicycle_id">
                                <option value="">All</option>
                                @foreach($bicycles ?? [] as $bicycle)
                                    <option value="{{ $bicycle->id }}">{{ $bicycle->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rider</label>
                            <select class="form-select" id="rentalRider" name="user_id">
                                <option value="">All</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
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
                <form action="{{ route('admin.reports.revenue') }}" method="POST" id="revenueReportForm" onsubmit="return generateReport(event, this)">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="revenueDateFrom" name="date_from"
                                value="{{ old('date_from', now()->subDays(30)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="revenueDateTo" name="date_to"
                                value="{{ old('date_to', now()->format('Y-m-d')) }}" required>
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
                <form action="{{ route('admin.reports.incident') }}" method="POST" id="incidentReportForm" onsubmit="return generateReport(event, this)">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="incidentDateFrom" name="date_from"
                                value="{{ old('date_from', now()->subDays(30)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="incidentDateTo" name="date_to"
                                value="{{ old('date_to', now()->format('Y-m-d')) }}" required>
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
@endsection

@section('scripts')
<script>
const _token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function generateReport(event, form) {
    event.preventDefault();
    const url = form.getAttribute('action');
    const formData = new FormData(form);
    const resultsDiv = document.getElementById('reportResults');
    const resultsCard = document.getElementById('resultsCard');
    const resultCount = document.getElementById('resultCount');

    resultsCard.style.display = 'block';
    resultsDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-pedalya" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Generating report...</p></div>';

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

        if (records.length === 0 && !summary) {
            resultCount.textContent = '0 records';
            resultsDiv.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-inbox" style="font-size:3rem;"></i><p class="mt-3">No data found for the selected criteria.</p></div>';
            return;
        }

        var type = detectReportType(form);
        var html = '';

        if (summary) {
            html += renderSummaryCards(type, summary);
        }

        if (records.length > 0) {
            resultCount.textContent = records.length + ' record' + (records.length !== 1 ? 's' : '');
            html += renderTypedTable(type, records);
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
    if (id.indexOf('rental') !== -1) return 'rental';
    if (id.indexOf('revenue') !== -1) return 'revenue';
    if (id.indexOf('incident') !== -1) return 'incident';
    return 'rental';
}

function renderSummaryCards(type, summary) {
    var labels = {
        total: 'Total Records', completed: 'Completed', cancelled: 'Cancelled',
        active: 'Active', totalRevenue: 'Total Revenue', averageFee: 'Average Fee',
        averageRevenue: 'Avg Revenue', totalRentals: 'Total Rentals', periods: 'Periods',
        critical: 'Critical', high: 'High', moderate: 'Moderate', major: 'Major',
        minor: 'Minor', theftIncidents: 'Theft', acknowledged: 'Acknowledged',
        unacknowledged: 'Unacknowledged'
    };
    var money = { totalRevenue: true, averageFee: true, averageRevenue: true };

    var html = '<div class="row g-3 mb-4">';
    Object.entries(summary).forEach(function (entry) {
        var key = entry[0], val = entry[1];
        var label = labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
        var display = money[key] ? '\u20B1' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    : typeof val === 'number' ? val.toLocaleString() : val;
        html += '<div class="col-md-3 col-6"><div class="admin-card mb-0"><div class="admin-card__body text-center py-3">'
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
    return renderRentalTable(records);
}

function renderRentalTable(records) {
    var html = '<div class="table-responsive"><table class="table admin-table"><thead><tr>'
        + '<th>Rental ID</th><th>Rider</th><th>Bicycle</th><th>Start</th><th>End</th>'
        + '<th>Duration</th><th>Fee</th><th>Status</th><th>Payment</th>'
        + '</tr></thead><tbody>';
    records.forEach(function (r) {
        var start = r.startTime ? new Date(r.startTime).toLocaleString() : '-';
        var end = r.endTime ? new Date(r.endTime).toLocaleString() : '-';
        var dur = r.durationMinutes ? Math.floor(r.durationMinutes / 60) + 'h ' + (r.durationMinutes % 60) + 'm' : '-';
        var fee = '\u20B1' + Number(r.totalFee || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
        var statusClass = 'secondary';
        if (r.status === 'active') statusClass = 'success';
        else if (r.status === 'completed') statusClass = 'primary';
        else if (r.status === 'cancelled') statusClass = 'danger';
        else if (r.status === 'overdue') statusClass = 'warning';
        var payClass = r.paymentStatus === 'paid' ? 'success' : (r.paymentStatus === 'pending' ? 'warning' : 'danger');
        html += '<tr>'
            + '<td><code>' + esc(r.rentalId || r.id) + '</code></td>'
            + '<td>' + esc((r.rider && r.rider.name) || r.riderName || '-') + '</td>'
            + '<td>' + esc((r.bicycle && r.bicycle.name) || r.bicycleName || '-') + '</td>'
            + '<td>' + start + '</td>'
            + '<td>' + end + '</td>'
            + '<td>' + dur + '</td>'
            + '<td class="fw-bold">' + fee + '</td>'
            + '<td><span class="badge bg-' + statusClass + '">' + esc(r.status) + '</span></td>'
            + '<td><span class="badge bg-' + payClass + '">' + esc(r.paymentStatus || '-') + '</span></td>'
            + '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
}

function renderRevenueTable(records) {
    var html = '<div class="table-responsive"><table class="table admin-table"><thead><tr>'
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

function renderIncidentTable(records) {
    var html = '<div class="table-responsive"><table class="table admin-table"><thead><tr>'
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

function exportReport(type, format) {
    var form = document.getElementById(type + 'ReportForm');
    var params = new URLSearchParams();
    params.append('type', type);
    var fields = form.querySelectorAll('input, select');
    fields.forEach(function (field) {
        if (field.name && field.value) params.append(field.name, field.value);
    });
    var url = @json(route('admin.reports.export.pdf')) + '?' + params.toString();
    url = url.replace('/export/pdf', '/export/' + format);
    window.location.href = url;
}

function clearResults() {
    document.getElementById('resultsCard').style.display = 'none';
    document.getElementById('reportResults').innerHTML = '';
    document.getElementById('resultCount').textContent = '0';
}
</script>
@endsection
