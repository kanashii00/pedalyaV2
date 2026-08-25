

<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .id-cam-wrap {
        position: relative;
        border-radius: 14px;
        overflow: hidden;
        background: #0d1117;
        line-height: 0;
    }
    .id-cam-wrap video { width: 100%; display: block; }
    .id-scan-frame {
        position: absolute;
        inset: 12% 8%;
        border: 2px dashed rgba(255, 255, 255, 0.7);
        border-radius: 10px;
        pointer-events: none;
    }
    .id-scan-frame::after {
        content: 'Place your ID within the frame';
        position: absolute;
        left: 50%;
        bottom: -30px;
        transform: translateX(-50%);
        font-size: 11px;
        font-weight: 600;
        color: #fff;
        background: rgba(0, 0, 0, 0.6);
        padding: 4px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .id-scan-corner {
        position: absolute;
        width: 24px;
        height: 24px;
        border: 3px solid #22c55e;
    }
    .id-scan-corner.tl { top: 0; left: 0; border-right: none; border-bottom: none; border-radius: 8px 0 0 0; }
    .id-scan-corner.tr { top: 0; right: 0; border-left: none; border-bottom: none; border-radius: 0 8px 0 0; }
    .id-scan-corner.bl { bottom: 0; left: 0; border-right: none; border-top: none; border-radius: 0 0 0 8px; }
    .id-scan-corner.br { bottom: 0; right: 0; border-left: none; border-top: none; border-radius: 0 0 8px 0; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-4">
    <!-- Profile Picture & Status -->
    <div class="col-lg-4">
        <div class="card-pedalya mb-4">
            <div class="card-pedalya-body text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:90px;height:90px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-size:2rem;font-weight:700;">
                    <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                </div>
                <h5 class="mb-0"><?php echo e($user->name); ?></h5>
                <p class="text-muted mb-3"><?php echo e($user->email); ?></p>
                <?php if($user->verified): ?>
                    <span class="badge-status badge-verified mb-3"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Verified Rider</span>
                <?php else: ?>
                    <span class="badge-status badge-pending mb-3"><i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Unverified</span>
                <?php endif; ?>
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center"><strong class="d-block"><?php echo e($totalRentals); ?></strong><small class="text-muted">Rentals</small></div>
                    <div class="text-center"><strong class="d-block">₱<?php echo e(number_format($totalSpent, 2)); ?></strong><small class="text-muted">Spent</small></div>
                </div>
            </div>
        </div>

        <!-- ID Verification -->
        <div class="card-pedalya">
            <div class="card-pedalya-header"><span><i class="bi bi-shield-check text-success me-2"></i><strong>ID Verification</strong></span></div>
            <div class="card-pedalya-body">
                <?php if($user->verified): ?>
                    <div class="text-center mb-3">
                        <span class="badge-status badge-verified"><i class="bi bi-check-circle me-1"></i> Verified</span>
                    </div>
                    <p class="text-muted text-center" style="font-size:0.85rem;">Your identity has been verified. You have full access to the rental system.</p>
                    <div class="bg-light rounded p-3 text-center">
                        <i class="bi bi-file-earmark-image" style="font-size:2rem;color:#aaa;"></i><br>
                        <small class="text-muted">ID uploaded</small>
                    </div>
                <?php else: ?>
                    <div class="text-center mb-3">
                        <span class="badge-status badge-pending"><i class="bi bi-exclamation-circle me-1"></i> Pending Verification</span>
                    </div>
                    <p class="text-muted text-center" style="font-size:0.85rem;">Upload a valid government ID to verify your identity and unlock full rental access.</p>
                    <form action="<?php echo e(route('rider.profile.upload-id')); ?>" method="POST" enctype="multipart/form-data" id="idUploadForm">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label-pedalya">Upload Government ID</label>
                            <input type="file" class="form-control-pedalya" name="id_image" id="idImageInput" accept="image/*,.pdf">
                            <small class="text-muted">Accepted: JPG, PNG, PDF (max 5MB) — or capture your ID with the camera below.</small>
                            <?php $__errorArgs = ['id_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" id="openIdCam" class="btn btn-outline-success"><i class="bi bi-camera-video me-1"></i> Open Camera</button>
                            <button type="button" id="captureIdBtn" class="btn btn-pedalya" style="display:none;"><i class="bi bi-circle-fill me-1"></i> Capture ID</button>
                        </div>
                        <div id="idCamWrap" class="id-cam-wrap mb-3" style="display:none;">
                            <video id="idCamVideo" playsinline muted autoplay></video>
                            <div class="id-scan-frame">
                                <div class="id-scan-corner tl"></div>
                                <div class="id-scan-corner tr"></div>
                                <div class="id-scan-corner bl"></div>
                                <div class="id-scan-corner br"></div>
                            </div>
                        </div>
                        <div id="idCamPreview" style="display:none;margin-bottom:12px;"></div>
                        <input type="hidden" name="id_image_base64" id="idImageBase64">

                        <button type="submit" class="btn btn-pedalya w-100"><i class="bi bi-upload me-1"></i> Submit for Verification</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="col-lg-8">
        <div class="card-pedalya mb-4">
            <div class="card-pedalya-header"><span><i class="bi bi-person-gear text-primary me-2"></i><strong>Personal Information</strong></span></div>
            <div class="card-pedalya-body">
                <form action="<?php echo e(route('rider.profile.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-pedalya">Full Name</label>
                            <input type="text" class="form-control-pedalya" name="displayName" value="<?php echo e(old('displayName', $user->name)); ?>" required>
                            <?php $__errorArgs = ['displayName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-pedalya">Email Address</label>
                            <input type="email" class="form-control-pedalya" value="<?php echo e($user->email); ?>" readonly style="background: var(--gray-100);">
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-pedalya">Phone Number</label>
                            <input type="tel" class="form-control-pedalya" name="phoneNumber" value="<?php echo e(old('phoneNumber', $user->phoneNumber)); ?>">
                            <?php $__errorArgs = ['phoneNumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-pedalya">Address</label>
                            <input type="text" class="form-control-pedalya" name="address" value="<?php echo e(old('address', $user->address)); ?>">
                            <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-pedalya"><i class="bi bi-check-lg"></i> Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card-pedalya mb-4">
            <div class="card-pedalya-header"><span><i class="bi bi-lock text-warning me-2"></i><strong>Change Password</strong></span></div>
            <div class="card-pedalya-body">
                <form action="<?php echo e(route('rider.profile.update-password')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-pedalya">Current Password</label>
                            <input type="password" class="form-control-pedalya" name="current_password" placeholder="Enter current password" required>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label-pedalya">New Password</label>
                            <input type="password" class="form-control-pedalya" name="password" placeholder="Min 6 characters" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-pedalya">Confirm New Password</label>
                            <input type="password" class="form-control-pedalya" name="password_confirmation" placeholder="Re-enter new password" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-pedalya-outline"><i class="bi bi-shield-lock"></i> Update Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Settings -->
        <div class="card-pedalya">
            <div class="card-pedalya-header"><span><i class="bi bi-gear me-2" style="color:#666;"></i><strong>Account Settings</strong></span></div>
            <div class="card-pedalya-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div><strong>Email Notifications</strong><br><small class="text-muted">Receive rental confirmations and updates via email</small></div>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked></div>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div><strong>SMS Alerts</strong><br><small class="text-muted">Get SMS for important safety alerts</small></div>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div><strong class="text-danger">Delete Account</strong><br><small class="text-muted">Permanently delete your account and all data</small></div>
                    <form action="<?php echo e(route('rider.profile.delete')); ?>" method="POST" onsubmit="return confirm('This action is irreversible. Are you sure you want to delete your account?');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var camBtn = document.getElementById('openIdCam');
        var capBtn = document.getElementById('captureIdBtn');
        var wrap = document.getElementById('idCamWrap');
        var video = document.getElementById('idCamVideo');
        var hidden = document.getElementById('idImageBase64');
        var fileInput = document.getElementById('idImageInput');
        var previewBox = document.getElementById('idCamPreview');
        var form = document.getElementById('idUploadForm');
        var stream = null;

        if (!camBtn || !form) return;

        camBtn.addEventListener('click', async function() {
            if (stream) return;
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false,
                });
                video.srcObject = stream;
                await video.play();
                wrap.style.display = 'block';
                capBtn.style.display = 'inline-flex';
                camBtn.disabled = true;
            } catch (e) {
                alert('Unable to open camera: ' + (e.message || 'access denied'));
            }
        });

        capBtn.addEventListener('click', function() {
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 1280;
            canvas.height = video.videoHeight || 720;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            hidden.value = dataUrl;
            previewBox.innerHTML = '<img src="' + dataUrl + '" alt="Captured ID">';
            previewBox.style.display = 'block';
            if (stream) { stream.getTracks().forEach(function(t) { t.stop(); }); stream = null; }
            camBtn.disabled = false;
            camBtn.innerHTML = '<i class="bi bi-camera-video me-1"></i> Re-open Camera';
            capBtn.style.display = 'none';
            wrap.style.display = 'none';
            fileInput.removeAttribute('required');
        });

        fileInput.addEventListener('change', function() {
            if (fileInput.files.length) {
                hidden.value = '';
                previewBox.style.display = 'none';
            }
        });

        form.addEventListener('submit', function(e) {
            if (!fileInput.files.length && !hidden.value) {
                e.preventDefault();
                alert('Please upload a file or capture your ID with the camera first.');
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.rider', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\rider\profile.blade.php ENDPATH**/ ?>