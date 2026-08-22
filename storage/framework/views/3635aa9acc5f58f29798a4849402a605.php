<?php $__env->startSection('title', 'Register Customer — Pedalya Admin'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Register Customer</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
    <a href="<?php echo e(route('admin.riders.index')); ?>" class="btn-admin btn-admin--secondary btn-admin--sm">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <form method="POST" action="<?php echo e(route('admin.riders.store')); ?>" class="admin-form" novalidate>
            <?php echo csrf_field(); ?>

            
            <div class="admin-card">
                <div class="admin-card__head">
                    <div class="admin-card__title">
                        <i class="bi bi-person"></i>
                        <span>Customer Information</span>
                    </div>
                </div>
                <div class="admin-card__body">
                    <div class="mb-3">
                        <label class="form-label" for="name">
                            Full Name <span class="form-required">*</span>
                        </label>
                        <input type="text"
                               class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="name" name="name"
                               value="<?php echo e(old('name')); ?>"
                               required autofocus
                               placeholder="e.g. Juan Dela Cruz">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            
            <div class="admin-card">
                <div class="admin-card__head">
                    <div class="admin-card__title">
                        <i class="bi bi-telephone"></i>
                        <span>Contact Information</span>
                    </div>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="email">
                                Email Address <span class="form-required">*</span>
                            </label>
                            <input type="email"
                                   class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="email" name="email"
                                   value="<?php echo e(old('email')); ?>"
                                   required
                                   placeholder="e.g. juan@example.com">
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phoneNumber">
                                Phone Number
                            </label>
                            <input type="text"
                                   class="form-control <?php $__errorArgs = ['phoneNumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="phoneNumber" name="phoneNumber"
                                   value="<?php echo e(old('phoneNumber')); ?>"
                                   placeholder="e.g. 09XX XXX XXXX">
                            <?php $__errorArgs = ['phoneNumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="address">
                                Address
                            </label>
                            <input type="text"
                                   class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="address" name="address"
                                   value="<?php echo e(old('address')); ?>"
                                   placeholder="e.g. Azuela Cove, Davao City">
                            <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="admin-card">
                <div class="admin-card__head">
                    <div class="admin-card__title">
                        <i class="bi bi-shield-lock"></i>
                        <span>Account Security</span>
                    </div>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="password">
                                Password <span class="form-required">*</span>
                            </label>
                            <input type="password"
                                   class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="password" name="password"
                                   required
                                   placeholder="Min 8 characters">
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirmation">
                                Confirm Password <span class="form-required">*</span>
                            </label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation" name="password_confirmation"
                                   required
                                   placeholder="Repeat password">
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="d-flex align-items-center justify-content-end gap-2 mb-4">
                <a href="<?php echo e(route('admin.riders.index')); ?>" class="btn-admin btn-admin--secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-admin btn-admin--primary btn-admin--lg">
                    <i class="bi bi-person-plus me-1"></i>Register Customer
                </button>
            </div>
        </form>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\riders\create.blade.php ENDPATH**/ ?>