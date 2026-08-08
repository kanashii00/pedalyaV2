<?php $__env->startSection('title', 'Pedalya - Smart Bicycle Rental'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .landing-page { min-height: 100vh; }
    .hero-section {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, rgba(8, 24, 13, 0.75) 0%, rgba(8, 24, 13, 0.85) 100%),
                    url('<?php echo e(asset('assets/img/bg.png')); ?>') center / cover no-repeat;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
<div class="landing-page">

    
    <nav class="navbar navbar-expand-lg landing-nav" id="landingNav">
        <div class="container">
            <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
                <img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya" style="width:84px;height:84px;border-radius:16px;object-fit:cover;margin-right:16px;">
                <span>Peda<span style="color: var(--primary);">lya</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="landingNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo e(route('login')); ?>" class="btn-pedalya-outline" style="padding: 8px 20px; font-size: 0.88rem;">Sign In</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="<?php echo e(route('register')); ?>" class="btn-pedalya" style="padding: 8px 20px; font-size: 0.88rem;">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 hero-content">
                    <h1 class="text-white">Smart Bicycle Rentals at <span class="text-warning">Azuela Cove</span></h1>
                    <p class="text-white-75 lead">Experience a fully automated, IoT-powered bicycle rental system with GPS tracking, geofence security, and real-time accident monitoring — right along the waterfront in Davao City.</p>
                    <div class="d-flex gap-3 flex-wrap mt-4">
                        <a href="<?php echo e(route('register')); ?>" class="btn-pedalya btn-lg px-5">
                            <i class="bi bi-bicycle me-2"></i>Start Riding
                        </a>
                        <a href="#how-it-works" class="btn-pedalya-outline btn-lg px-5">
                            <i class="bi bi-play-circle me-2"></i>How It Works
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 hero-logo text-center">
                    <img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya" style="width:280px;height:280px;border-radius:32px;object-fit:cover;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                </div>
            </div>
        </div>
    </section>

    
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Everything You Need, Automatically</h2>
                <p>From ID verification to real-time theft detection, Pedalya handles it all so you can focus on the ride.</p>
                <div class="title-accent"></div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-person-badge"></i></div>
                        <h5>Automated ID Scanning</h5>
                        <p>Government IDs are detected, cropped, and read automatically using OCR — no manual typing.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-geo-alt"></i></div>
                        <h5>Live GPS Tracking</h5>
                        <p>Every bicycle streams its location, speed, and battery to a 3D map in real time.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-bounding-box-circles"></i></div>
                        <h5>Circular Geofence</h5>
                        <p>A configurable riding boundary keeps bicycles inside the safe zone with instant alerts.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-shield-exclamation"></i></div>
                        <h5>Theft Detection</h5>
                        <p>Leaving the boundary triggers a theft alert, red marker, and remote smart-lock control.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-activity"></i></div>
                        <h5>Accident Monitoring</h5>
                        <p>MPU6050 sensors detect falls and collisions, notifying staff instantly with GPS location.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-cpu"></i></div>
                        <h5>ESP32 Smart Locks</h5>
                        <p>Lock and unlock bicycles remotely with IoT-powered smart lock hardware.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Three simple steps between you and your next ride along Azuela Cove.</p>
                <div class="title-accent"></div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h5>Verify Your ID</h5>
                        <p>Register and scan your government ID. Our system verifies it automatically and securely.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h5>Pick a Bicycle</h5>
                        <p>Choose an available bicycle, unlock it with your phone, and start your ride.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h5>Ride &amp; Return</h5>
                        <p>Enjoy the ride. Return the bicycle when you're done and pay only for the time you used.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h2 style="font-size: 2rem; font-weight: 700;">Built for Azuela Cove, Davao City</h2>
                    <p class="text-muted mt-3" style="font-size: 1.02rem; line-height: 1.8;">
                        Pedalya is a complete IoT bicycle rental platform designed for waterfront locations like Azuela Cove.
                        It combines automated ID verification, live 3D mapping, geofence security, smart locks, and accident
                        monitoring into one reliable system.
                    </p>
                    <div class="mt-4">
                        <a href="<?php echo e(route('register')); ?>" class="btn-pedalya btn-lg">
                            <i class="bi bi-arrow-right me-2"></i>Create Your Account
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                            <h5>Data Privacy Compliant</h5>
                            <p>ID images and personal data are encrypted and stored securely.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon"><i class="bi bi-lightning-charge"></i></div>
                            <h5>Real-Time Everything</h5>
                            <p>WebSocket-powered updates keep you in sync with every ride.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <footer class="landing-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h6><img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya" style="width:56px;height:56px;border-radius:10px;object-fit:cover;margin-right:12px;vertical-align:middle;">Pedalya</h6>
                    <p style="font-size: 0.9rem;">IoT-Based Bicycle Rental Management System for Azuela Cove, Davao City.</p>
                </div>
                <div class="col-lg-4">
                    <h6>Quick Links</h6>
                    <a href="<?php echo e(route('login')); ?>">Sign In</a>
                    <a href="<?php echo e(route('register')); ?>">Create Account</a>
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How It Works</a>
                </div>
                <div class="col-lg-4">
                    <h6>Contact</h6>
                    <a href="mailto:hello@pedalya.com">hello@pedalya.com</a>
                    <a href="#">Azuela Cove, J.P. Laurel Ave., Lanang, Davao City</a>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?php echo e(date('Y')); ?> Pedalya. All rights reserved.
            </div>
        </div>
    </footer>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    window.addEventListener('scroll', function() {
        const nav = document.getElementById('landingNav');
        if (nav) {
            nav.classList.toggle('scrolled', window.scrollY > 40);
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\kyra&friends\laravel\resources\views/index.blade.php ENDPATH**/ ?>