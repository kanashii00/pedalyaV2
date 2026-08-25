<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Pedalya - <?php echo $__env->yieldContent('title', 'Bicycle Rental Management'); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- MapLibre GL JS (GeoLibre / OpenStreetMap, no API key required) -->
    <link href="https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

    <!-- Custom Styles -->
    <link href="<?php echo e(asset('css/style.css')); ?>" rel="stylesheet">

    <!-- Admin shell (scoped to body.admin-shell, safe to load globally) -->
    <link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">

    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body class="<?php echo $__env->yieldContent('bodyClass', ''); ?>">
    <?php echo $__env->yieldContent('body'); ?>

    <!-- Bootstrap 5.3.2 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Laravel Echo + Pusher (compatible with Laravel Reverb) -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.js"></script>

    <script>
        window.Pedalya = {
            baseUrl: <?php echo json_encode(url('/'), 15, 512) ?>,
            apiBase: <?php echo json_encode(url('/api'), 15, 512) ?>,
            csrfToken: <?php echo json_encode(csrf_token(), 15, 512) ?>,
            reverb: {
                key: <?php echo json_encode(config('broadcasting.connections.reverb.key'), 15, 512) ?>,
                host: <?php echo json_encode(config('broadcasting.connections.reverb.options.host', '127.0.0.1'), 512) ?>,
                port: <?php echo json_encode(config('broadcasting.connections.reverb.options.port', 8080), 512) ?>,
                scheme: <?php echo json_encode(config('broadcasting.connections.reverb.options.scheme', 'http'), 512) ?>,
            },
            broadcastEnabled: <?php echo json_encode(config('broadcasting.default') === 'reverb' || config('broadcasting.default') === 'pusher', 15, 512) ?>,
        };

        <?php if(config('broadcasting.default') === 'reverb' || config('broadcasting.default') === 'pusher'): ?>
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: window.Pedalya.reverb.key,
            wsHost: window.Pedalya.reverb.host,
            wsPort: window.Pedalya.reverb.port,
            wssPort: window.Pedalya.reverb.port,
            forceTLS: window.Pedalya.reverb.scheme === 'https',
            encrypted: window.Pedalya.reverb.scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        <?php endif; ?>
    </script>

    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Administrator\pedalya\resources\views\layouts\app.blade.php ENDPATH**/ ?>