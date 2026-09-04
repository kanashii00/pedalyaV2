

<?php $__env->startSection('title', 'Create Account - Pedalya'); ?>

<?php $__env->startSection('body'); ?>
<div class="auth-page">
    <div class="auth-card" style="max-width: 480px;">
        <div class="auth-logo">
            <h1><i class="bi bi-bicycle me-2"></i>Peda<span style="color: var(--primary);">lya</span></h1>
            <p>Create your renter account to start riding</p>
        </div>

        <?php if(session('status')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-google me-2"></i><?php echo e(session('status')); ?>

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

        <form method="POST" action="<?php echo e(route('register')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label-pedalya" for="name">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control form-control-pedalya" id="name" name="name"
                           value="<?php echo e(old('name', $pendingOauth['name'] ?? '')); ?>" <?php echo e($pendingOauth ? 'readonly' : ''); ?>

                           required autofocus autocomplete="name" placeholder="Juan Dela Cruz">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-pedalya" for="email">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control form-control-pedalya" id="email" name="email"
                           value="<?php echo e(old('email', $pendingOauth['email'] ?? '')); ?>" <?php echo e($pendingOauth ? 'readonly' : ''); ?>

                           required autocomplete="email" placeholder="you@example.com">
                </div>
            </div>

            <?php if($pendingOauth): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-google me-2"></i>You're signed in with Google as
                    <strong><?php echo e($pendingOauth['email']); ?></strong>. Enter your phone number below to finish creating your account.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label-pedalya" for="phoneNumber">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="text" class="form-control form-control-pedalya" id="phoneNumber" name="phoneNumber"
                           value="<?php echo e(old('phoneNumber')); ?>" <?php echo e($pendingOauth ? '' : 'required'); ?>

                           autocomplete="tel" placeholder="09XX XXX XXXX">
                </div>
            </div>

            <?php if(! $pendingOauth): ?>
            <div class="row g-2 mb-4">
                <div class="col-md-6">
                    <label class="form-label-pedalya" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control form-control-pedalya" id="password" name="password"
                               required autocomplete="new-password" placeholder="Min 8 characters">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label-pedalya" for="password_confirmation">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" class="form-control form-control-pedalya" id="password_confirmation"
                               name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-pedalya w-100 btn-lg">
                <i class="bi bi-person-plus me-2"></i><?php echo e($pendingOauth ? 'Finish Creating Account' : 'Create Account'); ?>

            </button>
        </form>

        <?php if(! $pendingOauth): ?>
        <div class="d-flex align-items-center my-4">
            <div style="flex:1; height:1px; background: rgba(0,0,0,0.1);"></div>
            <span class="px-3" style="font-size:0.85rem; font-weight:600; color:var(--gray-500);">OR</span>
            <div style="flex:1; height:1px; background: rgba(0,0,0,0.1);"></div>
        </div>

        <a href="<?php echo e(route('login.google')); ?>" class="google-btn w-100 btn btn-lg"
           style="background:#fff !important; color:#333 !important; font-weight:600; border:1px solid rgba(0,0,0,0.12); display:flex; align-items:center; justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 48 48" style="margin-right:10px;" aria-hidden="true">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
            </svg>
            Sign up with Google
        </a>
        <?php endif; ?>

        <p class="text-center mt-4 mb-0" style="font-size: 0.88rem; color: var(--gray-500);">
            Already have an account?
            <a href="<?php echo e(route('login')); ?>" style="font-weight: 600;">Sign in</a>
        </p>

        <p class="text-center mt-3 mb-0">
            <a href="<?php echo e(route('home')); ?>" style="color: var(--gray-500); font-size: 0.82rem;">
                <i class="bi bi-arrow-left me-1"></i>Back to Home
            </a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\auth\register.blade.php ENDPATH**/ ?>