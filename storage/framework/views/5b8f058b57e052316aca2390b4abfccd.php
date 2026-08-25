

<?php $__env->startSection('title', ($currentPage['title'] ?? 'Dashboard') . ' — Pedalya Admin'); ?>
<?php $__env->startSection('bodyClass', 'admin-shell'); ?>

<?php
    use App\Models\Accident;
    use App\Models\Bicycle;
    use App\Models\MaintenanceRecord;

    $unreadNotifs = auth()->user()->notifications()->where('read', false)->count();
    $recentNotifs = auth()->user()->notifications()->with('bicycle')->latest()->take(6)->get();
    $unackTheft = Accident::where('type', 'theft')->where('acknowledged', false)->count();
    $unackAccidents = Accident::where('acknowledged', false)->count();
    $pendingMaint = MaintenanceRecord::whereIn('status', ['scheduled', 'in_progress'])->count();
    $pendingScans = \App\Models\IdScan::where('status', 'pending')->count();

    $iotOnline = Bicycle::where('lastHeartbeat', '>=', now()->subMinutes(5))->count();
    $gpsOnline = Bicycle::where('lastGpsUpdate', '>=', now()->subMinutes(5))->count();

    $currentSection = in_array(request()->query('section'), ['map', 'gps', 'locks', 'devices'], true)
        ? request()->query('section')
        : 'map';

    $navGroups = [
        [
            'label' => 'Dashboard',
            'icon' => 'bi-grid-1x2',
            'items' => [
                ['title' => 'Dashboard Overview', 'icon' => 'bi-grid-1x2', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
                ['title' => 'Analytics', 'icon' => 'bi-graph-up-arrow', 'url' => route('admin.dashboard') . '#analytics', 'active' => []],
            ],
        ],
        [
            'label' => 'Customer Management',
            'icon' => 'bi-people',
            'items' => [
                ['title' => 'Customer List', 'icon' => 'bi-person-lines-fill', 'route' => 'admin.riders.index', 'active' => ['admin.riders.index']],
                ['title' => 'Register Customer', 'icon' => 'bi-person-plus', 'route' => 'admin.riders.create', 'active' => ['admin.riders.create']],
                ['title' => 'Automated ID Scanner', 'icon' => 'bi-person-badge', 'route' => 'admin.id-scans.create', 'active' => ['admin.id-scans.create'], 'badge' => $pendingScans, 'badgeType' => 'warning'],
                ['title' => 'ID Scan Records', 'icon' => 'bi-files', 'route' => 'admin.id-scans.index', 'active' => ['admin.id-scans.index']],
                ['title' => 'Verified Customers', 'icon' => 'bi-patch-check', 'route' => 'admin.riders.verified', 'active' => ['admin.riders.verified']],
                ['title' => 'Blacklisted Customers', 'icon' => 'bi-x-octagon', 'route' => 'admin.riders.blacklisted', 'active' => ['admin.riders.blacklisted']],
            ],
        ],
        [
            'label' => 'Bicycle Management',
            'icon' => 'bi-bicycle',
            'items' => [
                ['title' => 'Bicycle Inventory', 'icon' => 'bi-bicycle', 'route' => 'admin.bicycles.index', 'active' => ['admin.bicycles.index']],
                ['title' => 'Add Bicycle', 'icon' => 'bi-plus-circle', 'route' => 'admin.bicycles.create', 'active' => ['admin.bicycles.create']],
                ['title' => 'Bicycle Status', 'icon' => 'bi-speedometer', 'route' => 'admin.bicycles.index', 'active' => ['admin.bicycles.index'], 'query' => '?filter=status'],
                ['title' => 'Maintenance Schedule', 'icon' => 'bi-tools', 'route' => 'admin.maintenance.index', 'active' => ['admin.maintenance.*'], 'badge' => $pendingMaint],
            ],
        ],
        [
            'label' => 'Rental Management',
            'icon' => 'bi-key',
            'items' => [
                ['title' => 'Active Rentals', 'icon' => 'bi-play-circle', 'route' => 'admin.rentals.index', 'active' => ['admin.rentals.index']],
                ['title' => 'Rental History', 'icon' => 'bi-clock-history', 'route' => 'admin.rentals.history', 'active' => ['admin.rentals.history']],
                ['title' => 'Returns', 'icon' => 'bi-arrow-return-left', 'route' => 'admin.rentals.index', 'active' => ['admin.rentals.index'], 'query' => '?filter=returns'],
                ['title' => 'Rental Requests', 'icon' => 'bi-inbox', 'route' => 'admin.rentals.index', 'active' => ['admin.rentals.index'], 'query' => '?filter=pending'],
            ],
        ],
        [
            'label' => 'Incidents & Alerts',
            'icon' => 'bi-shield-check',
            'items' => [
                ['title' => 'Accident Monitoring', 'icon' => 'bi-activity', 'route' => 'admin.accidents.index', 'active' => ['admin.accidents.*'], 'badge' => $unackAccidents, 'badgeType' => 'warning'],
                ['title' => 'Notifications', 'icon' => 'bi-bell', 'route' => 'admin.notifications.index', 'active' => ['admin.notifications.*'], 'badge' => $unreadNotifs],
                ['title' => 'Incident Reports', 'icon' => 'bi-journal-alert', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'query' => '?tab=incident'],
                [
                    'label' => 'Monitoring',
                    'title' => 'Monitoring',
                    'icon' => 'bi-grid',
                    'active' => ['admin.monitoring.*', 'admin.geofence.*', 'admin.theft-alerts.*'],
                    'items' => [
                        ['title' => 'GeoLibre 3D Map', 'icon' => 'bi-map', 'route' => 'admin.monitoring.index', 'section' => 'map', 'sub' => 'Live 3D fleet map', 'active' => ['admin.monitoring.index']],
                        ['title' => 'Live GPS Tracking', 'icon' => 'bi-geo-alt', 'route' => 'admin.monitoring.index', 'section' => 'gps', 'query' => '?section=gps', 'sub' => 'Real-time positions', 'active' => ['admin.monitoring.index']],
                        ['title' => 'Circular Geofence', 'icon' => 'bi-bounding-box-circles', 'route' => 'admin.geofence.index', 'sub' => 'Azuela Cove riding zone', 'active' => ['admin.geofence.*']],
                        ['title' => 'Smart Lock Control', 'icon' => 'bi-lock', 'route' => 'admin.monitoring.index', 'section' => 'locks', 'query' => '?section=locks', 'sub' => 'Remote lock / unlock', 'active' => ['admin.monitoring.index']],
                        ['title' => 'IoT Device Monitoring', 'icon' => 'bi-cpu', 'route' => 'admin.monitoring.index', 'section' => 'devices', 'query' => '?section=devices', 'sub' => 'Firmware & telemetry', 'active' => ['admin.monitoring.index']],
                        ['title' => 'Theft Detection', 'icon' => 'bi-shield-exclamation', 'route' => 'admin.theft-alerts.index', 'sub' => 'Alerts & incident log', 'badge' => $unackTheft, 'badgeType' => 'danger', 'active' => ['admin.theft-alerts.*']],
                    ],
                ],
            ],
        ],
        [
            'label' => 'Reports',
            'icon' => 'bi-file-earmark-bar-graph',
            'items' => [
                ['title' => 'Customer Reports', 'icon' => 'bi-people', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'query' => '?tab=customer'],
                ['title' => 'Rental Reports', 'icon' => 'bi-bicycle', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'query' => '?tab=rental'],
                ['title' => 'Bicycle Reports', 'icon' => 'bi-speedometer', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'query' => '?tab=bicycle'],
                ['title' => 'Theft Reports', 'icon' => 'bi-shield-exclamation', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'query' => '?tab=theft'],
                ['title' => 'Accident Reports', 'icon' => 'bi-activity', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'query' => '?tab=accident'],
                ['title' => 'Export Reports', 'icon' => 'bi-download', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'query' => '?tab=export'],
            ],
        ],
        [
            'label' => 'Administration',
            'icon' => 'bi-gear',
            'items' => [
                ['title' => 'User Management', 'icon' => 'bi-person-gear', 'route' => 'admin.riders.index', 'active' => ['admin.riders.index'], 'query' => '?tab=users'],
                ['title' => 'Roles and Permissions', 'icon' => 'bi-shield-lock', 'route' => 'admin.settings.index', 'active' => ['admin.settings.*'], 'query' => '?tab=roles'],
                ['title' => 'Audit Logs', 'icon' => 'bi-journal-text', 'route' => 'admin.audit-log.index', 'active' => ['admin.audit-log.index']],
                ['title' => 'Activity Logs', 'icon' => 'bi-activity', 'route' => 'admin.audit-log.index', 'active' => ['admin.audit-log.index'], 'query' => '?tab=activity'],
                ['title' => 'System Settings', 'icon' => 'bi-gear-wide-connected', 'route' => 'admin.settings.index', 'active' => ['admin.settings.*']],
            ],
        ],
    ];

    // Flatten all leaf nav links for sibling detection (excludes parent group items)
    $allNavLinks = [];
    foreach ($navGroups as $group) {
        foreach ($group['items'] as $item) {
            if (isset($item['items'])) {
                foreach ($item['items'] as $subItem) { $allNavLinks[] = $subItem; }
            } else {
                $allNavLinks[] = $item;
            }
        }
    }

    $isActive = function ($item) use ($currentSection, $allNavLinks) {
        if (!isset($item['active'])) return false;

        $routeMatch = collect($item['active'])->contains(fn ($p) => request()->routeIs($p));
        if (!$routeMatch) return false;

        // Section check (monitoring sub-items: map / gps / locks / devices)
        if (isset($item['section']) && $currentSection !== $item['section']) return false;

        // Item declares specific query params → every key-value must match
        if (isset($item['query'])) {
            $expected = [];
            parse_str(ltrim($item['query'], '?'), $expected);
            foreach ($expected as $k => $v) {
                if ((string) request()->query($k) !== (string) $v) return false;
            }
            return true;
        }

        // Default item (no query) → active only when NO sibling with the same
        // route patterns has its query params satisfied by the current request.
        $patterns = $item['active'];
        foreach ($allNavLinks as $sibling) {
            if ($sibling === $item) continue;
            if (!isset($sibling['query'])) continue;
            if (($sibling['active'] ?? []) !== $patterns) continue;
            $siblingParams = [];
            parse_str(ltrim($sibling['query'], '?'), $siblingParams);
            $allMatch = true;
            foreach ($siblingParams as $k => $v) {
                if ((string) request()->query($k) !== (string) $v) { $allMatch = false; break; }
            }
            if ($allMatch) return false;
        }
        return true;
    };

    $currentPage = null;
    $currentGroup = null;
    foreach ($navGroups as $group) {
        foreach ($group['items'] as $item) {
            // Check top-level items
            if ($isActive($item)) { $currentPage = $item; $currentGroup = $group['label']; break 2; }
            // Check nested items (sub-groups)
            if (isset($item['items'])) {
                foreach ($item['items'] as $subItem) {
                    if ($isActive($subItem)) { $currentPage = $subItem; $currentGroup = $group['label']; break 3; }
                }
            }
        }
    }
    if (!$currentPage) { $currentPage = ['title' => 'Dashboard', 'icon' => 'bi-grid-1x2']; $currentGroup = 'Dashboard'; }

    $notifIcon = fn ($type) => match ($type) {
        'theft', 'accident' => 'bi-exclamation-triangle',
        'rental' => 'bi-key',
        'maintenance' => 'bi-tools',
        'system' => 'bi-gear',
        'success' => 'bi-check-circle',
        default => 'bi-info-circle',
    };
    $notifColor = fn ($type) => match ($type) {
        'theft', 'accident' => 'var(--danger)',
        'rental' => 'var(--accent)',
        'maintenance' => 'var(--warning)',
        'success' => 'var(--success)',
        default => 'var(--info)',
    };
?>

<?php $__env->startSection('body'); ?>


<div class="sidebar-overlay"></div>


<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar__brand">
        <div class="admin-sidebar__logo">
            <img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya" style="width:60px;height:60px;border-radius:16px;object-fit:cover;">
        </div>
        <div class="admin-sidebar__name">Peda<span>lya</span></div>
    </div>

    <nav class="admin-sidebar__nav" id="adminSidebarNav">
        <?php $__currentLoopData = $navGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $groupLabel = $group['label']; ?>
            <div class="admin-navgroup__label"><?php echo e($groupLabel); ?></div>

            <?php
                $hasNested = false;
                foreach ($group['items'] as $item) {
                    if (isset($item['items'])) { $hasNested = true; break; }
                }
                $isSingleItem = count($group['items']) === 1 && !$hasNested;
            ?>

            <?php if($hasNested): ?>
                
                <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(isset($item['items'])): ?>
                        
                        <?php $subGroupOpen = collect($item['items'])->contains($isActive); ?>
                        <div class="admin-nav <?php echo e($subGroupOpen ? 'admin-nav--open' : ''); ?>" data-collapsible>
                            <button type="button" class="admin-nav__link" data-tooltip="<?php echo e($item['title']); ?>">
                                <i class="bi <?php echo e($item['icon']); ?>"></i>
                                <span><?php echo e($item['title']); ?></span>
                                <i class="bi bi-chevron-down admin-nav__toggle"></i>
                            </button>
                            <div class="admin-nav__sub">
                                <?php $__currentLoopData = $item['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $subHref = isset($subItem['route']) ? route($subItem['route']) : ($subItem['url'] ?? '#');
                                        if (isset($subItem['query'])) $subHref .= $subItem['query'];
                                    ?>
                                    <a href="<?php echo e($subHref); ?>"
                                       class="admin-nav__link <?php echo e($isActive($subItem) ? 'active' : ''); ?>"
                                       data-tooltip="<?php echo e($subItem['title']); ?>"
                                       aria-current="<?php echo e($isActive($subItem) ? 'page' : 'false'); ?>">
                                        <i class="bi <?php echo e($subItem['icon']); ?>"></i>
                                        <span><?php echo e($subItem['title']); ?></span>
                                        <?php if(isset($subItem['badge']) && $subItem['badge'] > 0): ?>
                                            <span class="admin-nav__badge <?php echo e(isset($subItem['badgeType']) ? 'admin-nav__badge--' . $subItem['badgeType'] : ''); ?>"><?php echo e($subItem['badge'] > 99 ? '99+' : $subItem['badge']); ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <?php
                            $href = isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
                            if (isset($item['query'])) $href .= $item['query'];
                        ?>
                        <a href="<?php echo e($href); ?>"
                           class="admin-nav__link <?php echo e($isActive($item) ? 'active' : ''); ?>"
                           data-tooltip="<?php echo e($item['title']); ?>"
                           aria-current="<?php echo e($isActive($item) ? 'page' : 'false'); ?>">
                            <i class="bi <?php echo e($item['icon']); ?>"></i>
                            <span><?php echo e($item['title']); ?></span>
                            <?php if(isset($item['badge']) && $item['badge'] > 0): ?>
                                <span class="admin-nav__badge <?php echo e(isset($item['badgeType']) ? 'admin-nav__badge--' . $item['badgeType'] : ''); ?>"><?php echo e($item['badge'] > 99 ? '99+' : $item['badge']); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php elseif($isSingleItem): ?>
                
                <?php
                    $singleItem = $group['items'][0];
                    $singleHref = isset($singleItem['route']) ? route($singleItem['route']) : ($singleItem['url'] ?? '#');
                    if (isset($singleItem['query'])) $singleHref .= $singleItem['query'];
                ?>
                <a href="<?php echo e($singleHref); ?>"
                   class="admin-nav__link <?php echo e($isActive($singleItem) ? 'active' : ''); ?>"
                   data-tooltip="<?php echo e($singleItem['title']); ?>"
                   aria-current="<?php echo e($isActive($singleItem) ? 'page' : 'false'); ?>">
                    <i class="bi <?php echo e($singleItem['icon']); ?>"></i>
                    <span><?php echo e($singleItem['title']); ?></span>
                    <?php if(isset($singleItem['badge']) && $singleItem['badge'] > 0): ?>
                        <span class="admin-nav__badge <?php echo e(isset($singleItem['badgeType']) ? 'admin-nav__badge--' . $singleItem['badgeType'] : ''); ?>"><?php echo e($singleItem['badge'] > 99 ? '99+' : $singleItem['badge']); ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                
                <?php $groupOpen = collect($group['items'])->contains($isActive); ?>
                <div class="admin-nav <?php echo e($groupOpen ? 'admin-nav--open' : ''); ?>" data-collapsible>
                    <button type="button" class="admin-nav__link" data-tooltip="<?php echo e($group['label']); ?>">
                        <i class="bi <?php echo e($group['icon']); ?>"></i>
                        <span><?php echo e($group['label']); ?></span>
                        <i class="bi bi-chevron-down admin-nav__toggle"></i>
                    </button>
                    <div class="admin-nav__sub">
                        <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $href = isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
                                if (isset($item['query'])) $href .= $item['query'];
                            ?>
                            <a href="<?php echo e($href); ?>"
                               class="admin-nav__link <?php echo e($isActive($item) ? 'active' : ''); ?>"
                               data-tooltip="<?php echo e($item['title']); ?>"
                               aria-current="<?php echo e($isActive($item) ? 'page' : 'false'); ?>">
                                <i class="bi <?php echo e($item['icon']); ?>"></i>
                                <span><?php echo e($item['title']); ?></span>
                                <?php if(isset($item['badge']) && $item['badge'] > 0): ?>
                                    <span class="admin-nav__badge <?php echo e(isset($item['badgeType']) ? 'admin-nav__badge--' . $item['badgeType'] : ''); ?>"><?php echo e($item['badge'] > 99 ? '99+' : $item['badge']); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
</aside>


<div class="admin-main">

    
    <header class="admin-topbar">
        <div class="admin-topbar__left">
            <button class="admin-icon-btn" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="admin-topbar__title">
                <h1 id="adminPageTitle"><?php echo e($currentPage['title']); ?></h1>
                <div class="admin-topbar__crumb">
                    <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="bi bi-house-door"></i></a>
                    <span class="sep">/</span>
                    <span><?php echo e($currentGroup); ?></span>
                    <?php if($currentGroup !== $currentPage['title']): ?>
                        <span class="sep">/</span>
                        <span><?php echo e($currentPage['title']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="admin-search">
            <i class="bi bi-search"></i>
            <input type="text" id="adminSearchInput" placeholder="Search modules..." autocomplete="off">
            <kbd>Ctrl K</kbd>
            <div class="admin-search__results" id="adminSearchResults"></div>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
            
            <div class="admin-conn online" id="connIoT" data-on="IoT Online" data-off="IoT Offline" title="IoT Connection">
                <span class="dot"></span><span class="label">IoT</span>
            </div>
            <div class="admin-conn online" id="connGPS" data-on="GPS Online" data-off="GPS Offline" title="GPS Connection">
                <span class="dot"></span><span class="label">GPS</span>
            </div>
            <div class="admin-conn online" id="connWS" data-on="WS Online" data-off="WS Offline" title="WebSocket">
                <span class="dot"></span><span class="label">WS</span>
            </div>

            <div class="admin-clock" id="adminClock"></div>

            
            <button class="admin-icon-btn" id="themeToggle" type="button" aria-label="Toggle theme">
                <i class="bi bi-moon"></i>
            </button>

            
            <div class="admin-dropdown">
                <button class="admin-icon-btn" type="button" data-dropdown-toggle aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if($unreadNotifs > 0): ?>
                        <span class="admin-nav__badge" style="position:absolute;top:-4px;right:-4px;"><?php echo e($unreadNotifs > 99 ? '99+' : $unreadNotifs); ?></span>
                    <?php endif; ?>
                </button>
                <div class="admin-dropdown__menu">
                    <div class="admin-dropdown__head">
                        <span>Notifications</span>
                        <span class="badge-admin badge-admin--neutral badge-admin--plain"><?php echo e($unreadNotifs); ?> unread</span>
                    </div>
                    <div class="admin-dropdown__body">
                        <?php $__empty_1 = true; $__currentLoopData = $recentNotifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="<?php echo e(route('admin.notifications.index')); ?>" class="admin-notif <?php echo e(!$n->read ? 'unread' : ''); ?>">
                                <div class="admin-notif__icon" style="background: <?php echo e($notifColor($n->type)); ?>22; color: <?php echo e($notifColor($n->type)); ?>;">
                                    <i class="bi <?php echo e($notifIcon($n->type)); ?>"></i>
                                </div>
                                <div>
                                    <div class="admin-notif__title"><?php echo e($n->title); ?></div>
                                    <div class="admin-notif__msg"><?php echo e(\Illuminate\Support\Str::limit($n->message, 60)); ?></div>
                                    <div class="admin-notif__time"><?php echo e($n->created_at->diffForHumans()); ?></div>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="admin-empty">
                                <i class="bi bi-bell-slash"></i>
                                <h4>All caught up</h4>
                                <p>No notifications yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-dropdown__foot">
                        <a href="<?php echo e(route('admin.notifications.index')); ?>" class="btn-admin btn-admin--secondary btn-admin--sm">View all notifications</a>
                    </div>
                </div>
            </div>

            
            <div class="admin-dropdown">
                <div class="admin-profile" data-dropdown-toggle>
                    <div class="admin-avatar"><?php echo e(auth()->user()->initials); ?></div>
                    <div class="admin-profile__meta">
                        <div class="admin-profile__name"><?php echo e(auth()->user()->name); ?></div>
                        <div class="admin-profile__role">Administrator</div>
                    </div>
                    <i class="bi bi-chevron-down admin-profile__caret"></i>
                </div>
                <div class="admin-dropdown__menu">
                    <div class="admin-dropdown__head"><?php echo e(auth()->user()->email); ?></div>
                    <div class="admin-dropdown__body" style="padding:8px;">
                        <a class="admin-search__item" href="<?php echo e(route('admin.settings.index')); ?>"><i class="bi bi-person"></i>Profile settings</a>
                        <a class="admin-search__item" href="<?php echo e(route('admin.settings.index')); ?>"><i class="bi bi-gear"></i>System settings</a>
                        <a class="admin-search__item" href="<?php echo e(route('admin.audit-log.index')); ?>"><i class="bi bi-journal-text"></i>Audit log</a>
                    </div>
                    <div class="admin-dropdown__foot">
                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn-admin btn-admin--danger btn-admin--block">
                                <i class="bi bi-box-arrow-right"></i> Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    
    <main class="admin-content">
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

        <?php if (! empty(trim($__env->yieldContent('page-header')))): ?>
            <div class="admin-pagehead">
                <div class="admin-pagehead__title"><?php echo $__env->yieldContent('page-header'); ?></div>
                <?php if (! empty(trim($__env->yieldContent('actions')))): ?>
                    <div class="admin-pagehead__actions"><?php echo $__env->yieldContent('actions'); ?></div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="admin-pagehead">
                <div class="admin-pagehead__title">
                    <h1><?php echo e($currentPage['title']); ?></h1>
                </div>
                <?php if (! empty(trim($__env->yieldContent('actions')))): ?>
                    <div class="admin-pagehead__actions"><?php echo $__env->yieldContent('actions'); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>


<div class="admin-toasts" id="adminToasts"></div>


<div class="admin-modal" id="adminConfirm">
    <div class="admin-modal__backdrop"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__head">
            <h3 id="adminConfirmTitle">Are you sure?</h3>
            <button class="admin-icon-btn" data-modal-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="admin-modal__body">
            <p id="adminConfirmMsg" style="color: var(--text-2); margin:0;">This action cannot be undone.</p>
        </div>
        <div class="admin-modal__foot">
            <button class="btn-admin btn-admin--secondary" id="adminConfirmCancel">Cancel</button>
            <button class="btn-admin btn-admin--danger" id="adminConfirmOk">Confirm</button>
        </div>
    </div>
</div>

<script>
    window.PedalyaStatus = { iot: <?php echo e($iotOnline > 0 ? 'true' : 'false'); ?>, gps: <?php echo e($gpsOnline > 0 ? 'true' : 'false'); ?> };
    window.PedalyaChannels = { notifications: <?php echo json_encode('private-App.Models.User.' . auth()->id()); ?> };
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?php echo e(asset('js/admin.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views/layouts/admin.blade.php ENDPATH**/ ?>