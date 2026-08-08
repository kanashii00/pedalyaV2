@extends('layouts.rider')

@section('title', 'Rent Bicycle')

@section('styles')
<style>
    .bicycle-rental-card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }
    .bicycle-rental-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    .bicycle-rental-card .card-image {
        background: linear-gradient(135deg, #C8E6C9, #E8F5E9);
        height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #2E7D32;
    }
    .bicycle-rental-card .card-body {
        padding: 16px;
    }
    .bicycle-rental-card .card-details {
        display: flex;
        gap: 12px;
        font-size: 0.85rem;
        color: #666;
    }
    .bg-primary-custom {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
    }
    .rent-map-wrap {
        height: 350px;
        position: relative;
    }
    .rent-map-wrap:fullscreen {
        height: 100vh;
        background: #1a1a1a;
    }
    .rent-map-wrap:-webkit-full-screen {
        height: 100vh;
        background: #1a1a1a;
    }
    #rentMapMaximize {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid #d0d7de;
        background: #fff;
        color: #444;
        cursor: pointer;
        transition: all 0.15s;
    }
    #rentMapMaximize:hover { border-color: var(--primary); color: var(--primary); }
</style>
@endsection

@section('content')
<!-- Map -->
<div class="card-pedalya mb-4">
    <div class="card-pedalya-header"><span><i class="bi bi-geo-alt-fill text-primary me-2"></i><strong>Available Bicycles Near You</strong></span><span class="d-flex align-items-center gap-2"><span class="text-muted" style="font-size:0.85rem;">Click a marker to see details</span><button type="button" id="rentMapMaximize" aria-label="Fullscreen map"><i class="bi bi-arrows-fullscreen"></i><span>Fullscreen</span></button></span></div>
    <div class="rent-map-wrap" id="rentMapWrap"><div id="rentMap" style="width:100%;height:100%;"></div></div>
</div>

<!-- Bicycle Cards -->
<h5 class="mb-3"><i class="bi bi-collection me-2"></i>Available Bicycles</h5>
<div class="row g-4">
    @forelse($bicycles as $index => $bicycle)
        <div class="col-lg-4 col-md-6">
            <div class="bicycle-rental-card fade-in-up" style="animation-delay:{{ $index * 0.1 }}s;">
                <div class="card-image"><i class="bi bi-bicycle"></i></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6>{{ $bicycle->serialNumber }} - {{ $bicycle->name }}</h6>
                        <span class="badge-status badge-available">Available</span>
                    </div>
                    <p class="text-muted mb-2" style="font-size:0.85rem;">{{ $bicycle->model ?? '' }}</p>
                    <div class="card-details">
                        <span>
                            @if($bicycle->batteryLevel >= 70)
                                <i class="bi bi-battery-full text-success"></i>
                            @elseif($bicycle->batteryLevel >= 40)
                                <i class="bi bi-battery-three-quarters text-success"></i>
                            @else
                                <i class="bi bi-battery-half text-warning"></i>
                            @endif
                            {{ $bicycle->batteryLevel }}%
                        </span>
                        <span><strong>₱{{ number_format($bicycle->hourlyRate, 0) }}/hr</strong></span>
                    </div>
                    <button type="button" class="btn btn-pedalya w-100 justify-content-center mt-3"
                        onclick="selectBicycle('{{ $bicycle->id }}', '{{ $bicycle->serialNumber }} - {{ $bicycle->name }}', {{ $bicycle->batteryLevel }}, {{ $bicycle->hourlyRate }})">
                        <i class="bi bi-key-fill"></i> Select & Rent
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-bicycle" style="font-size:4rem;color:#ccc;"></i>
                <h5 class="mt-3 text-muted">No bicycles available</h5>
                <p class="text-muted">Check back later or try a different station.</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Rental Confirmation Modal -->
