@extends('admin.layouts.app')

@section('title', 'Add Patient')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Patients', 'url' => route('admin.patients.index')],
                    ['label' => 'Add Patient'],
                ]" />
                <h2 class="page-title">Add Patient</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <x-form-errors />

    <form method="POST" action="{{ route('admin.patients.store') }}">
        @csrf

        <div class="row">

            {{-- Main Form --}}
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Patient Information</h3>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label required">Full Name</label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="Enter full name" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Phone Number</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="text" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" placeholder="Enter phone number"
                                    required pattern="[0-9+]*" minlength="10" maxlength="15"
                                    oninput="this.value = this.value.replace(/[^0-9+]/g, '')" />
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="Enter email address" />
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Account Status</h3>
                    </div>
                    <div class="card-body">
                        <select name="status" class="form-select">
                            @foreach (\App\Models\User::getStatusList() as $value => $label)
                                <option value="{{ $value }}" {{ old('status', \App\Models\User::STATUS_ACTIVE) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-check me-1"></i>Create Patient
                </button>
            </div>

        </div>
    </form>

@endsection
