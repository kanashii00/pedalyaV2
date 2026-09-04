

<?php $__env->startSection('title', 'Sign In - Pedalya'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .auth-page {
        background:
            linear-gradient(180deg, rgba(8, 24, 13, 0.55) 0%, rgba(8, 24, 13, 0.72) 100%),
            url('<?php echo e(asset('assets/img/bg.png')); ?>') center / cover no-repeat;
    }
    .auth-card {
        background: rgba(255, 255, 255, 0.12);
        -webkit-backdrop-filter: blur(16px);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255,255,255,0.15);
    }
    .auth-logo h1 { font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; }
    .auth-logo p { font-size: 1.05rem; font-weight: 500; }
    .form-label-pedalya { color: #fff !important; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.5rem; text-shadow: 0 2px 6px rgba(0,0,0,0.7); }
    .form-control-pedalya { background: rgba(255,255,255,0.18) !important; border: 1px solid rgba(255,255,255,0.3) !important; color: #fff !important; font-size: 1rem; font-weight: 500; }
    .form-control-pedalya::placeholder { color: rgba(255,255,255,0.6) !important; }
    .form-control-pedalya:focus { background: rgba(255,255,255,0.25) !important; border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.3) !important; }
    .input-group-text { background: rgba(255,255,255,0.15) !important; border: 1px solid rgba(255,255,255,0.25) !important; border-right: none !important; color: rgba(255,255,255,0.85) !important; }
    .form-control-pedalya + .input-group-text, .input-group-text + .form-control-pedalya { border-left: none !important; }
    .btn-outline-secondary { background: transparent; border-color: rgba(255,255,255,0.3) !important; color: rgba(255,255,255,0.85) !important; }
    .btn-outline-secondary:hover { background: rgba(255,255,255,0.15) !important; color: #fff !important; border-color: rgba(255,255,255,0.5) !important; }
    .form-check-input { background-color: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); }
    .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
    .form-check-label { color: rgba(255,255,255,0.95) !important; font-weight: 600; text-shadow: 0 2px 6px rgba(0,0,0,0.7); }
    .auth-card a { color: var(--warning) !important; font-weight: 700; text-decoration: none; text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
    .auth-card a:hover { color: #fff !important; text-decoration: underline; }
    .auth-footer-text { color: rgba(255,255,255,0.9) !important; font-weight: 500; text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
    .google-btn { background:#fff !important; color:#333 !important; font-weight:600; border:1px solid rgba(0,0,0,0.12); display:flex; align-items:center; justify-content:center; }
    .google-btn:hover { background:#f6f6f6 !important; color:#111 !important; box-shadow:0 4px 14px rgba(0,0,0,0.15); }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya" style="width:160px;height:160px;border-radius:32px;object-fit:cover;margin-bottom:24px;box-shadow:0 20px 60px rgba(0,0,0,0.3);background:#fdfdfd;padding:12px;">
            <h1 class="text-white" style="text-shadow:0 3px 10px rgba(0,0,0,0.6);font-weight:800;">Peda<span style="color: var(--warning);">lya</span></h1>
            <p class="text-white" style="opacity:0.98;text-shadow:0 2px 6px rgba(0,0,0,0.5);font-weight:500;">Sign in to continue to your dashboard</p>
        </div>

        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <div>
                    <ul class="mb-0" style="padding-left: 18px;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label-pedalya" for="email">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control form-control-pedalya" id="email" name="email"
                           value="<?php echo e(old('email')); ?>" required autofocus autocomplete="email" placeholder="you@example.com">
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label-pedalya" for="password">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control form-control-pedalya" id="password" name="password"
                           required autocomplete="current-password" placeholder="••••••••">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn-pedalya w-100 btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="d-flex align-items-center my-4">
            <div style="flex:1; height:1px; background: rgba(255,255,255,0.25);"></div>
            <span class="px-3 auth-footer-text" style="font-size:0.85rem; font-weight:600;">OR</span>
            <div style="flex:1; height:1px; background: rgba(255,255,255,0.25);"></div>
        </div>

        <a href="<?php echo e(route('login.google')); ?>" class="btn w-100 btn-lg google-btn">
            <svg width="20" height="20" viewBox="0 0 48 48" style="margin-right:10px;" aria-hidden="true">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
            </svg>
            Sign in with Google
        </a>

        <p class="text-center mt-4 mb-0 auth-footer-text" style="font-size: 0.9rem;">
            Don't have an account?
            <a href="<?php echo e(route('register')); ?>">Create one here</a>
        </p>

        <p class="text-center mt-3 mb-0 auth-footer-text" style="font-size: 0.85rem;">
            <a href="<?php echo e(route('home')); ?>"><i class="bi bi-arrow-left me-1"></i>Back to Home</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        if (toggle && password) {
            toggle.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\auth\login.blade.php ENDPATH**/ ?>