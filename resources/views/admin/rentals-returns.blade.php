@extends('layouts.admin')

@section('title', 'Returns')

@section('styles')
<style>
    /* ============================================================
       Returns — Dedicated returned-ride view
       ============================================================ */

    /* ---- Summary stat strip ---- */
    .rv-summary {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;
        margin-bottom: 16px;
    }
    @media (max-width: 1100px) { .rv-summary { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 560px) { .rv-summary { grid-template-columns: 1fr; } }
    .rv-summary__card {
        background: var(--surface);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius);
        box-shadow: var(--shadow-card);
        padding: 14px 16px;
        display: flex; align-items: center; gap: 13px;
    }
    .rv-summary__icon {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 17px;
        background: var(--brand-soft); color: var(--brand-strong);
    }
    .rv-summary__icon--accent { background: var(--accent-soft); color: var(--accent); }
    .rv-summary__icon--success { background: var(--success-soft); color: var(--success); }
    .rv-summary__icon--warning { background: var(--warning-soft); color: var(--warning); }
    .rv-summary__label {
        font-size: 10px; font-weight: 700; letter-spacing: 0.07em;
        text-transform: uppercase; color: var(--text-3);
    }
    .rv-summary__value { font-size: 19px; font-weight: 800; color: var(--text-1); line-height: 1.15; font-variant-numeric: tabular-nums; }
    .rv-summary__sub { font-size: 11px; color: var(--text-3); }

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
        width: 100%; border-collapse: collapse; font-size: 13px; min-width: 1020px;
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

    .rv-id {
        display: inline-flex; align-items: baseline; gap: 4px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px; font-weight: 600; color: var(--text-1);
        background: var(--surface-3); border: 1px solid var(--border-subtle);
        border-radius: 7px; padding: 3px 9px;
    }
    .rv-id small { font-size: 9.5px; font-weight: 700; letter-spacing: 0.06em; color: var(--text-3); }

    .rv-rider { display: flex; align-items: center; gap: 10px; min-width: 160px; }
    .rv-rider__name { font-weight: 600; color: var(--text-1); font-size: 13px; line-height: 1.25; }
    .rv-rider__email { font-size: 11.5px; color: var(--text-3); line-height: 1.3; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .rv-bike__name { font-weight: 600; color: var(--text-1); font-size: 13px; }
    .rv-bike__sub { font-size: 11.5px; color: var(--text-3); }

    .rv-dt { line-height: 1.35; }
    .rv-dt__date { font-size: 12.5px; color: var(--text-1); font-weight: 500; }
    .rv-dt__time { font-size: 11px; color: var(--text-3); }

    .rv-chip {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11.5px; font-weight: 600; color: var(--text-2);
        background: var(--surface-3); border: 1px solid var(--border-subtle);
        border-radius: 999px; padding: 3px 10px; white-space: nowrap;
    }
    .rv-chip--avail { color: var(--success); background: var(--success-soft); border-color: color-mix(in srgb, var(--success) 22%, transparent); }
    .rv-chip--rented { color: var(--brand-strong); background: var(--brand-soft); border-color: color-mix(in srgb, var(--brand) 24%, transparent); }

    .rv-fee { font-weight: 700; color: var(--text-1); font-variant-numeric: tabular-nums; font-size: 13.5px; }
    .rv-fee__sub { font-size: 10.5px; color: var(--text-3); font-weight: 500; }

    .rv-status {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 11px; border-radius: 999px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.03em;
        white-space: nowrap; border: 1px solid transparent;
    }
    .rv-status i { font-size: 11px; }
    .rv-status--completed { background: var(--brand-soft); color: var(--brand-strong); border-color: color-mix(in srgb, var(--brand) 24%, transparent); }
    .rv-status--returned { background: var(--accent-soft); color: var(--accent); border-color: color-mix(in srgb, var(--accent) 22%, transparent); }
    .rv-status--cancelled { background: var(--surface-3); color: var(--text-3); border-color: var(--border-subtle); }
    .rv-status--paid { background: var(--success-soft); color: var(--success); border-color: color-mix(in srgb, var(--success) 22%, transparent); }
    .rv-status--dot::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

    .rv-method {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 600; padding: 3px 10px;
        border-radius: 999px; white-space: nowrap;
    }
    .rv-method--gcash { background: var(--accent-soft); color: var(--accent); }
    .rv-method--cash { background: var(--success-soft); color: var(--success); }

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

    .rv-parties { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    @media (max-width: 640px) { .rv-parties { grid-template-columns: 1fr; } }
    .rv-party {
        display: flex; align-items: center; gap: 10px; padding: 10px 12px;
        background: var(--surface-2); border: 1px solid var(--border-subtle);
        border-radius: 9px; min-width: 0;
    }
    .rv-party i.lead { font-size: 16px; color: var(--brand); flex-shrink: 0; }

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

    .rv-foot {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
        padding: 12px 18px; border-top: 1px solid var(--border-subtle); background: var(--surface);
        font-size: 12.5px; color: var(--text-3);
    }

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
    .rcpt-sec { margin-top: 12px; }
    .rcpt-sec__label {
        display: flex; align-items: center; gap: 5px; margin-bottom: 5px;
        font-size: 9px; font-weight: 800; letter-spacing: 0.09em; text-transform: uppercase; color: #6b7280;
    }
    .rcpt-sec__label i { font-size: 11px; color: #16a34a; }
    .rcpt-card {
        background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 9px 12px;
    }
    .rcpt-strong { font-size: 13px; font-weight: 700; color: #111827; line-height: 1.3; }
    .rcpt-sub { font-size: 11px; color: #6b7280; margin-top: 2px; overflow-wrap: anywhere; }
    .rcpt-list { margin: 0; }
    .rcpt-kv {
        display: flex; justify-content: space-between; align-items: baseline; gap: 10px;
        padding: 5px 2px; border-bottom: 1px dashed #e5e7eb; font-size: 12px;
    }
    .rcpt-kv:last-child { border-bottom: none; }
    .rcpt-kv dt { color: #6b7280; white-space: nowrap; }
    .rcpt-kv dd { margin: 0; font-weight: 600; color: #111827; text-align: right; }
    .rcpt-total {
        display: flex; justify-content: space-between; align-items: center; gap: 10px;
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
        padding: 10px 12px; margin: 0 0 8px;
    }
    .rcpt-total dt { font-size: 10px; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; color: #14532d; }
    .rcpt-total dd { margin: 0; font-size: 20px; font-weight: 800; color: #14532d; font-variant-numeric: tabular-nums; line-height: 1; }
    .rcpt-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .rcpt-chip {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10.5px; font-weight: 700; letter-spacing: 0.02em;
        border-radius: 999px; padding: 3px 10px; border: 1px solid transparent; white-space: nowrap;
    }
    .rcpt-chip i { font-size: 10.5px; }
    .rcpt-chip--green { background: #ecfdf3; color: #15803d; border-color: #bbf7d0; }
    .rcpt-chip--blue { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .rcpt-chip--amber { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .rcpt-chip--red { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .rcpt-chip--live { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .rcpt-footer {
        margin-top: 14px; padding-top: 10px; border-top: 2px solid #14532d;
        text-align: center; font-size: 11.5px; font-weight: 600; color: #374151;
    }
    .rcpt-footer small { display: block; margin-top: 3px; font-size: 9px; font-weight: 400; color: #9ca3af; line-height: 1.5; }

    @media (max-width: 520px) {
        .rcpt-modal__dialog { max-width: 100%; margin: 8px; }
        .rcpt-sheet { padding: 16px 14px 12px; }
        .rcpt-total dd { font-size: 17px; }
    }

    /* ============================================================
       Print — output ONLY the receipt on a clean white page
       ============================================================ */
    @media print {
        @page { size: A4 portrait; margin: 14mm; }
        html, body { background: #ffffff !important; }
        body.rcpt-printing .admin-sidebar,
        body.rcpt-printing .sidebar-overlay,
        body.rcpt-printing .admin-toasts,
        body.rcpt-printing .admin-main > header.admin-topbar,
        body.rcpt-printing .admin-pagehead,
        body.rcpt-printing .alert-pedalya,
        body.rcpt-printing .rv-summary,
        body.rcpt-printing .rv-filter-card,
        body.rcpt-printing .rv-table-card { display: none !important; }
        body.rcpt-printing * { visibility: hidden !important; box-shadow: none !important; }
        body.rcpt-printing .rcpt-sheet,
        body.rcpt-printing .rcpt-sheet * { visibility: visible !important; box-shadow: none !important; }
        body.rcpt-printing .rcpt-modal {
            position: static !important; display: block !important;
            opacity: 1 !important; visibility: visible !important;
            padding: 0 !important; overflow: visible !important;
        }
        body.rcpt-printing .rcpt-modal__backdrop,
        body.rcpt-printing .rcpt-modal__head,
        body.rcpt-printing .rcpt-modal__foot { display: none !important; }
        body.rcpt-printing .rcpt-modal__dialog {
            position: static !important; display: block !important;
            width: auto !important; max-width: none !important; max-height: none !important;
            border: none !important; border-radius: 0 !important;
            background: transparent !important; transform: none !important; overflow: visible !important;
        }
        body.rcpt-printing .rcpt-modal__body { padding: 0 !important; overflow: visible !important; }
        body.rcpt-printing .rcpt-sheet {
            width: 100% !important; max-width: 100% !important;
            margin: 0 !important; padding: 0 !important;
            border-radius: 0 !important; box-shadow: none !important;
        }
        .rcpt-sheet, .rcpt-sheet * {
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
    }
</style>
@endsection

@section('page-header')
<div class="admin-pagehead">
    <div class="admin-pagehead__title">
        <h1>Returns</h1>
        <p>Returned and completed rides with settlement details</p>
    </div>
</div>
@endsection

@section('content')
<div style="display:flex;flex-direction:column;gap:0;">

    {{-- ===== Summary strip ===== --}}
    <div class="rv-summary">
        <div class="rv-summary__card">
            <div class="rv-summary__icon"><i class="bi bi-arrow-return-left"></i></div>
            <div>
                <div class="rv-summary__label">Total Returns</div>
                <div class="rv-summary__value">{{ number_format($summary['total']) }}</div>
                <div class="rv-summary__sub">completed &amp; settled rides</div>
            </div>
        </div>
        <div class="rv-summary__card">
            <div class="rv-summary__icon rv-summary__icon--success"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="rv-summary__label">Fee Collected</div>
                <div class="rv-summary__value">&#8369;{{ number_format($summary['totalFee'], 2) }}</div>
                <div class="rv-summary__sub">from all returns</div>
            </div>
        </div>
        <div class="rv-summary__card">
            <div class="rv-summary__icon rv-summary__icon--accent"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="rv-summary__label">Ride Time</div>
                @php
                    $sumH = floor($summary['totalDuration'] / 60);
                    $sumM = $summary['totalDuration'] % 60;
                @endphp
                <div class="rv-summary__value">{{ $sumH ? $sumH . 'h ' . $sumM . 'm' : $sumM . 'm' }}</div>
                <div class="rv-summary__sub">total riding duration</div>
            </div>
        </div>
        <div class="rv-summary__card">
            <div class="rv-summary__icon rv-summary__icon--warning"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="rv-summary__label">Returned Today</div>
                <div class="rv-summary__value">{{ number_format($summary['today']) }}</div>
                <div class="rv-summary__sub">rides returned today</div>
            </div>
        </div>
    </div>

    {{-- ===== Filter bar ===== --}}
    <div class="rv-filter-card">
        <div class="rv-filter-head"><i class="bi bi-funnel"></i>Refine Returns</div>
        <form method="GET" action="{{ route('admin.rentals.returns') }}" class="rv-filter-grid">
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
                        <option value="">All Returns</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvFrom">Start Date</label>
                <div class="rv-filter-ctrl no-caret">
                    <input type="date" name="date_from" id="rvFrom" value="{{ request('date_from') }}">
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvTo">End Date</label>
                <div class="rv-filter-ctrl no-caret">
                    <input type="date" name="date_to" id="rvTo" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvBike">Bicycle</label>
                <div class="rv-filter-ctrl">
                    <select name="bicycle_id" id="rvBike">
                        <option value="">All Bicycles</option>
                        @foreach($bicyclesList ?? [] as $b)
                            <option value="{{ $b->id }}" {{ request('bicycle_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="rv-filter-field">
                <label for="rvRider">Rider</label>
                <div class="rv-filter-ctrl">
                    <select name="rider_id" id="rvRider">
                        <option value="">All Riders</option>
                        @foreach($ridersList ?? [] as $r)
                            <option value="{{ $r->id }}" {{ request('rider_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="rv-filter-actions">
                <button type="submit" class="btn-admin btn-admin--primary btn-admin--sm">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="{{ route('admin.rentals.returns') }}" class="btn-admin btn-admin--ghost btn-admin--sm" title="Clear all filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ===== Returns table ===== --}}
    <div class="rv-table-card">
        <div class="rv-table-scroll">
            <table class="rv-table" id="rvTable">
                <thead>
                    <tr>
                        <th>Rental ID</th>
                        <th>Rider</th>
                        <th>Bicycle</th>
                        <th>Returned</th>
                        <th>Duration</th>
                        <th>Final Fee</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Bicycle Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rentals as $rental)
                        @php
                            $durH = $rental->durationMinutes ? floor($rental->durationMinutes / 60) : null;
                            $durM = $rental->durationMinutes % 60;
                            $durLabel = $rental->durationMinutes ? ($durH ? $durH . 'h ' . $durM . 'm' : $durM . 'm') : null;
                        @endphp
                        <tr class="rv-row" data-detail="rvd-{{ $rental->id }}">
                            <td><span class="rv-id"><small>REN</small>#{{ $rental->id }}</span></td>
                            <td>
                                <div class="rv-rider">
                                    <div class="user-avatar" style="width:30px;height:30px;font-size:11px;">
                                        {{ strtoupper(substr($rental->rider->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div style="min-width:0;">
                                        <div class="rv-rider__name">{{ $rental->rider->name ?? 'Unknown' }}</div>
                                        <div class="rv-rider__email">{{ $rental->rider->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="rv-bike__name">{{ $rental->bicycle->name ?? 'Unknown' }}</div>
                                @if($rental->bicycle?->serialNumber)
                                    <div class="rv-bike__sub">SN &middot; {{ $rental->bicycle->serialNumber }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="rv-dt">
                                    <div class="rv-dt__date">{{ $rental->endTime?->format('M d, Y') ?? '&mdash;' }}</div>
                                    <div class="rv-dt__time">{{ $rental->endTime?->format('h:i A') ?? '&mdash;' }}</div>
                                </div>
                            </td>
                            <td>
                                @if($durLabel)
                                    <span class="rv-chip"><i class="bi bi-hourglass-split"></i>{{ $durLabel }}</span>
                                @else
                                    <span style="color:var(--text-3);">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <div class="rv-fee">&#8369;{{ number_format($rental->totalFee ?? 0, 2) }}</div>
                                <div class="rv-fee__sub">&#8369;{{ number_format($rental->ratePerHour ?? 0, 2) }}/hr</div>
                            </td>
                            <td>
                                @if($rental->paymentMethod === 'gcash')
                                    <span class="rv-method rv-method--gcash"><i class="bi bi-phone"></i>GCash</span>
                                @else
                                    <span class="rv-method rv-method--cash"><i class="bi bi-cash-stack"></i>Cash</span>
                                @endif
                            </td>
                            <td>
                                @if($rental->status === 'completed')
                                    <span class="rv-status rv-status--completed rv-status--dot">Completed</span>
                                @elseif($rental->status === 'returned')
                                    <span class="rv-status rv-status--returned rv-status--dot">Returned</span>
                                @else
                                    <span class="rv-status rv-status--cancelled rv-status--dot">{{ ucfirst($rental->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($rental->bicycle && $rental->bicycle->status === 'available')
                                    <span class="rv-chip rv-chip--avail"><i class="bi bi-check-circle"></i>Available</span>
                                @else
                                    <span class="rv-chip rv-chip--rented"><i class="bi bi-bicycle"></i>{{ $rental->bicycle->status ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="rv-actions">
                                    <button type="button" class="rv-btn rv-btn--view"
                                            data-detail-toggle="rvd-{{ $rental->id }}"
                                            title="View details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @php
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
                                            'payStatus' => ucfirst($rental->paymentStatus ?? 'Paid'),
                                            'reference' => $rental->paymentReference ?? ($rental->rentalId ?? ''),
                                            'status'    => ucfirst($rental->status),
                                            'issued'    => now()->format('M d, Y \a\t h:i A'),
                                        ];
                                    @endphp
                                    <button type="button" class="rv-btn rv-btn--print"
                                            data-print-receipt="{{ json_encode($receiptData) }}"
                                            title="Print receipt">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Expandable detail row --}}
                        <tr class="rv-detail" id="rvd-{{ $rental->id }}">
                            <td colspan="10" class="rv-detail__cell">
                                <div class="rv-detail__inner">
                                    <div class="rv-detail__content">
                                        <div class="rv-detail__card">
                                            <div class="rv-detail__head">
                                                <div class="rv-detail__title">
                                                    <i class="bi bi-arrow-return-left"></i>
                                                    Return #{{ $rental->id }}
                                                    @if($rental->status === 'completed')
                                                        <span class="rv-status rv-status--completed rv-status--dot">Completed</span>
                                                    @elseif($rental->status === 'returned')
                                                        <span class="rv-status rv-status--returned rv-status--dot">Returned</span>
                                                    @else
                                                        <span class="rv-status rv-status--cancelled rv-status--dot">{{ ucfirst($rental->status) }}</span>
                                                    @endif
                                                </div>
                                                <div class="rv-detail__meta">
                                                    <i class="bi bi-clock-history me-1"></i>Returned {{ $rental->endTime?->format('M d, Y h:i A') ?? '&mdash;' }}
                                                </div>
                                            </div>
                                            <div class="rv-detail__body">
                                                <div class="rv-detail__grid">
                                                    <div class="rv-panel">
                                                        <div class="rv-panel__label"><i class="bi bi-route"></i>Return Details</div>
                                                        <dl style="margin:0;">
                                                            <div class="rv-kv"><dt>Start Time</dt><dd>{{ $rental->startTime?->format('M d, Y h:i A') ?? '&mdash;' }}</dd></div>
                                                            <div class="rv-kv"><dt>Returned At</dt><dd>{{ $rental->endTime?->format('M d, Y h:i A') ?? '&mdash;' }}</dd></div>
                                                            <div class="rv-kv"><dt>Duration</dt><dd>{{ $durLabel ?? '&mdash;' }}</dd></div>
                                                            <div class="rv-kv"><dt>Settled</dt><dd>{{ $rental->updated_at->format('M d, Y h:i A') }}</dd></div>
                                                        </dl>
                                                        @if($rental->notes)
                                                            <div class="rv-notes"><strong><i class="bi bi-sticky me-1"></i>Notes</strong>{{ $rental->notes }}</div>
                                                        @endif
                                                    </div>

                                                    <div class="rv-panel">
                                                        <div class="rv-panel__label"><i class="bi bi-people"></i>Participants &amp; Payment</div>
                                                        <div class="rv-parties">
                                                            <div class="rv-party">
                                                                <div class="user-avatar" style="width:32px;height:32px;font-size:12px;">{{ strtoupper(substr($rental->rider->name ?? 'U', 0, 1)) }}</div>
                                                                <div style="min-width:0;">
                                                                    <div class="rv-rider__name">{{ $rental->rider->name ?? 'Unknown' }}</div>
                                                                    <div class="rv-rider__email">{{ $rental->rider->email ?? '&mdash;' }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="rv-party">
                                                                <i class="bi bi-bicycle lead"></i>
                                                                <div style="min-width:0;">
                                                                    <div class="rv-rider__name">{{ $rental->bicycle->name ?? 'Unknown' }}</div>
                                                                    <div class="rv-rider__email">{{ $rental->bicycle->status ?? '&mdash;' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="rv-fees">
                                                            <div class="rv-tile">
                                                                <div class="rv-tile__label">Hourly Rate</div>
                                                                <div class="rv-tile__value">&#8369;{{ number_format($rental->ratePerHour ?? 0, 2) }}</div>
                                                            </div>
                                                            <div class="rv-tile">
                                                                <div class="rv-tile__label">Duration</div>
                                                                <div class="rv-tile__value">{{ $durLabel ?? '&mdash;' }}</div>
                                                            </div>
                                                            <div class="rv-tile rv-tile--total">
                                                                <div class="rv-tile__label">Final Fee</div>
                                                                <div class="rv-tile__value">&#8369;{{ number_format($rental->totalFee ?? 0, 2) }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="rv-payline">
                                                            @if($rental->paymentMethod === 'gcash')
                                                                <span class="rv-method rv-method--gcash"><i class="bi bi-phone"></i>GCash</span>
                                                            @else
                                                                <span class="rv-method rv-method--cash"><i class="bi bi-cash-stack"></i>Cash</span>
                                                            @endif
                                                            @if($rental->paymentStatus === 'paid')
                                                                <span class="rv-status rv-status--paid"><i class="bi bi-check-circle-fill"></i>Paid</span>
                                                            @else
                                                                <span class="rv-status"><i class="bi bi-x-circle-fill"></i>{{ ucfirst($rental->paymentStatus ?? 'Unpaid') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="rv-empty">
                                <x-admin.empty-state icon="bi-arrow-return-left" title="No returns found" message="No returned or completed rides match your filters."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rv-foot">
            <span>
                <i class="bi bi-list-ul me-1"></i>Showing {{ $rentals->total() }} return{{ $rentals->total() === 1 ? '' : 's' }}
            </span>
            @if(method_exists($rentals, 'links'))
                {{ $rentals->withQueryString()->links() }}
            @endif
        </div>
    </div>
</div>

{{-- ===== Receipt preview modal ===== --}}
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
                        <img src="{{ asset('assets/img/Logo.png') }}" alt="Pedalya logo" class="rcpt-logo">
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
                    <span class="rcpt-chip rcpt-chip--green" id="rcptRideChip"><i class="bi bi-check-circle-fill"></i>Ride completed</span>
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
                        <div class="rcpt-kv"><dt>Returned</dt><dd id="rcptEnd">&mdash;</dd></div>
                        <div class="rcpt-kv"><dt>Duration</dt><dd id="rcptDuration">&mdash;</dd></div>
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
                        <span class="rcpt-chip rcpt-chip--green" id="rcptPayStatus">&mdash;</span>
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
            <button type="button" class="btn-admin btn-admin--secondary" data-rcpt-close>Close</button>
            <button type="button" class="btn-admin btn-admin--primary" id="rcptPrintBtn"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (sel, ctx) => (ctx || document).querySelector(sel);

    /* ---- Table search + detail toggles ---- */
    const searchInput = document.getElementById('rvSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#rvTable tbody tr.rv-row').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('[data-detail-toggle]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.detailToggle;
            const row = document.getElementById(id);
            const main = document.querySelector(`#rvTable tr[data-detail="${id}"]`);
            if (!row) return;
            const open = row.classList.toggle('open');
            this.classList.toggle('is-open', open);
            if (main) main.classList.toggle('is-open', open);
        });
    });

    /* ---- Receipt preview ---- */
    const modal = document.getElementById('rcptModal');
    const el = id => document.getElementById(id);
    const set = (id, val) => { const e = el(id); if (e) e.textContent = val; };

    function renderReceipt(d) {
        set('rcptId', d.id);
        set('rcptIssued', d.issued);
        set('rcptRiderName', d.rider);
        set('rcptRiderContact', d.email);
        set('rcptBikeName', d.bike);
        set('rcptBikeSub', 'Serial: ' + d.serial);
        set('rcptStart', d.start);
        set('rcptEnd', d.hasEnd ? d.end : 'In progress');
        set('rcptDuration', d.duration || '—');
        set('rcptRate', d.rate);
        set('rcptTotal', d.total);
        set('rcptMethod', d.method);
        set('rcptPayStatus', d.payStatus);
        el('rcptRideChip').className = 'rcpt-chip rcpt-chip--green';
        el('rcptRideChip').innerHTML = '<i class="bi bi-check-circle-fill"></i>' + d.status + ' · ' + d.id;
        el('rcptPayStatus').className = d.payStatus === 'Paid' ? 'rcpt-chip rcpt-chip--green' : 'rcpt-chip rcpt-chip--amber';
    }

    document.querySelectorAll('[data-print-receipt]').forEach(btn => {
        btn.addEventListener('click', function () {
            let data;
            try { data = JSON.parse(this.dataset.printReceipt); }
            catch (e) {
                console.error('Bad receipt JSON', e);
                return;
            }
            renderReceipt(data);
            modal.classList.add('open');
        });
    });

    function closeModal() { modal.classList.remove('open'); }
    modal.querySelectorAll('[data-rcpt-close]').forEach(b => b.addEventListener('click', closeModal));
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) closeModal(); });

    const printBtn = document.getElementById('rcptPrintBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            document.body.classList.add('rcpt-printing');
            window.print();
            setTimeout(() => document.body.classList.remove('rcpt-printing'), 200);
        });
    }
});
</script>
@endsection