<div class="modal fade" id="rentalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bicycle text-primary me-2"></i>Confirm Rental</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:70px;height:70px;background:var(--primary);color:#fff;font-size:1.8rem;">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <h5 class="mt-2 mb-0" id="modalBikeName">Bicycle</h5>
                    <span class="badge-status badge-available">Available</span>
                </div>
                <div class="bg-light rounded p-3 mb-3">
                    <div class="row text-center">
                        <div class="col-4"><small class="text-muted d-block">Battery</small><strong id="modalBikeBattery">0%</strong></div>
                        <div class="col-4"><small class="text-muted d-block">Hourly Rate</small><strong id="modalBikeRate">₱25/hr</strong></div>
                        <div class="col-4"><small class="text-muted d-block">Status</small><strong class="text-success">Available</strong></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label-pedalya">Rental Duration (hours)</label>
                    <input type="range" class="form-range" min="1" max="8" value="2" id="durationSlider" oninput="updateCost()">
                    <div class="d-flex justify-content-between"><small class="text-muted">1 hour</small><strong id="durationDisplay">2 hours</strong><small class="text-muted">8 hours</small></div>
                </div>
                <div class="bg-primary-custom text-white rounded p-3 text-center mb-3">
                    <small>Estimated Cost</small>
                    <h4 class="mb-0" id="costDisplay">₱50.00</h4>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rentalTerms" required>
                    <label class="form-check-label" for="rentalTerms" style="font-size:0.85rem;color:#666;">I agree to the rental terms and conditions, and will return the bicycle to a designated station.</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('rider.rent.store') }}" method="POST" id="rentalForm" class="d-inline">
                    @csrf
                    <input type="hidden" name="bicycleId" id="selectedBicycleId" value="">
                    <button type="submit" class="btn btn-pedalya" id="confirmRentBtn"><i class="bi bi-key-fill"></i> Start Rental</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var currentRate = 25;

    function selectBicycle(id, name, battery, rate) {
        currentRate = rate;
        document.getElementById('selectedBicycleId').value = id;
        document.getElementById('modalBikeName').textContent = name;
        document.getElementById('modalBikeBattery').textContent = battery + '%';
        document.getElementById('modalBikeRate').textContent = '₱' + rate + '/hr';
        updateCost();
        new bootstrap.Modal(document.getElementById('rentalModal')).show();
    }

    function updateCost() {
        var hours = document.getElementById('durationSlider').value;
        document.getElementById('durationDisplay').textContent = hours + (hours == 1 ? ' hour' : ' hours');
        document.getElementById('costDisplay').textContent = '₱' + (hours * currentRate).toFixed(2);
    }

    document.getElementById('rentalForm').addEventListener('submit', function(e) {
        if (!document.getElementById('rentalTerms').checked) {
            e.preventDefault();
            alert('Please accept the terms and conditions');
            return;
        }
        var btn = document.getElementById('confirmRentBtn');
        btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div> Processing...';
        btn.disabled = true;
    });

    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('rentMap');
        if (!el) return;
        if (typeof maplibregl === 'undefined') {
            el.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 bg-light"><small class="text-muted">Map loading...</small></div>';
            return;
        }
        var bicycles = {!! json_encode($bicycles->filter(fn($b) => $b->currentLat && $b->currentLng)->map(fn($b) => ['id' => $b->id, 'lat' => (float) $b->currentLat, 'lng' => (float) $b->currentLng, 'name' => $b->serialNumber . ' ' . $b->name, 'battery' => $b->batteryLevel, 'rate' => (float) $b->hourlyRate])) !!};
        var hasBikes = bicycles.length > 0;
        var map = new maplibregl.Map({
            container: el,
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: hasBikes ? [bicycles[0].lng, bicycles[0].lat] : [125.6470, 7.0990],
            zoom: 15
        });
        map.addControl(new maplibregl.NavigationControl(), 'top-right');
        map.addControl(new maplibregl.FullscreenControl(), 'top-right');
        bicycles.forEach(function(b) {
            var marker = new maplibregl.Marker({ color: '#2E7D32' })
                .setLngLat([b.lng, b.lat])
                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(
                    '<div style="padding:6px;font-family:Inter;"><strong>' + b.name + '</strong><br><span style="color:#2E7D32;">Available</span></div>'
                ))
                .addTo(map);
            marker.getElement().addEventListener('click', function() {
                selectBicycle(b.id, b.name, b.battery, b.rate);
            });
        });

        var wrap = document.getElementById('rentMapWrap');
        var maxBtn = document.getElementById('rentMapMaximize');
        if (wrap && maxBtn) {
            function syncFullscreenIcon() {
                var fs = document.fullscreenElement === wrap;
                maxBtn.innerHTML = fs
                    ? '<i class="bi bi-fullscreen-exit"></i><span>Exit Fullscreen</span>'
                    : '<i class="bi bi-arrows-fullscreen"></i><span>Fullscreen</span>';
            }
            maxBtn.addEventListener('click', function() {
                if (document.fullscreenElement === wrap) {
                    document.exitFullscreen();
                } else if (wrap.requestFullscreen) {
                    wrap.requestFullscreen();
                } else if (wrap.webkitRequestFullscreen) {
                    wrap.webkitRequestFullscreen();
                }
            });
            document.addEventListener('fullscreenchange', syncFullscreenIcon);
            document.addEventListener('webkitfullscreenchange', syncFullscreenIcon);
        }
    });
</script>
@endsection
