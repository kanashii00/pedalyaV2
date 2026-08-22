

<?php $__env->startSection('title', 'Rider Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<!-- Welcome Banner -->
<div class="rider-welcome mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4>Welcome, <?php echo e(explode(' ', $user->name)[0]); ?>! <i class="bi bi-bicycle ms-2"></i></h4>
            <p>Ready for your next ride? Find available bicycles near you and start cycling!</p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo e(route('rider.rentals.create')); ?>" class="btn btn-light btn-lg fw-semibold"><i class="bi bi-bicycle me-2"></i>Rent Now</a>
                <a href="<?php echo e(route('rider.rentals.index')); ?>" class="btn btn-outline-light btn-lg"><i class="bi bi-clock-history me-2"></i>View History</a>
            </div>
        </div>
        <div class="col-md-4 text-center mt-3 mt-md-0">
            <div class="d-inline-block p-3 rounded-circle" style="background:rgba(255,255,255,0.15);">
                <i class="bi bi-bicycle" style="font-size:4rem;color:rgba(255,255,255,0.9);"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="rider-stats mb-4">
    <div class="rider-stat-card fade-in-up">
        <div class="stat-icon-lg" style="background:#E8F5E9;color:#2E7D32;"><i class="bi bi-bicycle"></i></div>
        <div><div class="stat-value"><?php echo e($totalRentals); ?></div><div class="stat-label">Total Rentals</div></div>
    </div>
    <div class="rider-stat-card fade-in-up" style="animation-delay:0.1s;">
        <div class="stat-icon-lg" style="background:#E3F2FD;color:#1976D2;"><i class="bi bi-play-circle"></i></div>
        <div><div class="stat-value"><?php echo e($activeRental ? 1 : 0); ?></div><div class="stat-label">Active Rental</div></div>
    </div>
    <div class="rider-stat-card fade-in-up" style="animation-delay:0.2s;">
        <div class="stat-icon-lg" style="background:#FFF3E0;color:#F57C00;"><i class="bi bi-cash"></i></div>
        <div><div class="stat-value">₱<?php echo e(number_format($totalSpent, 2)); ?></div><div class="stat-label">Total Spent</div></div>
    </div>
    <div class="rider-stat-card fade-in-up" style="animation-delay:0.3s;">
        <div class="stat-icon-lg" style="background:#E8F5E9;color:#2E7D32;"><i class="bi bi-shield-check"></i></div>
        <div><span class="badge-status badge-verified"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> <?php echo e($user->verified ? 'Verified' : 'Unverified'); ?></span><div class="stat-label mt-1">Account Status</div></div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Rental -->
    <div class="col-lg-6">
        <div class="card-pedalya h-100">
            <div class="card-pedalya-header"><span><i class="bi bi-play-circle text-primary me-2"></i><strong>Active Rental</strong></span></div>
            <div class="card-pedalya-body" id="activeRentalCard">
                <?php if($activeRental): ?>
                    <div class="active-rental-card">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h6 class="mb-1">Bicycle <strong><?php echo e($activeRental->bicycle->serialNumber ?? 'N/A'); ?></strong> - <?php echo e($activeRental->bicycle->name ?? ''); ?></h6>
                                <p class="mb-2 text-muted" style="font-size:0.85rem;">Started: <?php echo e($activeRental->startTime->format('M d, g:i A')); ?></p>
                                <div class="d-flex gap-3">
                                    <span><i class="bi bi-clock text-primary"></i> <strong id="rentalTimer">0:00:00</strong></span>
                                    <span><i class="bi bi-battery-three-quarters text-success"></i> <?php echo e($activeRental->bicycle->batteryLevel ?? 0); ?>%</span>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="countdown-timer">
                                    <div class="countdown-item"><span class="countdown-value" id="cdHours">0</span><span class="countdown-label">Hrs</span></div>
                                    <div class="countdown-item"><span class="countdown-value" id="cdMins">0</span><span class="countdown-label">Min</span></div>
                                </div>
                                <p class="mt-2 mb-0 fw-bold text-primary">₱<?php echo e(number_format($activeRental->totalFee ?? 0, 2)); ?></p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-bicycle" style="font-size:3rem;color:var(--gray-300);"></i>
                        <p class="text-muted mt-2 mb-0">No active rental</p>
                        <a href="<?php echo e(route('rider.rentals.create')); ?>" class="btn btn-sm btn-pedalya mt-2"><i class="bi bi-bicycle me-1"></i> Rent Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent History -->
    <div class="col-lg-6">
        <div class="card-pedalya h-100">
            <div class="card-pedalya-header d-flex justify-content-between">
                <span><i class="bi bi-clock-history text-primary me-2"></i><strong>Recent Rentals</strong></span>
                <a href="<?php echo e(route('rider.rentals.index')); ?>" style="font-size:0.82rem;">View All</a>
            </div>
            <div class="card-pedalya-body p-0">
                <table class="table-pedalya mb-0">
                    <thead>
                        <tr>
                            <th>Bicycle</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentRentals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rental): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><strong><?php echo e($rental->bicycle->serialNumber ?? 'N/A'); ?></strong></td>
                                <td><?php echo e($rental->startTime->format('M d')); ?></td>
                                <td><?php echo e($rental->durationMinutes ? floor($rental->durationMinutes / 60) . 'h ' . ($rental->durationMinutes % 60) . 'm' : '—'); ?></td>
                                <td>₱<?php echo e(number_format($rental->totalFee ?? 0, 2)); ?></td>
                                <td>
                                    <?php if($rental->status === 'active'): ?>
                                        <span class="badge-status badge-active"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Active</span>
                                    <?php elseif($rental->status === 'completed'): ?>
                                        <span class="badge-status badge-completed"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Completed</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-cancelled"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> <?php echo e(ucfirst($rental->status)); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No rental history yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Map -->
