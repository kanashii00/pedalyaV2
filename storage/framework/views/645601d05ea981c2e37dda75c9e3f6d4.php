

<?php $__env->startSection('title', 'Rent Bicycle'); ?>

<?php $__env->startSection('styles'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Map -->
<div class="card-pedalya mb-4">
    <div class="card-pedalya-header"><span><i class="bi bi-geo-alt-fill text-primary me-2"></i><strong>Available Bicycles Near You</strong></span><span class="d-flex align-items-center gap-2"><span class="text-muted" style="font-size:0.85rem;">Click a marker to see details</span><button type="button" id="rentMapMaximize" aria-label="Fullscreen map"><i class="bi bi-arrows-fullscreen"></i><span>Fullscreen</span></button></span></div>
    <div class="rent-map-wrap" id="rentMapWrap"><div id="rentMap" style="width:100%;height:100%;"></div></div>
</div>

<!-- Bicycle Cards -->
<h5 class="mb-3"><i class="bi bi-collection me-2"></i>Available Bicycles</h5>
<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $bicycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $bicycle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-lg-4 col-md-6">
            <div class="bicycle-rental-card fade-in-up" style="animation-delay:<?php echo e($index * 0.1); ?>s;">
                <div class="card-image"><i class="bi bi-bicycle"></i></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6><?php echo e($bicycle->serialNumber); ?> - <?php echo e($bicycle->name); ?></h6>
                        <span class="badge-status badge-available">Available</span>
                    </div>
                    <p class="text-muted mb-2" style="font-size:0.85rem;"><?php echo e($bicycle->model ?? ''); ?></p>
                    <div class="card-details">
                        <span>
                            <?php if($bicycle->batteryLevel >= 70): ?>
                                <i class="bi bi-battery-full text-success"></i>
                            <?php elseif($bicycle->batteryLevel >= 40): ?>
                                <i class="bi bi-battery-three-quarters text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-battery-half text-warning"></i>
                            <?php endif; ?>
                            <?php echo e($bicycle->batteryLevel); ?>%
                        </span>
                        <span><strong>₱<?php echo e(number_format($bicycle->hourlyRate, 0)); ?>/hr</strong></span>
                    </div>
                    <button type="button" class="btn btn-pedalya w-100 justify-content-center mt-3"
                        onclick="selectBicycle('<?php echo e($bicycle->id); ?>', '<?php echo e($bicycle->serialNumber); ?> - <?php echo e($bicycle->name); ?>', <?php echo e($bicycle->batteryLevel); ?>, <?php echo e($bicycle->hourlyRate); ?>)">
                        <i class="bi bi-key-fill"></i> Select & Rent
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-bicycle" style="font-size:4rem;color:#ccc;"></i>
                <h5 class="mt-3 text-muted">No bicycles available</h5>
                <p class="text-muted">Check back later or try a different station.</p>
            </div>
        </div>
    <?php endif; ?>
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

                <!-- Payment Method -->
                <div class="mb-3">
                    <label class="form-label-pedalya fw-bold">Payment Method</label>
                    <div class="d-flex gap-3">
                        <label class="payment-method-option flex-fill active" id="payOptCash">
                            <input type="radio" name="paymentMethod" value="cash" class="d-none" checked onchange="togglePaymentMethod('cash')">
                            <div class="text-center p-3 rounded border h-100">
                                <i class="bi bi-cash-stack fs-3 text-success"></i>
                                <div class="fw-bold mt-1" style="font-size:0.9rem;">Cash</div>
                                <small class="text-muted">Pay at station</small>
                            </div>
                        </label>
                        <label class="payment-method-option flex-fill" id="payOptGcash">
                            <input type="radio" name="paymentMethod" value="gcash" class="d-none" onchange="togglePaymentMethod('gcash')">
                            <div class="text-center p-3 rounded border h-100">
                                <i class="bi bi-phone fs-3 text-primary"></i>
                                <div class="fw-bold mt-1" style="font-size:0.9rem;">GCash</div>
                                <small class="text-muted">Scan QR to pay</small>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- GCash Payment Area (hidden by default) -->
                <div id="gcashPaymentArea" class="d-none mb-3">
                    <div class="border rounded p-3" style="background:#f0f7ff;">
                        <div class="text-center mb-3">
                            <small class="text-muted">Scan this QR code to pay</small>
                            <div class="my-2">
                                <div id="gcashQrCode" style="display:inline-block;background:#fff;padding:12px;border-radius:8px;border:1px solid #e0e0e0;">
                                    <canvas id="gcashQrCanvas"></canvas>
                                </div>
                            </div>
                            <div class="fw-bold text-primary fs-5" id="gcashAmountDisplay">₱50.00</div>
                        </div>
                        <ol class="mb-3" style="font-size:0.85rem;padding-left:1.2rem;color:#555;">
                            <li>Open your <strong>GCash</strong> app and tap <strong>Scan QR</strong></li>
                            <li>Scan the QR code above</li>
                            <li>Enter the exact amount shown above</li>
                            <li>Complete the payment and take a screenshot</li>
                        </ol>
                        <div class="mb-2">
                            <label class="form-label fw-bold" style="font-size:0.85rem;">GCash Reference Number</label>
                            <input type="text" class="form-control" name="paymentReference" id="gcashRefInput"
                                   placeholder="e.g. 1234567890123" maxlength="100" required>
                            <small class="text-muted">Enter the reference number from your GCash receipt</small>
                        </div>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rentalTerms" required>
                    <label class="form-check-label" for="rentalTerms" style="font-size:0.85rem;color:#666;">I agree to the rental terms and conditions, and will return the bicycle to a designated station.</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo e(route('rider.rent.store')); ?>" method="POST" id="rentalForm" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="bicycleId" id="selectedBicycleId" value="">
                    <input type="hidden" name="durationHours" id="selectedDuration" value="2">
                    <input type="hidden" name="paymentMethod" id="selectedPaymentMethod" value="cash">
                    <input type="hidden" name="paymentReference" id="selectedPaymentRef" value="">
                    <button type="submit" class="btn btn-pedalya" id="confirmRentBtn"><i class="bi bi-key-fill"></i> Start Rental</button>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
    .payment-method-option { cursor: pointer; }
    .payment-method-option .border { transition: all 0.2s; }
    .payment-method-option.active .border,
    .payment-method-option input:checked + .border {
        border-color: var(--primary) !important;
        background: #f0f7ff;
        box-shadow: 0 0 0 2px var(--primary);
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    var currentRate = 25;

    function selectBicycle(id, name, battery, rate) {
        currentRate = rate;
        document.getElementById('selectedBicycleId').value = id;
        document.getElementById('modalBikeName').textContent = name;
        document.getElementById('modalBikeBattery').textContent = battery + '%';
        document.getElementById('modalBikeRate').textContent = '₱' + rate + '/hr';
        togglePaymentMethod('cash');
        updateCost();
        new bootstrap.Modal(document.getElementById('rentalModal')).show();
    }

    function updateCost() {
        var hours = document.getElementById('durationSlider').value;
        document.getElementById('durationDisplay').textContent = hours + (hours == 1 ? ' hour' : ' hours');
        var total = hours * currentRate;
        document.getElementById('costDisplay').textContent = '₱' + total.toFixed(2);
        document.getElementById('selectedDuration').value = hours;
        document.getElementById('gcashAmountDisplay').textContent = '₱' + total.toFixed(2);
    }

    function togglePaymentMethod(method) {
        document.getElementById('selectedPaymentMethod').value = method;
        var gcashArea = document.getElementById('gcashPaymentArea');
        var refInput = document.getElementById('gcashRefInput');
        var optCash = document.getElementById('payOptCash');
        var optGcash = document.getElementById('payOptGcash');
        var submitBtn = document.getElementById('confirmRentBtn');

        if (method === 'gcash') {
            gcashArea.classList.remove('d-none');
            refInput.required = true;
            optCash.classList.remove('active');
            optGcash.classList.add('active');
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit Payment';
            generateGcashQr();
        } else {
            gcashArea.classList.add('d-none');
            refInput.required = false;
            refInput.value = '';
            optCash.classList.add('active');
            optGcash.classList.remove('active');
            submitBtn.innerHTML = '<i class="bi bi-key-fill"></i> Start Rental';
        }
    }

    function generateGcashQr() {
        var canvas = document.getElementById('gcashQrCanvas');
        var amount = (document.getElementById('durationSlider').value * currentRate).toFixed(2);
        var text = 'gcash://pay?amount=' + amount + '&note=BicycleRental';
        canvas.width = 180;
        canvas.height = 180;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, 180, 180);
        var modules = generateQrMatrix(text);
        var size = modules.length;
        var cellSize = Math.floor(160 / size);
        var offset = Math.floor((180 - cellSize * size) / 2);
        ctx.fillStyle = '#000000';
        for (var r = 0; r < size; r++) {
            for (var c = 0; c < size; c++) {
                if (modules[r][c]) {
                    ctx.fillRect(offset + c * cellSize, offset + r * cellSize, cellSize, cellSize);
                }
            }
        }
    }

    function generateQrMatrix(text) {
        var len = text.length;
        var size = len < 20 ? 21 : len < 50 ? 25 : len < 100 ? 29 : 33;
        var matrix = [];
        for (var i = 0; i < size; i++) {
            matrix[i] = [];
            for (var j = 0; j < size; j++) {
                matrix[i][j] = false;
            }
        }
        drawFinder(matrix, 0, 0);
        drawFinder(matrix, size - 7, 0);
        drawFinder(matrix, 0, size - 7);
        for (var i = 8; i < size - 8; i++) {
            matrix[6][i] = (i % 2 === 0);
            matrix[i][6] = (i % 2 === 0);
        }
        var hash = 0;
        for (var k = 0; k < len; k++) {
            hash = ((hash << 5) - hash + text.charCodeAt(k)) | 0;
        }
        var seed = Math.abs(hash);
        for (var r = 9; r < size; r++) {
            for (var c = 9; c < size; c++) {
                if (matrix[r] && !matrix[r][c]) {
                    seed = (seed * 1103515245 + 12345) & 0x7fffffff;
                    matrix[r][c] = (seed % 3 === 0);
                }
            }
        }
        return matrix;
    }

    function drawFinder(matrix, row, col) {
        for (var r = 0; r < 7; r++) {
            for (var c = 0; c < 7; c++) {
                var isEdge = r === 0 || r === 6 || c === 0 || c === 6;
                var isInner = r >= 2 && r <= 4 && c >= 2 && c <= 4;
                matrix[row + r][col + c] = isEdge || isInner;
            }
        }
    }

    document.getElementById('rentalForm').addEventListener('submit', function(e) {
        if (!document.getElementById('rentalTerms').checked) {
            e.preventDefault();
            alert('Please accept the terms and conditions');
            return;
        }
        var method = document.getElementById('selectedPaymentMethod').value;
        if (method === 'gcash') {
            var ref = document.getElementById('gcashRefInput').value.trim();
            if (!ref) {
                e.preventDefault();
                alert('Please enter your GCash reference number');
                return;
            }
            document.getElementById('selectedPaymentRef').value = ref;
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
        var bicycles = <?php echo json_encode($bicycles->filter(fn($b) => $b->currentLat && $b->currentLng)->map(fn($b) => ['id' => $b->id, 'lat' => (float) $b->currentLat, 'lng' => (float) $b->currentLng, 'name' => $b->serialNumber . ' ' . $b->name, 'battery' => $b->batteryLevel, 'rate' => (float) $b->hourlyRate])); ?>;
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.rider', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views/rider/rent.blade.php ENDPATH**/ ?>