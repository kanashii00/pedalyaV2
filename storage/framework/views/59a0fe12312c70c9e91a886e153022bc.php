<?php $__env->startSection('title', 'Rental History'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* ============================================================
       Rental History — Read-only archive view
       ============================================================ */

    /* ---- Filter card ---- */
    .rv-filter-card {
        background: var(--surface);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius);
        box-shadow: var(--shadow-card);
        padding: 14px 18px;
        margin-bottom: 16px;
    }
    .rv-filter-head {
        display: flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: var(--text-3); margin-bottom: 12px;
    }
    .rv-filter-head i { font-size: 13px; color: var(--brand); }
    .rv-filter-grid {
        display: grid; gap: 10px; align-items: end;
        grid-template-columns: minmax(200px, 2fr) minmax(130px, 1fr) minmax(120px, 1fr) minmax(120px, 1fr) minmax(140px, 1fr) minmax(130px, 1fr) auto;
    }
    .rv-filter-field { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .rv-filter-field > label {
        font-size: 10px; font-weight: 700; letter-spacing: 0.06em;
        text-transform: uppercase; color: var(--text-3); padding-left: 2px; white-space: nowrap;
    }
    .rv-filter-ctrl { position: relative; }
    .rv-filter-ctrl > i {
        position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
        color: var(--text-3); font-size: 13px; pointer-events: none;
    }
    .rv-filter-ctrl input,
    .rv-filter-ctrl select {
        width: 100%; height: 34px; padding: 0 10px; font-size: 12.5px;
        color: var(--text-1); background: var(--surface-2);
        border: 1px solid var(--border-subtle); border-radius: 8px;
        transition: border-color 0.15s, box-shadow 0.15s;
        outline: none; appearance: none;
    }
    .rv-filter-ctrl.has-icon input { padding-left: 32px; }
    .rv-filter-ctrl select { padding-right: 24px; cursor: pointer; }
    .rv-filter-ctrl::after {
        content: '\F282'; font-family: 'bootstrap-icons'; font-size: 10px;
        color: var(--text-3); position: absolute; right: 10px; top: 50%;
        transform: translateY(-50%); pointer-events: none;
    }
    .rv-filter-ctrl.no-caret::after { display: none; }
    .rv-filter-ctrl input:hover,
    .rv-filter-ctrl select:hover { border-color: var(--border-strong); }
    .rv-filter-ctrl input:focus,
    .rv-filter-ctrl select:focus {
        border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-ring); background: var(--surface);
    }
    .rv-filter-actions { display: flex; gap: 8px; align-items: center; height: 34px; }
    @media (max-width: 1400px) {
        .rv-filter-grid { grid-template-columns: repeat(3, 1fr); }
        .rv-filter-field--search { grid-column: span 3; }
    }
    @media (max-width: 900px) {
        .rv-filter-grid { grid-template-columns: repeat(2, 1fr); }
        .rv-filter-field--search { grid-column: span 2; }
    }
    @media (max-width: 640px) {
        .rv-filter-grid { grid-template-columns: 1fr; }
        .rv-filter-field--search { grid-column: span 1; }
    }

    /* ---- Table card ---- */
    .rv-table-card {
        background: var(--surface);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius);
        box-shadow: var(--shadow-card);
        overflow: hidden;
    }
    .rv-table-scroll { overflow-x: auto; }
    .rv-table {
        width: 100%; border-collapse: collapse; font-size: 13px; min-width: 960px;
    }
    .rv-table thead th {
        position: sticky; top: 0; z-index: 2;
        padding: 11px 16px; text-align: left; white-space: nowrap;
        font-size: 10.5px; font-weight: 700; letter-spacing: 0.07em;
        text-transform: uppercase; color: var(--text-3); background: var(--surface-2);
        border-bottom: 1px solid var(--border-subtle); user-select: none;
    }
    .rv-table tbody td {
        padding: 12px 16px; border-bottom: 1px solid var(--border-subtle);
        color: var(--text-2); vertical-align: middle;
    }
    .rv-table tbody tr.rv-row { transition: background 0.12s; }
    .rv-table tbody tr.rv-row:hover { background: var(--surface-2); }
    .rv-table tbody tr.rv-row:last-child td { border-bottom: none; }
    .rv-table tbody tr.rv-row.is-open { background: var(--surface-2); }

    /* Rental ID chip */
    .rv-id {
        display: inline-flex; align-items: baseline; gap: 4px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px; font-weight: 600; color: var(--text-1);
        background: var(--surface-3); border: 1px solid var(--border-subtle);
        border-radius: 7px; padding: 3px 9px;
    }
    .rv-id small { font-size: 9.5px; font-weight: 700; letter-spacing: 0.06em; color: var(--text-3); }

    /* Rider cell */
    .rv-rider { display: flex; align-items: center; gap: 10px; min-width: 160px; }
    .rv-rider__name { font-weight: 600; color: var(--text-1); font-size: 13px; line-height: 1.25; }
    .rv-rider__email { font-size: 11.5px; color: var(--text-3); line-height: 1.3; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Bicycle cell */
    .rv-bike__name { font-weight: 600; color: var(--text-1); font-size: 13px; }
    .rv-bike__sub { font-size: 11.5px; color: var(--text-3); }

    /* Date/time cell */
    .rv-dt { line-height: 1.35; }
    .rv-dt__date { font-size: 12.5px; color: var(--text-1); font-weight: 500; }
    .rv-dt__time { font-size: 11px; color: var(--text-3); }

    /* Duration chip */
    .rv-chip {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11.5px; font-weight: 600; color: var(--text-2);
        background: var(--surface-3); border: 1px solid var(--border-subtle);
        border-radius: 999px; padding: 3px 10px; white-space: nowrap;
    }

    /* Fee */
    .rv-fee { font-weight: 700; color: var(--text-1); font-variant-numeric: tabular-nums; font-size: 13.5px; }
    .rv-fee__sub { font-size: 10.5px; color: var(--text-3); font-weight: 500; }

    /* ---- Status pills ---- */
    .rv-status {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 11px; border-radius: 999px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.03em;
        white-space: nowrap; border: 1px solid transparent;
    }
    .rv-status i { font-size: 11px; }
    .rv-status--active { background: var(--success-soft); color: var(--success); border-color: color-mix(in srgb, var(--success) 22%, transparent); }
    .rv-status--completed { background: var(--brand-soft); color: var(--brand-strong); border-color: color-mix(in srgb, var(--brand) 24%, transparent); }
    .rv-status--pending { background: var(--warning-soft); color: var(--warning); border-color: color-mix(in srgb, var(--warning) 24%, transparent); }
    .rv-status--overdue { background: var(--danger-soft); color: var(--danger); border-color: color-mix(in srgb, var(--danger) 24%, transparent); }
    .rv-status--cancelled { background: var(--surface-3); color: var(--text-3); border-color: var(--border-subtle); }
    .rv-status--returned { background: var(--accent-soft); color: var(--accent); border-color: color-mix(in srgb, var(--accent) 22%, transparent); }
    .rv-status--expired { background: var(--danger-soft); color: var(--danger); border-color: color-mix(in srgb, var(--danger) 24%, transparent); }
    .rv-status--paid { background: var(--success-soft); color: var(--success); border-color: color-mix(in srgb, var(--success) 22%, transparent); }
    .rv-status--unpaid { background: var(--danger-soft); color: var(--danger); border-color: color-mix(in srgb, var(--danger) 24%, transparent); }
    .rv-status--dot::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

    /* Payment method */
    .rv-method {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 600; padding: 3px 10px;
        border-radius: 999px; white-space: nowrap;
    }
    .rv-method--gcash { background: var(--accent-soft); color: var(--accent); }
    .rv-method--cash { background: var(--success-soft); color: var(--success); }

    /* ---- Action buttons (view + print only) ---- */
    .rv-actions { display: flex; gap: 6px; align-items: center; }
    .rv-btn {
        width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; border: 1px solid var(--border-subtle); background: transparent;
        color: var(--text-2); font-size: 13.5px; cursor: pointer; padding: 0;
        transition: all 0.15s ease; position: relative;
    }
    .rv-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-card); }
    .rv-btn:active { transform: translateY(0); }
    .rv-btn--view:hover, .rv-btn--view.is-open { color: var(--accent); border-color: var(--accent); background: var(--accent-soft); }
    .rv-btn--print { color: var(--text-3); border-color: var(--border-subtle); }
    .rv-btn--print:hover { color: var(--brand); border-color: var(--brand); background: var(--brand-soft); }
    .rv-btn[title]::after {
        content: attr(title); position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
        background: var(--text-1); color: var(--canvas); padding: 4px 8px; border-radius: 6px;
        font-size: 11px; font-weight: 600; white-space: nowrap; pointer-events: none;
        opacity: 0; transition: opacity 0.15s; z-index: 10;
    }
    .rv-btn:hover[title]::after { opacity: 1; }

    /* ---- Expandable detail rows ---- */
    tr.rv-detail { display: none; }
    tr.rv-detail.open { display: table-row; }
    .rv-detail__cell { padding: 0 !important; border-bottom: 1px solid var(--border-subtle) !important; }
    .rv-detail__inner {
        display: grid; grid-template-rows: 0fr; transition: grid-template-rows 0.32s ease;
    }
    tr.rv-detail.open .rv-detail__inner { grid-template-rows: 1fr; }
    .rv-detail__content { overflow: hidden; min-height: 0; }

    .rv-detail__card {
        margin: 0 14px 14px; border-radius: 12px; overflow: hidden;
        background: var(--surface-2); border: 1px solid var(--border-subtle);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02), var(--shadow-card);
    }
    .rv-detail__head {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
        padding: 13px 16px; border-bottom: 1px solid var(--border-subtle); background: var(--surface-3);
    }
    .rv-detail__title { display: flex; align-items: center; gap: 10px; font-size: 13.5px; font-weight: 700; color: var(--text-1); }
    .rv-detail__title i { color: var(--brand); font-size: 15px; }
    .rv-detail__meta { font-size: 11.5px; color: var(--text-3); }
    .rv-detail__body { padding: 16px; }
    .rv-detail__grid { display: grid; grid-template-columns: minmax(240px, 1fr) minmax(340px, 1.8fr); gap: 14px; }
    @media (max-width: 1100px) { .rv-detail__grid { grid-template-columns: 1fr; } }

    /* Detail panels */
    .rv-panel {
        background: var(--surface); border: 1px solid var(--border-subtle);
        border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px;
    }
    .rv-panel__label {
        font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
        color: var(--text-3); display: flex; align-items: center; gap: 7px;
    }
    .rv-panel__label i { color: var(--brand); font-size: 12px; }

    .rv-kv { display: flex; justify-content: space-between; align-items: baseline; gap: 14px; font-size: 12.5px; padding: 5px 0; border-bottom: 1px dashed var(--border-subtle); }
    .rv-kv:last-child { border-bottom: none; }
    .rv-kv dt { color: var(--text-3); flex-shrink: 0; }
    .rv-kv dd { margin: 0; color: var(--text-1); font-weight: 600; text-align: right; }

    /* Participants grid */
    .rv-parties { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    @media (max-width: 640px) { .rv-parties { grid-template-columns: 1fr; } }
    .rv-party {
        display: flex; align-items: center; gap: 10px; padding: 10px 12px;
        background: var(--surface-2); border: 1px solid var(--border-subtle);
        border-radius: 9px; min-width: 0;
    }
    .rv-party i.lead { font-size: 16px; color: var(--brand); flex-shrink: 0; }

    /* Fee tiles */
    .rv-fees { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    @media (max-width: 520px) { .rv-fees { grid-template-columns: 1fr; } }
    .rv-tile {
        background: var(--surface-2); border: 1px solid var(--border-subtle);
        border-radius: 9px; padding: 10px 12px; text-align: center;
    }
    .rv-tile__label { font-size: 9.5px; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; color: var(--text-3); margin-bottom: 4px; }
    .rv-tile__value { font-size: 14.5px; font-weight: 700; color: var(--text-1); font-variant-numeric: tabular-nums; }
    .rv-tile--total { background: var(--brand-soft); border-color: color-mix(in srgb, var(--brand) 30%, transparent); }
    .rv-tile--total .rv-tile__value { color: var(--brand-strong); font-size: 16px; }

    .rv-payline { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .rv-notes {
        margin-top: 2px; padding: 10px 12px; border-radius: 9px; font-size: 12.5px; color: var(--text-2);
        background: var(--warning-soft); border: 1px dashed color-mix(in srgb, var(--warning) 40%, transparent);
    }
    .rv-notes strong { color: var(--warning); font-size: 10px; letter-spacing: 0.06em; text-transform: uppercase; display: block; margin-bottom: 3px; }

    /* ---- Footer ---- */
    .rv-foot {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
        padding: 12px 18px; border-top: 1px solid var(--border-subtle); background: var(--surface);
        font-size: 12.5px; color: var(--text-3);
    }

    /* ---- Empty state ---- */
    .rv-empty { padding: 48px 20px !important; }

    /* ============================================================
       Receipt preview modal
       ============================================================ */
    .rcpt-modal {
        position: fixed; inset: 0; z-index: 2000;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: opacity 0.2s, visibility 0.2s;
    }
    .rcpt-modal.open { opacity: 1; visibility: visible; }
    .rcpt-modal__backdrop {
        position: absolute; inset: 0;
        background: rgba(9, 12, 20, 0.6); backdrop-filter: blur(2px);
    }
    .rcpt-modal__dialog {
        position: relative; z-index: 1; width: 100%; max-width: 460px;
        max-height: 90vh; overflow-y: auto; margin: 16px;
        background: var(--surface); border: 1px solid var(--border-subtle);
        border-radius: 14px; box-shadow: var(--shadow-pop);
        transform: translateY(12px) scale(0.97);
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .rcpt-modal.open .rcpt-modal__dialog { transform: translateY(0) scale(1); }
    .rcpt-modal__head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);
    }
    .rcpt-modal__head h3 {
        margin: 0; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px;
    }
    .rcpt-modal__head h3 i { color: var(--brand); font-size: 17px; }
    .rcpt-modal__close {
        width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; border: 1px solid var(--border-subtle); background: transparent;
        color: var(--text-2); font-size: 13px; cursor: pointer; transition: all 0.15s;
    }
    .rcpt-modal__close:hover { background: var(--surface-3); color: var(--text-1); }
    .rcpt-modal__body { padding: 20px; }
    .rcpt-modal__foot {
        display: flex; align-items: center; justify-content: flex-end; gap: 10px;
        padding: 14px 20px; border-top: 1px solid var(--border-subtle);
    }

    /* ---- Receipt sheet (white paper) ---- */
    .rcpt-sheet {
        background: #ffffff; color: #111827; border-radius: 10px;
        padding: 22px 22px 16px; font-size: 13px; line-height: 1.45;
        box-shadow: inset 0 0 0 1px rgba(17, 24, 39, 0.06);
    }
    .rcpt-brandrow {
        display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;
        padding-bottom: 12px; border-bottom: 3px solid #14532d;
    }
    .rcpt-brand { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .rcpt-logo { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
    .rcpt-name { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; line-height: 1.15; color: #14532d; }
    .rcpt-name span { color: #16a34a; }
    .rcpt-tagline { font-size: 9px; letter-spacing: 0.05em; text-transform: uppercase; color: #6b7280; font-weight: 600; margin-top: 1px; }
    .rcpt-doc { text-align: right; flex-shrink: 0; }
    .rcpt-doc__label { font-size: 9px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: #6b7280; margin-bottom: 3px; }
    .rcpt-doc__id {
        display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 11.5px; font-weight: 700; color: #14532d; white-space: nowrap;
        background: #f0fdf4; border: 1px solid #bbf7d0; padding: 3px 8px; border-radius: 6px;
    }
    .rcpt-meta {
        display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 6px;
        padding: 8px 2px; border-bottom: 1px dashed #e5e7eb; font-size: 11px; color: #6b7280;
    }
    .rcpt-meta i { font-size: 11px; }
    .rcpt-chip {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 999px;
        font-size: 10.5px; font-weight: 700; letter-spacing: 0.02em;
    }
    .rcpt-chip--live { background: #ecfdf5; color: #166534; }
    .rcpt-chip--green { background: #ecfdf5; color: #166534; }
    .rcpt-chip--amber { background: #fffbeb; color: #92400e; }
    .rcpt-chip--red { background: #fef2f2; color: #991b1b; }
    .rcpt-chip--blue { background: #eff6ff; color: #1e40af; }

    .rcpt-sec { margin-top: 14px; }
    .rcpt-sec__label {
        font-size: 9.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
        color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;
    }
    .rcpt-sec__label i { font-size: 11px; color: #14532d; }
    .rcpt-card {
        background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px;
    }
    .rcpt-strong { font-weight: 700; color: #111827; font-size: 13px; }
    .rcpt-sub { font-size: 11.5px; color: #6b7280; }

    .rcpt-list { margin: 0; }
    .rcpt-kv {
        display: flex; justify-content: space-between; align-items: baseline;
        font-size: 12px; padding: 4px 0; border-bottom: 1px dashed #f3f4f6;
    }
    .rcpt-kv:last-child { border-bottom: none; }
    .rcpt-kv dt { color: #6b7280; }
    .rcpt-kv dd { margin: 0; font-weight: 600; color: #111827; }

    .rcpt-total {
        display: flex; justify-content: space-between; align-items: baseline;
        margin: 8px 0 10px; padding: 10px 12px;
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
    }
    .rcpt-total dt { font-size: 12px; font-weight: 600; color: #14532d; }
    .rcpt-total dd { margin: 0; font-size: 18px; font-weight: 800; color: #14532d; }

    .rcpt-chips { display: flex; flex-wrap: wrap; gap: 6px; }

    .rcpt-footer {
        margin-top: 18px; padding-top: 12px; border-top: 2px solid #14532d;
        text-align: center; font-size: 10.5px; font-weight: 600; color: #14532d; line-height: 1.5;
    }
    .rcpt-footer small { display: block; margin-top: 4px; font-size: 9px; font-weight: 500; color: #9ca3af; }

    /* ---- Print styles ---- */
    @media print {
        body > *:not(.rcpt-printing) { display: none !important; }
        body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
        body.rcpt-printing { background: #fff !important; }
        body.rcpt-printing > * { display: none !important; }
        body.rcpt-printing .rcpt-modal,
        body.rcpt-printing .rcpt-modal.open { position: static; opacity: 1; visibility: visible; background: none !important; }
        body.rcpt-printing .rcpt-modal__backdrop,
        body.rcpt-printing .rcpt-modal__head,
        body.rcpt-printing .rcpt-modal__foot { display: none !important; }
        body.rcpt-printing .rcpt-modal__dialog {
            width: 100%; max-width: 100%; margin: 0; border: none; border-radius: 0; box-shadow: none; transform: none;
            max-height: none; overflow: visible;
        }
        body.rcpt-printing .rcpt-modal__body { padding: 0; }
        body.rcpt-printing .rcpt-sheet { box-shadow: none; border-radius: 0; padding: 18px; max-width: 100%; margin: 0; }
        .rv-btn[title]::after { display: none !important; }
        * { print-color-adjust: exact !important; -webkit-print-color-adjust: exact !important; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
<div class="admin-pagehead">
    <div class="admin-pagehead__title">
        <h1>Rental History</h1>
        <p>Completed, returned, cancelled, and expired rental records</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;flex-direction:column;gap:0;">

    
    <div class="rv-filter-card">
        <div class="rv-filter-head"><i class="bi bi-funnel"></i>Refine History</div>
        <form method="GET" action="<?php echo e(route('admin.rentals.history')); ?>" class="rv-filter-grid">
            <div class="rv-filter-field rv-filter-field--search">
                <label for="rvSearch">Search</label>
                <div class="rv-filter-ctrl has-icon no-caret">
                    <i class="bi bi-search"></i>
                    <input type="text" id="rvSearch" data-table-search placeholder="Rider, bicycle, ID...">
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvStatus">Status</label>
                <div class="rv-filter-ctrl">
                    <select name="status" id="rvStatus">
                        <option value="">All History</option>
                        <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed</option>
                        <option value="returned" <?php echo e(request('status') === 'returned' ? 'selected' : ''); ?>>Returned</option>
                        <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                        <option value="expired" <?php echo e(request('status') === 'expired' ? 'selected' : ''); ?>>Expired</option>
                    </select>
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvFrom">Start Date</label>
                <div class="rv-filter-ctrl no-caret">
                    <input type="date" name="date_from" id="rvFrom" value="<?php echo e(request('date_from')); ?>">
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvTo">End Date</label>
                <div class="rv-filter-ctrl no-caret">
                    <input type="date" name="date_to" id="rvTo" value="<?php echo e(request('date_to')); ?>">
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvBike">Bicycle</label>
                <div class="rv-filter-ctrl">
                    <select name="bicycle_id" id="rvBike">
                        <option value="">All Bicycles</option>
                        <?php $__currentLoopData = $bicyclesList ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>" <?php echo e(request('bicycle_id') == $b->id ? 'selected' : ''); ?>><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvRider">Rider</label>
                <div class="rv-filter-ctrl">
                    <select name="rider_id" id="rvRider">
                        <option value="">All Riders</option>
                        <?php $__currentLoopData = $ridersList ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r->id); ?>" <?php echo e(request('rider_id') == $r->id ? 'selected' : ''); ?>><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="rv-filter-actions">
                <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="<?php echo e(route('admin.rentals.history')); ?>" class="btn-admin btn-admin--ghost btn-admin--sm" title="Clear all filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    
    <div class="rv-table-card">
        <div class="rv-table-scroll">
            <table class="rv-table" id="rvTable">
                <thead>
                    <tr>
                        <th>Rental ID</th>
                        <th>Rider</th>
                        <th>Bicycle</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Duration</th>
                        <th>Fee</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rentals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rental): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $durH = $rental->durationMinutes ? floor($rental->durationMinutes / 60) : null;
                            $durM = $rental->durationMinutes % 60;
                            $durLabel = $rental->durationMinutes ? ($durH ? $durH . 'h ' . $durM . 'm' : $durM . 'm') : null;
                        ?>
                        <tr class="rv-row" data-detail="rvd-<?php echo e($rental->id); ?>">
                            <td><span class="rv-id"><small>REN</small>#<?php echo e($rental->id); ?></span></td>
                            <td>
                                <div class="rv-rider">
                                    <div class="user-avatar" style="width:30px;height:30px;font-size:11px;">
                                        <?php echo e(strtoupper(substr($rental->rider->name ?? 'U', 0, 1))); ?>

                                    </div>
                                    <div style="min-width:0;">
                                        <div class="rv-rider__name"><?php echo e($rental->rider->name ?? 'Unknown'); ?></div>
                                        <div class="rv-rider__email"><?php echo e($rental->rider->email ?? ''); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="rv-bike__name"><?php echo e($rental->bicycle->name ?? 'Unknown'); ?></div>
                                <?php if($rental->bicycle?->serialNumber): ?>
                                    <div class="rv-bike__sub">SN &middot; <?php echo e($rental->bicycle->serialNumber); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="rv-dt">
                                    <div class="rv-dt__date"><?php echo e($rental->startTime?->format('M d, Y') ?? '&mdash;'); ?></div>
                                    <div class="rv-dt__time"><?php echo e($rental->startTime?->format('h:i A') ?? 'Not started'); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="rv-dt">
                                    <div class="rv-dt__date"><?php echo e($rental->endTime?->format('M d, Y') ?? '&mdash;'); ?></div>
                                    <div class="rv-dt__time"><?php echo e($rental->endTime?->format('h:i A') ?? '&mdash;'); ?></div>
                                </div>
                            </td>
                            <td>
                                <?php if($durLabel): ?>
                                    <span class="rv-chip"><i class="bi bi-hourglass-split"></i><?php echo e($durLabel); ?></span>
                                <?php else: ?>
                                    <span style="color:var(--text-3);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="rv-fee">&#8369;<?php echo e(number_format($rental->totalFee ?? 0, 2)); ?></div>
                                <div class="rv-fee__sub">&#8369;<?php echo e(number_format($rental->ratePerHour ?? 0, 2)); ?>/hr</div>
                            </td>
                            <td>
                                <?php if($rental->paymentMethod === 'gcash'): ?>
                                    <span class="rv-method rv-method--gcash"><i class="bi bi-phone"></i>GCash</span>
                                <?php else: ?>
                                    <span class="rv-method rv-method--cash"><i class="bi bi-cash-stack"></i>Cash</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($rental->status === 'completed'): ?>
                                    <span class="rv-status rv-status--completed rv-status--dot">Completed</span>
                                <?php elseif($rental->status === 'returned'): ?>
                                    <span class="rv-status rv-status--returned rv-status--dot">Returned</span>
                                <?php elseif($rental->status === 'cancelled'): ?>
                                    <span class="rv-status rv-status--cancelled rv-status--dot">Cancelled</span>
                                <?php elseif($rental->status === 'expired'): ?>
                                    <span class="rv-status rv-status--expired rv-status--dot">Expired</span>
                                <?php else: ?>
                                    <span class="rv-status rv-status--cancelled rv-status--dot"><?php echo e(ucfirst($rental->status)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($rental->paymentStatus === 'paid'): ?>
                                    <span class="rv-status rv-status--paid"><i class="bi bi-check-circle-fill"></i>Paid</span>
                                <?php elseif($rental->paymentStatus === 'pending'): ?>
                                    <span class="rv-status rv-status--pending rv-status--dot">Pending</span>
                                <?php else: ?>
                                    <span class="rv-status rv-status--unpaid rv-status--dot"><?php echo e(ucfirst($rental->paymentStatus ?? 'unpaid')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="rv-actions">
                                    <button type="button" class="rv-btn rv-btn--view"
                                            data-detail-toggle="rvd-<?php echo e($rental->id); ?>"
                                            title="View details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php
                                        $receiptData = [
                                            'id'        => $rental->rentalId ?? ('REN-' . str_pad($rental->id, 4, '0', STR_PAD_LEFT)),
                                            'rider'     => $rental->rider->name ?? $rental->riderName ?? 'Unknown',
                                            'email'     => $rental->rider->email ?? $rental->riderEmail ?? '',
                                            'bike'      => $rental->bicycle->name ?? $rental->bicycleName ?? 'Unknown',
                                            'serial'    => $rental->bicycle->serialNumber ?? $rental->bicycleSerial ?? '',
                                            'start'     => $rental->startTime?->format('M d, Y \a\t h:i A') ?? '—',
                                            'end'       => $rental->endTime?->format('M d, Y \a\t h:i A') ?? '',
                                            'startIso'  => $rental->startTime?->toIso8601String() ?? '',
                                            'hasEnd'    => (bool) $rental->endTime,
                                            'duration'  => $durLabel,
                                            'rate'      => number_format((float) ($rental->ratePerHour ?? 0), 2),
                                            'total'     => number_format((float) ($rental->totalFee ?? 0), 2),
                                            'method'    => $rental->paymentMethod === 'gcash' ? 'GCash' : 'Cash',
                                            'payStatus' => ucfirst($rental->paymentStatus ?? 'Unpaid'),
                                            'status'    => ucfirst($rental->status),
                                            'issued'    => now()->format('M d, Y \a\t h:i A'),
                                        ];
                                    ?>
                                    <button type="button" class="rv-btn rv-btn--print"
                                            data-print-receipt="<?php echo e(json_encode($receiptData)); ?>"
                                            title="Print receipt">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        
                        <tr class="rv-detail" id="rvd-<?php echo e($rental->id); ?>">
                            <td colspan="11" class="rv-detail__cell">
                                <div class="rv-detail__inner">
                                    <div class="rv-detail__content">
                                        <div class="rv-detail__card">
                                            <div class="rv-detail__head">
                                                <div class="rv-detail__title">
                                                    <i class="bi bi-receipt"></i>
                                                    Rental #<?php echo e($rental->id); ?>

                                                    <?php if($rental->status === 'completed'): ?>
                                                        <span class="rv-status rv-status--completed rv-status--dot">Completed</span>
                                                    <?php elseif($rental->status === 'returned'): ?>
                                                        <span class="rv-status rv-status--returned rv-status--dot">Returned</span>
                                                    <?php elseif($rental->status === 'cancelled'): ?>
                                                        <span class="rv-status rv-status--cancelled rv-status--dot">Cancelled</span>
                                                    <?php elseif($rental->status === 'expired'): ?>
                                                        <span class="rv-status rv-status--expired rv-status--dot">Expired</span>
                                                    <?php else: ?>
                                                        <span class="rv-status rv-status--cancelled rv-status--dot"><?php echo e(ucfirst($rental->status)); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="rv-detail__meta">
                                                    <i class="bi bi-clock-history me-1"></i>Created <?php echo e($rental->created_at->format('M d, Y h:i A')); ?>

                                                </div>
                                            </div>
                                            <div class="rv-detail__body">
                                                <div class="rv-detail__grid">
                                                    
                                                    <div class="rv-panel">
                                                        <div class="rv-panel__label"><i class="bi bi-route"></i>Rental Details</div>
                                                        <dl style="margin:0;">
                                                            <div class="rv-kv"><dt>Start Time</dt><dd><?php echo e($rental->startTime?->format('M d, Y h:i A') ?? '&mdash;'); ?></dd></div>
                                                            <div class="rv-kv"><dt>End Time</dt><dd><?php echo e($rental->endTime?->format('M d, Y h:i A') ?? '&mdash;'); ?></dd></div>
                                                            <div class="rv-kv"><dt>Duration</dt><dd><?php echo e($durLabel ?? '&mdash;'); ?></dd></div>
                                                            <div class="rv-kv"><dt>Created</dt><dd><?php echo e($rental->created_at->format('M d, Y h:i A')); ?></dd></div>
                                                        </dl>
                                                        <?php if($rental->notes): ?>
                                                            <div class="rv-notes"><strong><i class="bi bi-sticky me-1"></i>Notes</strong><?php echo e($rental->notes); ?></div>
                                                        <?php endif; ?>
                                                    </div>

                                                    
                                                    <div class="rv-panel">
                                                        <div class="rv-panel__label"><i class="bi bi-people"></i>Participants &amp; Payment</div>
                                                        <div class="rv-parties">
                                                            <div class="rv-party">
                                                                <div class="user-avatar" style="width:32px;height:32px;font-size:12px;"><?php echo e(strtoupper(substr($rental->rider->name ?? 'U', 0, 1))); ?></div>
                                                                <div style="min-width:0;">
                                                                    <div class="rv-rider__name"><?php echo e($rental->rider->name ?? 'Unknown'); ?></div>
                                                                    <div class="rv-rider__email"><?php echo e($rental->rider->email ?? '&mdash;'); ?></div>
                                                                </div>
                                                            </div>
                                                            <div class="rv-party">
                                                                <i class="bi bi-bicycle lead"></i>
                                                                <div style="min-width:0;">
                                                                    <div class="rv-rider__name"><?php echo e($rental->bicycle->name ?? 'Unknown'); ?></div>
                                                                    <div class="rv-rider__email">&#8369;<?php echo e(number_format($rental->bicycle->hourlyRate ?? 0, 2)); ?>/hour</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="rv-fees">
                                                            <div class="rv-tile">
                                                                <div class="rv-tile__label">Hourly Rate</div>
                                                                <div class="rv-tile__value">&#8369;<?php echo e(number_format($rental->ratePerHour ?? 0, 2)); ?></div>
                                                            </div>
                                                            <div class="rv-tile">
                                                                <div class="rv-tile__label">Duration</div>
                                                                <div class="rv-tile__value"><?php echo e($durLabel ?? '&mdash;'); ?></div>
                                                            </div>
                                                            <div class="rv-tile rv-tile--total">
                                                                <div class="rv-tile__label">Total Fee</div>
                                                                <div class="rv-tile__value">&#8369;<?php echo e(number_format($rental->totalFee ?? 0, 2)); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="rv-payline">
                                                            <?php if($rental->paymentMethod === 'gcash'): ?>
                                                                <span class="rv-method rv-method--gcash"><i class="bi bi-phone"></i>GCash</span>
                                                            <?php else: ?>
                                                                <span class="rv-method rv-method--cash"><i class="bi bi-cash-stack"></i>Cash</span>
                                                            <?php endif; ?>
                                                            <?php if($rental->paymentStatus === 'paid'): ?>
                                                                <span class="rv-status rv-status--paid"><i class="bi bi-check-circle-fill"></i>Paid</span>
                                                            <?php elseif($rental->paymentStatus === 'pending'): ?>
                                                                <span class="rv-status rv-status--pending rv-status--dot">Pending</span>
                                                            <?php else: ?>
                                                                <span class="rv-status rv-status--unpaid rv-status--dot"><?php echo e(ucfirst($rental->paymentStatus ?? 'unpaid')); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="rv-empty">
                                <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'bi-clock-history','title' => 'No rental history found','message' => 'No finished rentals match your filters.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-clock-history','title' => 'No rental history found','message' => 'No finished rentals match your filters.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="rv-foot">
            <span>
                <i class="bi bi-list-ul me-1"></i>Showing <?php echo e($rentals->total()); ?> record<?php echo e($rentals->total() === 1 ? '' : 's'); ?>

            </span>
            <?php if(method_exists($rentals, 'links')): ?>
                <?php echo e($rentals->withQueryString()->links()); ?>

            <?php endif; ?>
        </div>
    </div>
</div>


<div class="rcpt-modal" id="rcptModal">
    <div class="rcpt-modal__backdrop" data-rcpt-close></div>
    <div class="rcpt-modal__dialog" role="dialog" aria-modal="true" aria-label="Rental receipt preview">
        <div class="rcpt-modal__head">
            <h3><i class="bi bi-receipt"></i>Receipt Preview</h3>
            <button type="button" class="rcpt-modal__close" data-rcpt-close aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="rcpt-modal__body">
            <div class="rcpt-sheet" id="rcptSheet">
                <div class="rcpt-brandrow">
                    <div class="rcpt-brand">
                        <img src="<?php echo e(asset('assets/img/Logo.png')); ?>" alt="Pedalya logo" class="rcpt-logo">
                        <div style="min-width:0;">
                            <div class="rcpt-name">Peda<span>lya</span></div>
                            <div class="rcpt-tagline">IoT Bicycle Rental System</div>
                        </div>
                    </div>
                    <div class="rcpt-doc">
                        <div class="rcpt-doc__label">Official Receipt</div>
                        <span class="rcpt-doc__id" id="rcptId">&mdash;</span>
                    </div>
                </div>

                <div class="rcpt-meta">
                    <span><i class="bi bi-clock-history me-1"></i>Issued: <strong id="rcptIssued" style="color:#111827;">&mdash;</strong></span>
                    <span class="rcpt-chip rcpt-chip--live" id="rcptRideChip"><i class="bi bi-play-fill"></i>Ride in progress</span>
                </div>

                <div class="rcpt-sec">
                    <div class="rcpt-sec__label"><i class="bi bi-person-circle"></i>Rider</div>
                    <div class="rcpt-card">
                        <div class="rcpt-strong" id="rcptRiderName">&mdash;</div>
                        <div class="rcpt-sub" id="rcptRiderContact">&mdash;</div>
                    </div>
                </div>

                <div class="rcpt-sec">
                    <div class="rcpt-sec__label"><i class="bi bi-bicycle"></i>Bicycle</div>
                    <div class="rcpt-card">
                        <div class="rcpt-strong" id="rcptBikeName">&mdash;</div>
                        <div class="rcpt-sub" id="rcptBikeSub">&mdash;</div>
                    </div>
                </div>

                <div class="rcpt-sec">
                    <div class="rcpt-sec__label"><i class="bi bi-calendar3"></i>Rental Period</div>
                    <dl class="rcpt-list">
                        <div class="rcpt-kv"><dt>Start</dt><dd id="rcptStart">&mdash;</dd></div>
                        <div class="rcpt-kv"><dt>End</dt><dd id="rcptEnd">In progress</dd></div>
                        <div class="rcpt-kv">
                            <dt>Duration</dt>
                            <dd><span id="rcptDuration">&mdash;</span> <small style="font-weight:500;font-size:10px;color:#c2410c;" id="rcptDurationNote"></small></dd>
                        </div>
                        <div class="rcpt-kv"><dt>Rate</dt><dd>&#8369;<span id="rcptRate">0.00</span> / hour</dd></div>
                    </dl>
                </div>

                <div class="rcpt-sec">
                    <div class="rcpt-sec__label"><i class="bi bi-cash-coin"></i>Payment</div>
                    <dl class="rcpt-total">
                        <dt>Total Fee</dt>
                        <dd>&#8369;<span id="rcptTotal">0.00</span></dd>
                    </dl>
                    <div class="rcpt-chips">
                        <span class="rcpt-chip" id="rcptMethod">&mdash;</span>
                        <span class="rcpt-chip" id="rcptPayStatus">&mdash;</span>
                    </div>
                </div>

                <div class="rcpt-footer">
                    Thank you for riding with Pedalya!
                    <small>
                        Pedalya IoT Bicycle Rental Management System &bull; Azuela Cove, Davao City<br>
                        This receipt was generated electronically and is valid without signature.
                    </small>
                </div>
            </div>
        </div>
        <div class="rcpt-modal__foot">
            <button type="button" class="btn-admin btn-admin--secondary btn-admin--sm" data-rcpt-close>Close</button>
            <button type="button" class="btn-admin btn-admin--primary btn-admin--sm" id="rcptPrintBtn">
                <i class="bi bi-printer me-1"></i>Print Receipt
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(function () {
    var tbody = document.querySelector('#rvTable tbody');
    if (!tbody) return;

    /* --- Expandable details --- */
    function closeAll(exceptBtn) {
        tbody.querySelectorAll('tr.rv-detail.open').forEach(function (r) { r.classList.remove('open'); });
        tbody.querySelectorAll('tr.rv-row.is-open').forEach(function (r) { r.classList.remove('is-open'); });
        tbody.querySelectorAll('.rv-btn--view.is-open').forEach(function (b) {
            if (b !== exceptBtn) {
                b.classList.remove('is-open');
                var ic = b.querySelector('i');
                if (ic) ic.className = 'bi bi-eye';
            }
        });
    }

    tbody.querySelectorAll('[data-detail-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.getAttribute('data-detail-toggle'));
            if (!target) return;
            var wasOpen = target.classList.contains('open');
            closeAll(btn);
            if (!wasOpen) {
                target.classList.add('open');
                var main = tbody.querySelector('tr.rv-row[data-detail="' + btn.getAttribute('data-detail-toggle') + '"]');
                if (main) main.classList.add('is-open');
                btn.classList.add('is-open');
                var ic = btn.querySelector('i');
                if (ic) ic.className = 'bi bi-eye-slash';
            }
        });
    });

    /* --- Client-side search --- */
    var searchInput = document.querySelector('[data-table-search]');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = searchInput.value.toLowerCase().trim();
            tbody.querySelectorAll('tr.rv-row').forEach(function (row) {
                var hit = row.textContent.toLowerCase().indexOf(q) !== -1;
                var det = document.getElementById(row.getAttribute('data-detail'));
                row.style.display = hit ? '' : 'none';
                if (!hit && det) { det.style.display = 'none'; det.classList.remove('open'); }
                else if (det) { det.style.display = ''; }
            });
        });
    }
})();

/* --- Receipt preview & print --- */
(function () {
    var modal = document.getElementById('rcptModal');
    if (!modal) return;
    var current = null;
    var tick = null;

    function el(id) { return document.getElementById(id); }

    function fmtDuration(totalMin) {
        var h = Math.floor(totalMin / 60), m = totalMin % 60;
        return h ? h + 'h ' + m + 'm' : m + 'm';
    }

    function chip(node, text, tone) { node.textContent = text; node.className = 'rcpt-chip ' + tone; }

    function refreshLive() {
        if (!current || current.hasEnd) return;
        var start = new Date(current.startIso).getTime();
        var mins = isNaN(start) ? 0 : Math.max(0, Math.floor((Date.now() - start) / 60000));
        el('rcptDuration').textContent = fmtDuration(mins);
        el('rcptDurationNote').textContent = '(ongoing)';
    }

    function openReceipt(d) {
        current = d;
        el('rcptId').textContent = d.id;
        el('rcptIssued').textContent = d.issued;
        el('rcptRiderName').textContent = d.rider;
        var contact = [d.email].filter(Boolean).join('');
        el('rcptRiderContact').textContent = contact || '\u2014';
        el('rcptRiderContact').style.display = contact ? '' : 'none';
        el('rcptBikeName').textContent = d.bike;
        el('rcptBikeSub').textContent = d.serial ? 'SN \u00B7 ' + d.serial : '\u2014';
        el('rcptStart').textContent = d.start;
        el('rcptEnd').textContent = d.hasEnd ? d.end : 'In progress';
        el('rcptDurationNote').textContent = '';
        if (d.duration && d.hasEnd) { el('rcptDuration').textContent = d.duration; }
        else { refreshLive(); }
        el('rcptRate').textContent = d.rate;
        el('rcptTotal').textContent = d.total;
        chip(el('rcptMethod'), d.method, d.method === 'GCash' ? 'rcpt-chip--blue' : 'rcpt-chip--green');
        var st = String(d.payStatus).toLowerCase();
        chip(el('rcptPayStatus'), d.payStatus,
             st === 'paid' ? 'rcpt-chip--green' : (st === 'pending' ? 'rcpt-chip--amber' : 'rcpt-chip--red'));
        var ride = String(d.status || '').toLowerCase();
        var rideTone = ride === 'completed' ? 'rcpt-chip--green'
                     : (ride === 'pending' ? 'rcpt-chip--amber'
                     : (ride === 'cancelled' ? 'rcpt-chip--red'
                     : (ride === 'overdue' ? 'rcpt-chip--red' : 'rcpt-chip--live')));
        var rideIcon = ride === 'completed' ? 'bi-check-circle-fill'
                     : (ride === 'pending' ? 'bi-hourglass-split'
                     : (ride === 'cancelled' ? 'bi-x-circle-fill'
                     : (ride === 'overdue' ? 'bi-exclamation-triangle-fill' : 'bi-play-fill')));
        var rideLabel = ride === 'active' ? 'Ride in progress' : d.status;
        var rc = el('rcptRideChip');
        rc.className = 'rcpt-chip ' + rideTone;
        rc.innerHTML = '<i class="bi ' + rideIcon + '"></i>' + rideLabel;
        modal.classList.add('open');
        document.body.classList.add('rcpt-printing');
        if (tick) clearInterval(tick);
        tick = setInterval(refreshLive, 30000);
    }

    function closeReceipt() {
        modal.classList.remove('open');
        document.body.classList.remove('rcpt-printing');
        if (tick) { clearInterval(tick); tick = null; }
        current = null;
    }

    document.querySelectorAll('[data-print-receipt]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            try { openReceipt(JSON.parse(btn.getAttribute('data-print-receipt'))); } catch (e) { /* ignore */ }
        });
    });

    modal.querySelectorAll('[data-rcpt-close]').forEach(function (node) {
        node.addEventListener('click', closeReceipt);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeReceipt();
    });

    var printBtn = el('rcptPrintBtn');
    if (printBtn) printBtn.addEventListener('click', function () { window.print(); });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\pedalya\resources\views\admin\rentals-history.blade.php ENDPATH**/ ?>