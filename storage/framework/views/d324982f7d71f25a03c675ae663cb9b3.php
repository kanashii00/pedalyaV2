

<?php $__env->startSection('title', 'Create Account - Pedalya'); ?>

<?php $__env->startSection('body'); ?>
<div class="auth-page">
    <div class="auth-card" style="max-width: 480px;">
        <div class="auth-logo">
            <h1><i class="bi bi-bicycle me-2"></i>Peda<span style="color: var(--primary);">lya</span></h1>
            <p>Create your renter account to start riding</p>
        </div>

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
                           value="<?php echo e(old('name')); ?>" required autofocus autocomplete="name" placeholder="Juan Dela Cruz">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-pedalya" for="email">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control form-control-pedalya" id="email" name="email"
                           value="<?php echo e(old('email')); ?>" required autocomplete="email" placeholder="you@example.com">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-pedalya" for="phoneNumber">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="text" class="form-control form-control-pedalya" id="phoneNumber" name="phoneNumber"
                           value="<?php echo e(old('phoneNumber')); ?>" required autocomplete="tel" placeholder="09XX XXX XXXX">
                </div>
            </div>

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

            <button type="submit" class="btn-pedalya w-100 btn-lg">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>
        </form>

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