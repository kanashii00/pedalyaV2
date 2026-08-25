

<?php $__env->startSection('title', 'ID Scanner — Scan New ID'); ?>

<?php $__env->startSection('page-header'); ?>
    <h1>Scan New ID</h1>
    <p>Position the government ID within the frame. The scanner detects the document, checks quality, and extracts the text automatically.</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('admin.id-scans.index')); ?>" class="btn-admin btn-admin--secondary">
    <i class="bi bi-arrow-left me-1"></i>Back to Records
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form id="scanForm" method="POST" action="<?php echo e(route('admin.id-scans.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('title', null, []); ?> <i class="bi bi-gear me-2"></i>Step 1 — Document &amp; Renter <?php $__env->endSlot(); ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Document Type <span class="text-danger">*</span></label>
                <select id="documentType" name="documentType" class="form-select" required>
                    <option value="national_id">National ID (PhilSys)</option>
                    <option value="drivers_license">Driver's License</option>
                    <option value="passport">Passport</option>
                    <option value="umid">UMID</option>
                    <option value="philhealth_id">PhilHealth ID</option>
                    <option value="student_id">Student ID</option>
                    <option value="voters_id">Voter's ID</option>
                    <option value="other">Other Government ID</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Link to Renter</label>
                <select id="riderSelect" name="userId" class="form-select">
                    <option value="">— New renter (no account yet) —</option>
                    <?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($rider->id); ?>">
                            <?php echo e($rider->name); ?> (<?php echo e($rider->email); ?>)<?php echo e($rider->verified ? ' — Verified' : ' — Unverified'); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div class="form-text">Select an existing renter to link this ID, or leave empty for a new renter.</div>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('title', null, []); ?> <i class="bi bi-camera-video-fill me-2"></i>Step 2 — Capture Document <?php $__env->endSlot(); ?>
         <?php $__env->slot('tools', null, []); ?> 
            <div class="d-flex gap-2">
                <button type="button" id="startCamera" class="btn-admin btn-admin--primary btn-admin--sm">
                    <i class="bi bi-camera me-1"></i>Start Camera
                </button>
                <button type="button" id="stopCamera" class="btn-admin btn-admin--secondary btn-admin--sm" disabled>
                    <i class="bi bi-stop-circle me-1"></i>Stop
                </button>
            </div>
         <?php $__env->endSlot(); ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="camera-wrap position-relative" id="cameraWrap" style="display:none;">
                    <video id="cameraVideo" playsinline muted autoplay style="width:100%;border-radius:var(--radius-lg);background:#111;"></video>
                    <canvas id="detectionCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;"></canvas>

                    
                    <div class="scan-guide position-absolute" id="scanGuide">
                        <div class="guide-corner" style="top:0;left:0;"></div>
                        <div class="guide-corner" style="top:0;right:0;"></div>
                        <div class="guide-corner" style="bottom:0;left:0;"></div>
                        <div class="guide-corner" style="bottom:0;right:0;"></div>
                    </div>

                    
                    <div class="position-absolute top-0 start-0 m-3" id="detectStatus">
                        <span class="badge-admin badge-admin--warning" id="detectBadge">
                            <i class="bi bi-hourglass-split me-1"></i>Waiting for camera...
                        </span>
                    </div>

                    
                    <div class="position-absolute bottom-0 start-0 end-0 p-3 text-center">
                        <button type="button" id="captureBtn" class="btn-admin btn-admin--primary btn-admin--lg" disabled>
                            <i class="bi bi-circle-fill me-2"></i>Capture
                        </button>
                    </div>
                </div>
                <div id="cameraPlaceholder" class="text-center text-muted py-5">
                    <i class="bi bi-camera-video" style="font-size:48px;"></i>
                    <p class="mt-3 mb-1 fw-semibold">Camera is off</p>
                    <small>Click <strong>Start Camera</strong> to activate the device camera.</small>
                </div>
            </div>

            <div class="col-lg-5">
                <label class="form-label">Quality Metrics</label>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Document Detected</small>
                        <small id="metricDetect" class="text-danger fw-semibold">No</small>
                    </div>
                    <div class="progress mt-1" style="height:8px;">
                        <div id="barDetect" class="progress-bar" style="width:0%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Sharpness (Blur)</small>
                        <small id="metricBlur" class="text-muted">—</small>
                    </div>
                    <div class="progress mt-1" style="height:8px;">
                        <div id="barBlur" class="progress-bar bg-warning" style="width:0%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Glare / Overexposure</small>
                        <small id="metricGlare" class="text-muted">—</small>
                    </div>
                    <div class="progress mt-1" style="height:8px;">
                        <div id="barGlare" class="progress-bar bg-info" style="width:0%"></div>
                    </div>
                </div>

                <hr>

                <label class="form-label">Captured Sides</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center" style="border-color:var(--border-strong);">
                            <div id="frontPreview" class="preview-thumb mb-1" style="height:90px;background:var(--surface-2);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--text-3);overflow:hidden;">
                                <small>Front not captured</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="scanSide('front')">Scan Front</button>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center" style="border-color:var(--border-strong);">
                            <div id="backPreview" class="preview-thumb mb-1" style="height:90px;background:var(--surface-2);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--text-3);overflow:hidden;">
                                <small>Back not captured</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="scanSide('back')">Scan Back</button>
                        </div>
                    </div>
                </div>
                <div class="form-text mt-2">Scan the front of the ID first, then flip it and scan the back. OCR runs on the front capture.</div>

                <input type="hidden" name="frontImage" id="inputFrontImage">
                <input type="hidden" name="backImage" id="inputBackImage">
                <input type="hidden" name="rawOcrText" id="inputOcrText">
                <input type="hidden" name="ocrConfidence" id="inputOcrConfidence">
                <input type="hidden" name="qualityScore" id="inputQualityScore">
                <input type="hidden" name="blurScore" id="inputBlurScore">
                <input type="hidden" name="glareScore" id="inputGlareScore">
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('title', null, []); ?> <i class="bi bi-person-lines-fill me-2"></i>Step 3 — Extracted Information <?php $__env->endSlot(); ?>
        <div id="ocrStatus" class="alert alert-info d-none" role="alert">
            <i class="bi bi-cpu me-2"></i><span id="ocrStatusText">Running OCR...</span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullName" id="fieldFullName" class="form-control" placeholder="Auto-filled from OCR">
            </div>
            <div class="col-md-6">
                <label class="form-label">ID Number</label>
                <input type="text" name="idNumber" id="fieldIdNumber" class="form-control" placeholder="Auto-filled from OCR">
            </div>
            <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <input type="text" name="dateOfBirth" id="fieldDob" class="form-control" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <label class="form-label">Expiration Date</label>
                <input type="text" name="expirationDate" id="fieldExpiration" class="form-control" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <label class="form-label">OCR Confidence</label>
                <input type="text" class="form-control" id="displayConfidence" value="—" disabled>
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" id="fieldAddress" class="form-control" placeholder="Auto-filled from OCR">
            </div>
            <div class="col-12">
                <label class="form-label">Raw OCR Text <span class="text-muted">(editable if the scanner misread the card)</span></label>
                <textarea name="rawOcrTextAlt" id="fieldRawOcr" rows="4" class="form-control" style="font-family:monospace;font-size:0.82rem;" placeholder="OCR text will appear here after scanning the front side."></textarea>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn-admin btn-admin--primary btn-admin--lg flex-grow-1" id="submitBtn" disabled>
            <i class="bi bi-shield-check me-2"></i>Save Scan &amp; Submit for Review
        </button>
        <a href="<?php echo e(route('admin.id-scans.index')); ?>" class="btn-admin btn-admin--secondary btn-admin--lg">Cancel</a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
    .scan-guide {
        inset: 8% 12% 20% 12%;
        border: 2px dashed rgba(255,255,255,0.85);
        border-radius: 12px;
        pointer-events: none;
    }
    .guide-corner {
        position: absolute;
        width: 26px;
        height: 26px;
        border: 3px solid #22c55e;
    }
    .guide-corner:nth-child(1) { border-right:none; border-bottom:none; border-radius: 10px 0 0 0; }
    .guide-corner:nth-child(2) { border-left:none; border-bottom:none; border-radius: 0 10px 0 0; }
    .guide-corner:nth-child(3) { border-right:none; border-top:none; border-radius: 0 0 0 10px; }
    .guide-corner:nth-child(4) { border-left:none; border-top:none; border-radius: 0 0 10px 0; }
    .scan-guide.good { border-color: #22c55e; border-style: solid; }
    .preview-thumb img { width:100%; height:100%; object-fit:cover; }
</style>
<script>
    (function() {
        const video = document.getElementById('cameraVideo');
        const wrap = document.getElementById('cameraWrap');
        const placeholder = document.getElementById('cameraPlaceholder');
        const detectionCanvas = document.getElementById('detectionCanvas');
        const dctx = detectionCanvas.getContext('2d');
        const captureBtn = document.getElementById('captureBtn');
        const startBtn = document.getElementById('startCamera');
        const stopBtn = document.getElementById('stopCamera');
        const detectBadge = document.getElementById('detectBadge');

        let stream = null;
        let rafId = null;
        let activeSide = 'front';
        let lastMetrics = { detected: false, blur: 0, glare: 0 };
        let captured = { front: null, back: null };

        const TESS_URL = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';

        window.scanSide = function(side) {
            if (!stream) {
                alert('Please start the camera first.');
                return;
            }
            activeSide = side;
            detectBadge.className = 'badge-admin badge-admin--warning';
            detectBadge.innerHTML = '<i class="bi bi-camera me-1"></i>Capturing ' + side + ' side...';
            captureBtn.disabled = false;
        };

        startBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment',
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                    audio: false,
                });
                video.srcObject = stream;
                await video.play();
                wrap.style.display = 'block';
                placeholder.style.display = 'none';
                startBtn.disabled = true;
                stopBtn.disabled = false;
                detectBadge.className = 'badge-admin badge-admin--success';
                detectBadge.innerHTML = '<i class="bi bi-camera-video me-1"></i>Detecting document...';
                detectionCanvas.width = video.videoWidth || 1280;
                detectionCanvas.height = video.videoHeight || 720;
                runDetectionLoop();
            } catch (e) {
                detectBadge.className = 'badge-admin badge-admin--neutral';
                detectBadge.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Camera error: ' + e.message;
            }
        });

        stopBtn.addEventListener('click', () => {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            if (rafId) cancelAnimationFrame(rafId);
            wrap.style.display = 'none';
            placeholder.style.display = 'block';
            startBtn.disabled = false;
            stopBtn.disabled = true;
            captureBtn.disabled = true;
            detectBadge.className = 'badge-admin badge-admin--warning';
            detectBadge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Waiting for camera...';
        });

        function runDetectionLoop() {
            if (!stream) return;
            analyzeFrame();
            rafId = requestAnimationFrame(runDetectionLoop);
        }

        function analyzeFrame() {
            if (!video.videoWidth) return;

            const vw = video.videoWidth;
            const vh = video.videoHeight;
            const scale = 0.2;
            const w = Math.round(vw * scale);
            const h = Math.round(vh * scale);

            const off = document.createElement('canvas');
            off.width = w;
            off.height = h;
            const octx = off.getContext('2d', { willReadFrequently: true });
            octx.drawImage(video, 0, 0, w, h);
            const img = octx.getImageData(0, 0, w, h).data;

            // Luminance array
            const lum = new Float32Array(w * h);
            let sum = 0;
            for (let i = 0; i < w * h; i++) {
                const p = i * 4;
                lum[i] = 0.299 * img[p] + 0.587 * img[p + 1] + 0.114 * img[p + 2];
                sum += lum[i];
            }
            const avgLum = sum / (w * h);

            // Blur score via edge variance (Laplacian-lite: avg absolute neighbor diff)
            let edgeSum = 0, edgeCount = 0;
            for (let y = 1; y < h - 1; y++) {
                for (let x = 1; x < w - 1; x++) {
                    const i = y * w + x;
                    const dx = lum[i + 1] - lum[i - 1];
                    const dy = lum[i + w] - lum[i - w];
                    edgeSum += Math.abs(dx) + Math.abs(dy);
                    edgeCount++;
                }
            }
            const sharpness = edgeCount ? Math.min(100, edgeSum / edgeCount * 3.2) : 0;
            const blur = Math.max(0, Math.min(100, 100 - sharpness));

            // Glare score: overexposed pixels ratio
            let over = 0;
            for (let i = 0; i < w * h; i++) if (lum[i] > 235) over++;
            const glare = Math.round((over / (w * h)) * 100);

            // Document detection: bright region bounding box
            const threshold = Math.max(120, avgLum + 12);
            let minX = w, minY = h, maxX = 0, maxY = 0, brightCount = 0;
            for (let y = 0; y < h; y++) {
                for (let x = 0; x < w; x++) {
                    if (lum[y * w + x] > threshold) {
                        brightCount++;
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    }
                }
            }
            const coverage = brightCount / (w * h);
            const bw = maxX - minX, bh = maxY - minY;
            const boxRatio = (bw * bh) / (w * h);
            const aspect = bw / Math.max(bh, 1);

            // A document is a fairly large, centered, roughly-card-aspect bright region
            const detected = coverage > 0.10 && coverage < 0.65 &&
                             boxRatio > 0.15 && boxRatio < 0.70 &&
                             aspect > 0.55 && aspect < 2.6 &&
                             glare < 55 && blur < 65;

            lastMetrics = { detected, blur: Math.round(blur), glare };

            drawOverlay(detected, minX, minY, bw, bh, w, h, vw, vh, scale);
            updateMetrics(lastMetrics);

            if (detected && captureBtn.disabled) {
                captureBtn.disabled = false;
                detectBadge.className = 'badge-admin badge-admin--success';
                detectBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Document detected — ready to capture';
            } else if (!detected && !captureBtn.disabled) {
                captureBtn.disabled = true;
                detectBadge.className = 'badge-admin badge-admin--warning';
                detectBadge.innerHTML = '<i class="bi bi-viewfinder me-1"></i>Align the ID within the frame...';
            }
        }

        function drawOverlay(detected, minX, minY, bw, bh, w, h, vw, vh, scale) {
            dctx.clearRect(0, 0, detectionCanvas.width, detectionCanvas.height);
            if (!detected) return;

            // Scale detection box up to full-res coordinates
            const pad = 0.05;
            const x = (minX - bw * pad) * (vw / w);
            const y = (minY - bh * pad) * (vh / h);
            const bwFull = bw * (1 + 2 * pad) * (vw / w);
            const bhFull = bh * (1 + 2 * pad) * (vh / h);

            dctx.strokeStyle = '#22c55e';
            dctx.lineWidth = 6;
            dctx.setLineDash([]);
            dctx.strokeRect(x, y, bwFull, bhFull);

            const guide = document.getElementById('scanGuide');
            guide.classList.add('good');
        }

        function updateMetrics(m) {
            const safe = Math.min(100, Math.max(0, m.blur));
            document.getElementById('metricDetect').textContent = m.detected ? 'Yes' : 'No';
            document.getElementById('metricDetect').className = m.detected ? 'text-success fw-semibold' : 'text-danger fw-semibold';
            document.getElementById('barDetect').style.width = m.detected ? '100%' : '0%';
            document.getElementById('barDetect').className = 'progress-bar ' + (m.detected ? 'bg-success' : 'bg-danger');

            document.getElementById('metricBlur').textContent = m.blur + '% blurred';
            document.getElementById('barBlur').style.width = m.blur + '%';

            document.getElementById('metricGlare').textContent = m.glare + '%';
            document.getElementById('barGlare').style.width = m.glare + '%';
        }

        captureBtn.addEventListener('click', () => {
            if (!stream) return;

            const vw = video.videoWidth;
            const vh = video.videoHeight;
            const canvas = document.createElement('canvas');
            canvas.width = vw;
            canvas.height = vh;
            const ctx = canvas.getContext('2d');

            // Small enhancement pass
            ctx.drawImage(video, 0, 0, vw, vh);
            ctx.filter = 'contrast(1.18) brightness(1.03) saturate(1.12)';
            ctx.drawImage(canvas, 0, 0);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

            // Store for preview
            captured[activeSide] = dataUrl;
            updateCapturedPreviews();

            const blur = lastMetrics.blur;
            const glare = lastMetrics.glare;
            const quality = Math.round((100 - blur) * 0.6 + (100 - glare) * 0.4);
            document.getElementById('inputQualityScore').value = quality;
            document.getElementById('inputBlurScore').value = blur;
            document.getElementById('inputGlareScore').value = glare;
            document.getElementById(activeSide === 'front' ? 'inputFrontImage' : 'inputBackImage').value = dataUrl;

            detectBadge.className = 'badge-admin badge-admin--success';
            detectBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + activeSide.toUpperCase() + ' captured';

            if (activeSide === 'front') {
                runOcr(dataUrl);
            } else {
                captureBtn.disabled = true;
                detectBadge.className = 'badge-admin badge-admin--warning';
                detectBadge.innerHTML = '<i class="bi bi-viewfinder me-1"></i>Both sides captured';
            }
        });

        function updateCapturedPreviews() {
            for (const side of ['front', 'back']) {
                const box = document.getElementById(side + 'Preview');
                if (captured[side]) {
                    box.innerHTML = '<img src="' + captured[side] + '" alt="' + side + '">';
                }
            }
        }

        async function runOcr(dataUrl) {
            const statusBox = document.getElementById('ocrStatus');
            const statusText = document.getElementById('ocrStatusText');
            statusBox.classList.remove('d-none');
            statusText.textContent = 'Loading OCR engine (first run downloads ~2MB)...';

            try {
                if (!window.Tesseract) {
                    await new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = TESS_URL;
                        s.onload = resolve;
                        s.onerror = () => reject(new Error('Failed to load Tesseract.js'));
                        document.head.appendChild(s);
                    });
                }

                statusText.textContent = 'Running OCR — extracting text...';
                const result = await window.Tesseract.recognize(dataUrl, 'eng', {
                    logger: (m) => {
                        if (m.status === 'recognizing text') {
                            statusText.textContent = 'Recognizing text... ' + Math.round(m.progress * 100) + '%';
                        }
                    },
                });

                const text = (result.data.text || '').trim();
                document.getElementById('inputOcrText').value = text;
                document.getElementById('fieldRawOcr').value = text;

                const conf = Math.round(result.data.confidence || 0);
                document.getElementById('inputOcrConfidence').value = conf;
                document.getElementById('displayConfidence').value = conf + '%';

                // Light client-side parse to pre-fill the form (server re-parses authoritatively)
                const fields = parseOcrClient(text, document.getElementById('documentType').value);
                if (fields.fullName) document.getElementById('fieldFullName').value = fields.fullName;
                if (fields.idNumber) document.getElementById('fieldIdNumber').value = fields.idNumber;
                if (fields.dob) document.getElementById('fieldDob').value = fields.dob;
                if (fields.expiration) document.getElementById('fieldExpiration').value = fields.expiration;
                if (fields.address) document.getElementById('fieldAddress').value = fields.address;

                statusText.innerHTML = '<i class="bi bi-check-circle me-1"></i>OCR complete — confidence ' + conf + '%. Review the fields before submitting.';
                maybeEnableSubmit();
            } catch (e) {
                statusBox.classList.add('alert-warning');
                statusBox.classList.remove('alert-info');
                statusText.textContent = 'OCR failed: ' + e.message + '. You can type the details manually.';
                maybeEnableSubmit();
            }
        }

        function parseOcrClient(text, docType) {
            const out = { fullName: null, idNumber: null, dob: null, expiration: null, address: null };
            const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean);

            const date = /(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/;
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i];
                if (!out.dob && /birth|b\.o\.|b\/o|born|dob|date of birth/i.test(line)) {
                    const d = (line.match(date) || (lines[i+1] || '').match(date));
                    if (d) {
                        const year = d[3].length === 2 ? (d[3] >= 40 ? '19' : '20') + d[3] : d[3];
                        out.dob = d[1].padStart(2, '0') + '-' + d[2].padStart(2, '0') + '-' + year;
                    }
                }
            }
            return out;
        }

        function maybeEnableSubmit() {
            const hasFront = !!captured.front;
            const name = document.getElementById('fieldFullName').value.trim();
            document.getElementById('submitBtn').disabled = !(hasFront && name);
        }

        // Sync raw OCR text textarea back to hidden input on manual edits
        document.getElementById('fieldRawOcr').addEventListener('input', function() {
            document.getElementById('inputOcrText').value = this.value;
        });

        // Live-form re-enable of submit
        ['fieldFullName', 'fieldIdNumber'].forEach(id => {
            document.getElementById(id).addEventListener('input', maybeEnableSubmit);
        });
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            const front = captured.front;
            const back = captured.back;
            if (!front) { e.preventDefault(); alert('Please capture the FRONT of the ID before submitting.'); return; }
            if (back) {
                document.getElementById('inputBackImage').value = back;
            }
            document.getElementById('inputFrontImage').value = front;
        });
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\idscans\scanner.blade.php ENDPATH**/ ?>