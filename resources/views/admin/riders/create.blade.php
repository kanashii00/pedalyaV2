@extends('layouts.admin')

@section('title', 'Register Customer — Pedalya Admin')

@section('page-header')
    <h1>Register Customer</h1>
@endsection

@section('actions')
    <a href="{{ route('admin.riders.index') }}" class="btn-admin btn-admin--secondary btn-admin--sm">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <form method="POST" action="{{ route('admin.riders.store') }}" class="admin-form" novalidate>
            @csrf

            {{-- ── Customer Information ────────────────────────────── --}}
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
                               class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name"
                               value="{{ old('name') }}"
                               required autofocus
                               placeholder="e.g. Juan Dela Cruz">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ── Contact Information ─────────────────────────────── --}}
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
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email') }}"
                                   required
                                   placeholder="e.g. juan@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phoneNumber">
                                Phone Number
                            </label>
                            <input type="text"
                                   class="form-control @error('phoneNumber') is-invalid @enderror"
                                   id="phoneNumber" name="phoneNumber"
                                   value="{{ old('phoneNumber') }}"
                                   placeholder="e.g. 09XX XXX XXXX">
                            @error('phoneNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="address">
                                Address
                            </label>
                            <input type="text"
                                   class="form-control @error('address') is-invalid @enderror"
                                   id="address" name="address"
                                   value="{{ old('address') }}"
                                   placeholder="e.g. Azuela Cove, Davao City">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Account Security ────────────────────────────────── --}}
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
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password"
                                   required
                                   placeholder="Min 8 characters">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

            {{-- ── Submit ──────────────────────────────────────────── --}}
            <div class="d-flex align-items-center justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.riders.index') }}" class="btn-admin btn-admin--secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-admin btn-admin--primary btn-admin--lg">
                    <i class="bi bi-person-plus me-1"></i>Register Customer
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
