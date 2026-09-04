<div class="row g-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card__head">
                <div class="admin-card__title"><i class="bi bi-download me-2"></i>Report Export Center</div>
                <div class="admin-card__tools"><span class="badge-admin badge-admin--neutral">PDF &middot; Excel &middot; CSV</span></div>
            </div>
            <div class="admin-card__body">
                <p class="text-muted mb-4">Choose a report module to view its detailed page, or export its full dataset directly to PDF, Excel, or CSV.</p>
                <div class="row g-3">
                    @php
                        $exportModules = [
                            ['type' => 'customer', 'title' => 'Customer Reports', 'icon' => 'bi-people', 'desc' => 'Riders, verification & spending'],
                            ['type' => 'rental', 'title' => 'Rental Reports', 'icon' => 'bi-bicycle', 'desc' => 'Rental activity, fees & payments'],
                            ['type' => 'bicycle', 'title' => 'Bicycle Reports', 'icon' => 'bi-speedometer', 'desc' => 'Fleet usage, battery & condition'],
                            ['type' => 'theft', 'title' => 'Theft Reports', 'icon' => 'bi-shield-exclamation', 'desc' => 'Theft-detection incidents & alerts'],
                            ['type' => 'accident', 'title' => 'Accident Reports', 'icon' => 'bi-activity', 'desc' => 'Accident & impact-detection records'],
                            ['type' => 'revenue', 'title' => 'Revenue Reports', 'icon' => 'bi-currency-dollar', 'desc' => 'Revenue grouped by period'],
                        ];
                    @endphp
                    @foreach($exportModules as $m)
                    <div class="col-md-6 col-xl-4">
                        <div class="admin-card mb-0 h-100">
                            <div class="admin-card__body">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="width:42px;height:42px;border-radius:var(--radius-sm);background:var(--brand-soft);display:flex;align-items:center;justify-content:center;color:var(--brand);font-size:1.1rem;"><i class="bi {{ $m['icon'] }}"></i></div>
                                    <div class="ms-3">
                                        <div class="fw-bold">{{ $m['title'] }}</div>
                                        <div class="text-muted" style="font-size:0.8rem;">{{ $m['desc'] }}</div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3 flex-wrap">
                                    <a class="btn-admin btn-admin--secondary btn-admin--sm" href="{{ route('admin.reports.index', ['tab' => $m['type']]) }}">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="quickExport('{{ $m['type'] }}', 'pdf')"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="quickExport('{{ $m['type'] }}', 'excel')"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="quickExport('{{ $m['type'] }}', 'csv')"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function quickExport(type, format) {
    var params = new URLSearchParams();
    params.append('type', type);
    window.location.href = _baseUrl + '/export/' + format + '?' + params.toString();
}
</script>
