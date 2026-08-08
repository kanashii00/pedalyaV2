

<?php $__env->startSection('title', 'Notifications'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-pedalya">
            <div class="card-pedalya-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span><strong>Notifications</strong></span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" onclick="filterNotif('all', this)">All</button>
                        <button class="btn btn-outline-secondary" onclick="filterNotif('unread', this)">Unread</button>
                        <button class="btn btn-outline-secondary" onclick="filterNotif('rental', this)">Rentals</button>
                        <button class="btn btn-outline-secondary" onclick="filterNotif('system', this)">System</button>
                    </div>
                </div>
                <form action="<?php echo e(route('rider.notifications.mark-all-read')); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-check-all"></i> Mark All Read</button>
                </form>
            </div>
            <div class="notification-list" id="riderNotifList">
                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="notification-item <?php echo e($notification->readAt ? '' : 'unread'); ?>" data-type="<?php echo e($notification->type ?? 'system'); ?>">
                        <div class="notification-icon" style="background: <?php echo e($notification->type === 'rental' ? '#E8F5E9; color: #2E7D32' : '#E3F2FD; color: #1976D2'); ?>;">
                            <?php if($notification->type === 'rental'): ?>
                                <i class="bi bi-key-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-info-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-text"><?php echo $notification->message; ?></div>
                            <div class="notification-time"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                        </div>
                        <div class="notification-actions">
                            <?php if(!$notification->readAt): ?>
                                <form action="<?php echo e(route('rider.notifications.mark-read', $notification->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-link" title="Mark as read"><i class="bi bi-envelope-open"></i></button>
                                </form>
                            <?php else: ?>
                                <span class="btn btn-sm btn-link text-muted" title="Read"><i class="bi bi-envelope"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-bell-slash"></i>
                            <p>No notifications yet</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function filterNotif(type, btn) {
        document.querySelectorAll('.btn-group .btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        document.querySelectorAll('#riderNotifList .notification-item').forEach(function(item) {
            if (type === 'all') {
                item.style.display = '';
            } else if (type === 'unread') {
                item.style.display = item.classList.contains('unread') ? '' : 'none';
            } else {
                item.style.display = item.dataset.type === type ? '' : 'none';
            }
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.rider', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\flutter\Projects\4th_year\pedalya\resources\views/rider/notifications.blade.php ENDPATH**/ ?>