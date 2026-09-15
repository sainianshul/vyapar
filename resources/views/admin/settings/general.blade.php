@extends('admin.layouts.app')

@section('title', 'General Settings')
@section('page_title', 'General Settings')

@section('content')
    <div  class="page-header d-print-none py-3 py-lg-6">
        <div  class="container-xl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <x-page-header title="General Settings" description="Manage core application rules and configuration" />
                <x-breadcrumb :items="[
                    ['label' => 'Settings'],
                    ['label' => 'General Setting', 'url' => route('admin.settings.general')],
                ]" />
            </div>
            <a href="{{ route('admin.dashboard') }}"
                class="btn btn-sm btn btn-outline-dark fw-semibold">
                <i class="ti ti-chevron-left h4 me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">General Application Settings</h3>
        </div>
        
        <form action="{{ route('admin.settings.general.update') }}" method="POST">
            @csrf
            
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                        <i class="ti ti-shield-check fs-1 text-success me-4"></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-success">Success</h4>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                <div class="row mb-8">
                    <div class="col-md-6 mb-3 mb-5">
                        <label class="required fw-semibold mb-2">Minimum Withdrawal Amount (₹)</label>
                        <input type="number" class="form-control" name="min_withdrawal_amount" value="{{ old('min_withdrawal_amount', setting('min_withdrawal_amount', config('care.min_withdrawal_amount', 100))) }}" required />
                        <div class="text-muted small mt-2">Minimum amount a nurse can withdraw from their wallet.</div>
                        @error('min_withdrawal_amount')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3 mb-5">
                        <label class="required fw-semibold mb-2">Nurse Cancel Strike Limit</label>
                        <input type="number" class="form-control" name="nurse_cancel_strike_limit" value="{{ old('nurse_cancel_strike_limit', setting('nurse_cancel_strike_limit', config('care.nurse_cancel_strike_limit', 3))) }}" required />
                        <div class="text-muted small mt-2">How many bookings a nurse can cancel before they are suspended.</div>
                        @error('nurse_cancel_strike_limit')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-8">
                    <div class="col-md-6 mb-3 mb-5">
                        <label class="required fw-semibold mb-2">Max Booking Advance Days</label>
                        <input type="number" class="form-control" name="max_booking_advance_days" value="{{ old('max_booking_advance_days', setting('max_booking_advance_days', config('care.max_booking_advance_days', 4))) }}" required />
                        <div class="text-muted small mt-2">Maximum days in advance a patient can schedule a booking.</div>
                        @error('max_booking_advance_days')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3 mb-5">
                        <label class="required fw-semibold mb-2">Min Booking Notice Hours</label>
                        <input type="number" class="form-control" name="min_booking_notice_hours" value="{{ old('min_booking_notice_hours', setting('min_booking_notice_hours', config('care.min_booking_notice_hours', 6))) }}" required />
                        <div class="text-muted small mt-2">Minimum hours from now before a new booking can start.</div>
                        @error('min_booking_notice_hours')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
            </div>
            
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="submit" class="btn btn-primary" >
                    <span class="indicator-label">Save Changes</span>
                </button>
            </div>
        </form>
    </div>
@endsection
