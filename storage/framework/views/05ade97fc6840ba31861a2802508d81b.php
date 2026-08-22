

<?php $__env->startSection('body'); ?>
<div>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo-wrapper">
                <img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya" class="sidebar-logo-img">
            </div>
            <span class="sidebar-brand-text"><span>Peda</span>lya</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">Menu</div>
            <div class="nav-item">
                <a href="<?php echo e(route('rider.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('rider.dashboard') ? 'active' : ''); ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?php echo e(route('rider.rentals.create')); ?>" class="nav-link <?php echo e(request()->routeIs('rider.rentals.create') ? 'active' : ''); ?>">
                    <i class="bi bi-bicycle"></i>
                    <span>Rent Bicycle</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?php echo e(route('rider.rentals.index')); ?>" class="nav-link <?php echo e(request()->routeIs('rider.rentals.index') ? 'active' : ''); ?>">
                    <i class="bi bi-key-fill"></i>
                    <span>My Rentals</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?php echo e(route('rider.notifications.index')); ?>" class="nav-link <?php echo e(request()->routeIs('rider.notifications.*') ? 'active' : ''); ?>">
                    <i class="bi bi-bell-fill"></i>
                    <span>Notifications</span>
                </a>
            </div>

            <div class="nav-section">Account</div>
            <div class="nav-item">
                <a href="<?php echo e(route('rider.profile.index')); ?>" class="nav-link <?php echo e(request()->routeIs('rider.profile.*') ? 'active' : ''); ?>">
                    <i class="bi bi-person-fill"></i>
                    <span>Profile</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <a href="<?php echo e(route('rider.dashboard')); ?>" class="topbar-brand">
                    <div class="topbar-logo-wrapper">
                        <img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya" class="topbar-logo-img">
                    </div>
                    <span><span>Peda</span>lya</span>
                </a>
            </div>
            <div class="topbar-right">
                <div class="notification-bell">
                    <a href="<?php echo e(route('rider.notifications.index')); ?>">
                        <i class="bi bi-bell"></i>
                        <?php if(auth()->check() && auth()->user()->notifications()->where('read', false)->count() > 0): ?>
                            <span class="notification-badge">
                                <?php echo e(auth()->user()->notifications()->where('read', false)->count()); ?>

                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                    </div>
                    <div>
                        <div class="user-name"><?php echo e(auth()->user()->name); ?></div>
                        <div class="user-role">Rider</div>
                    </div>
                </div>
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <?php if(session('success')): ?>
                <div class="alert alert-pedalya alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-pedalya alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-pedalya alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>Please correct the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function collapseSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 991) {
                    toggleSidebar();
                } else {
                    collapseSidebar();
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth > 991) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\layouts\rider.blade.php ENDPATH**/ ?>