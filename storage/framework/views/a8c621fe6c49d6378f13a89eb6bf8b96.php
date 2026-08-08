

<?php $__env->startSection('title', 'My Rental History'); ?>

<?php $__env->startSection('content'); ?>
<!-- Filters -->
<div class="card-pedalya mb-4">
    <div class="card-pedalya-body">
        <form method="GET" action="<?php echo e(route('rider.rentals.index')); ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label-pedalya">From</label>
                    <input type="date" class="form-control-pedalya" name="from" value="<?php echo e(request('from')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label-pedalya">To</label>
                    <input type="date" class="form-control-pedalya" name="to" value="<?php echo e(request('to')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label-pedalya">Status</label>
                    <select class="form-select form-control-pedalya" name="status">
                        <option value="">All</option>
                        <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed</option>
                        <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-pedalya btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="<?php echo e(route('rider.rentals.index')); ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" style="font-size:1.4rem;"><?php echo e($totalRentals); ?></div>
                    <div class="stat-label">Total Rentals</div>
                </div>
                <div class="stat-icon" style="background:#E8F5E9;color:#2E7D32;"><i class="bi bi-bicycle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" style="font-size:1.4rem;">₱<?php echo e(number_format($totalSpent, 2)); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-icon" style="background:#FFF3E0;color:#F57C00;"><i class="bi bi-cash"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" style="font-size:1.4rem;"><?php echo e($totalTime); ?></div>
                    <div class="stat-label">Total Time</div>
                </div>
                <div class="stat-icon" style="background:#E3F2FD;color:#1976D2;"><i class="bi bi-clock"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card-pedalya">
    <div class="card-pedalya-header"><span><strong>Rental History</strong></span></div>
    <div class="table-responsive">
        <table class="table table-pedalya mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bicycle</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Duration</th>
                    <th>Cost</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $rentals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rental): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($rental->rentalId ?? 'R-' . str_pad($rental->id, 4, '0', STR_PAD_LEFT)); ?></strong></td>
                        <td><?php echo e($rental->bicycle->serialNumber ?? 'N/A'); ?> - <?php echo e($rental->bicycle->name ?? ''); ?></td>
                        <td><?php echo e($rental->startTime?->format('M d, g:i A') ?? '—'); ?></td>
                        <td><?php echo e($rental->endTime ? $rental->endTime->format('M d, g:i A') : '—'); ?></td>
                        <td><?php echo e($rental->durationFormatted ?? ($rental->durationMinutes ? floor($rental->durationMinutes / 60) . 'h ' . ($rental->durationMinutes % 60) . 'm' : '—')); ?></td>
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
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewRental(<?php echo e($rental->id); ?>)"><i class="bi bi-eye"></i></button>
                            <?php if($rental->status === 'completed'): ?>
                                <button class="btn btn-sm btn-outline-secondary ms-1" onclick="downloadReceipt('<?php echo e($rental->rentalId ?? 'R-' . str_pad($rental->id, 4, '0', STR_PAD_LEFT)); ?>')"><i class="bi bi-download"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-clock-history" style="font-size:2rem;"></i>
                            <p class="mt-2 mb-0">No rental history found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($rentals->hasPages()): ?>
        <div class="card-pedalya-body d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing <?php echo e($rentals->firstItem()); ?>-<?php echo e($rentals->lastItem()); ?> of <?php echo e($rentals->total()); ?> rentals</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php echo e($rentals->links()); ?>

                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Rental Detail Modal -->
<div class="modal fade" id="rentalDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rental Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-sm-6"><label class="form-label-pedalya">Rental ID</label><p><strong id="detailRentalId">—</strong></p></div>
                    <div class="col-sm-6"><label class="form-label-pedalya">Bicycle</label><p id="detailBicycle">—</p></div>
                    <div class="col-sm-6"><label class="form-label-pedalya">Start Time</label><p id="detailStart">—</p></div>
                    <div class="col-sm-6"><label class="form-label-pedalya">End Time</label><p id="detailEnd">—</p></div>
                    <div class="col-sm-6"><label class="form-label-pedalya">Duration</label><p id="detailDuration">—</p></div>
                    <div class="col-sm-6"><label class="form-label-pedalya">Total Cost</label><p><strong class="text-primary" id="detailCost">—</strong></p></div>
                    <div class="col-sm-6"><label class="form-label-pedalya">Status</label><p id="detailStatus">—</p></div>
                    <div class="col-sm-6"><label class="form-label-pedalya">Payment</label><p id="detailPayment">—</p></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    var rentalsData = <?php echo json_encode($rentals->map(fn($r) => [
        'id' => $r->id,
        'rentalId' => $r->rentalId ?? 'R-' . str_pad($r->id, 4, '0', STR_PAD_LEFT),
        'bicycle' => ($r->bicycle->serialNumber ?? 'N/A') . ' - ' . ($r->bicycle->name ?? ''),
        'start' => $r->startTime?->format('M d, Y g:i A') ?? '—',
        'end' => $r->endTime ? $r->endTime->format('M d, Y g:i A') : '—',
        'duration' => $r->durationFormatted ?? ($r->durationMinutes ? floor($r->durationMinutes / 60) . 'h ' . ($r->durationMinutes % 60) . 'm' : '—'),
        'cost' => '₱' . number_format($r->totalFee ?? 0, 2),
        'status' => $r->status,
        'payment' => $r->paymentStatus ?? 'pending',
    ])); ?>;

    function viewRental(id) {
        var rental = rentalsData.find(function(r) { return r.id === id; });
        if (!rental) return;
        document.getElementById('detailRentalId').textContent = rental.rentalId;
        document.getElementById('detailBicycle').textContent = rental.bicycle;
        document.getElementById('detailStart').textContent = rental.start;
        document.getElementById('detailEnd').textContent = rental.end;
        document.getElementById('detailDuration').textContent = rental.duration;
        document.getElementById('detailCost').textContent = rental.cost;
        document.getElementById('detailPayment').textContent = rental.payment;
        var statusHtml = '';
        if (rental.status === 'active') statusHtml = '<span class="badge-status badge-active"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Active</span>';
        else if (rental.status === 'completed') statusHtml = '<span class="badge-status badge-completed"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Completed</span>';
        else statusHtml = '<span class="badge-status badge-cancelled"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> ' + rental.status.charAt(0).toUpperCase() + rental.status.slice(1) + '</span>';
        document.getElementById('detailStatus').innerHTML = statusHtml;
        new bootstrap.Modal(document.getElementById('rentalDetailModal')).show();
    }

    function downloadReceipt(id) {
        alert('Receipt for ' + id + ' downloading...');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.rider', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\flutter\Projects\4th_year\pedalya\resources\views/rider/history.blade.php ENDPATH**/ ?>