<div class="card-pedalya mt-4">
    <div class="card-pedalya-header"><span><i class="bi bi-geo-alt-fill text-primary me-2"></i><strong>Nearby Available Bicycles</strong></span></div>
    <div style="height:300px;"><div id="riderMap" style="width:100%;height:100%;"></div></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php if($activeRental): ?>
<script>
    (function() {
        var startTime = new Date('<?php echo e($activeRental->startTime->toIso8601String()); ?>');
        var hourlyRate = <?php echo e($activeRental->bicycle->hourlyRate ?? 25); ?>;

        function updateTimer() {
            var now = new Date();
            var diff = Math.floor((now - startTime) / 1000);
            var h = Math.floor(diff / 3600);
            var m = Math.floor((diff % 3600) / 60);
            var s = diff % 60;
            document.getElementById('rentalTimer').textContent = h + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            document.getElementById('cdHours').textContent = h;
            document.getElementById('cdMins').textContent = m;
        }
        updateTimer();
        setInterval(updateTimer, 1000);
    })();
</script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('riderMap');
        if (!el) return;
        if (typeof maplibregl === 'undefined') {
            el.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 bg-light"><small class="text-muted">Map loading...</small></div>';
            return;
        }
        var bicycles = <?php echo json_encode($bicycles->filter(fn($b) => $b->currentLat && $b->currentLng)->map(fn($b) => ['lat' => (float) $b->currentLat, 'lng' => (float) $b->currentLng, 'name' => $b->serialNumber . ' - ' . $b->name, 'status' => $b->status])); ?>;
        var map = new maplibregl.Map({
            container: el,
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [125.6470, 7.0990],
            zoom: 14,
            attributionControl: true
        });
        map.addControl(new maplibregl.NavigationControl(), 'top-right');
        var markers = [];
        bicycles.forEach(function(b) {
            var color = b.status === 'available' ? '#2E7D32' : (b.status === 'rented' ? '#F57C00' : '#D32F2F');
            var marker = new maplibregl.Marker({ color: color })
                .setLngLat([b.lng, b.lat])
                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(
                    '<div style="padding:6px;font-family:Inter;"><strong>' + b.name + '</strong><br><span style="color:#2E7D32;text-transform:capitalize;">' + b.status + '</span></div>'
                ))
                .addTo(map);
            markers.push([b.lng, b.lat]);
        });
        if (markers.length > 1) {
            map.fitBounds(markers.reduce(function(b, c) {
                b.extend(c);
                return b;
            }, new maplibregl.LngLatBounds(markers[0], markers[0])), { padding: 60 });
        } else if (markers.length === 1) {
            map.setCenter(markers[0]);
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.rider', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\rider\dashboard.blade.php ENDPATH**/ ?>