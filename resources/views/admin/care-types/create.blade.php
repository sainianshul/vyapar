@extends('admin.layouts.app')

@section('title', 'Add Care Type')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Services'],
                    ['label' => 'Care Types', 'url' => route('admin.services.care-types.index')],
                    ['label' => 'Add New'],
                ]" />
                <h2 class="page-title">Add Care Type</h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                <a href="{{ route('admin.services.care-types.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <x-form-errors />

            <form method="POST" action="{{ route('admin.services.care-types.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    {{-- Name --}}
                    <div class="col-md-6">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Elderly Care" autofocus required />
                    </div>

                    {{-- Image --}}
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail Image</label>
                        <input type="file" name="image" class="form-control" accept=".png,.jpg,.jpeg,.webp" />
                        <div class="form-hint">Square image (PNG, JPG) max 2MB.</div>
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Brief description of this care service">{{ old('description') }}</textarea>
                    </div>

                    {{-- Commission Type --}}
                    <div class="col-md-4">
                        <label class="form-label required">Commission Type</label>
                        <select name="commision_type" class="form-select @error('commision_type') is-invalid @enderror" required>
                            <option value="1" {{ old('commision_type', '1') == '1' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="0" {{ old('commision_type') == '0' ? 'selected' : '' }}>Fixed per day (₹)</option>
                            <option value="2" {{ old('commision_type') == '2' ? 'selected' : '' }}>Flat Fee (₹)</option>
                        </select>
                    </div>

                    {{-- Commission Value --}}
                    <div class="col-md-4">
                        <label class="form-label required">Commission Value</label>
                        <input type="number" name="commision_value" class="form-control @error('commision_value') is-invalid @enderror" min="0" step="0.01" value="{{ old('commision_value', 0) }}" required />
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="{{ \App\Models\CareType::STATUS_ACTIVE }}">Active</option>
                            <option value="{{ \App\Models\CareType::STATUS_INACTIVE }}">Inactive</option>
                            <option value="{{ \App\Models\CareType::STATUS_DRAFT }}">Draft</option>
                        </select>
                    </div>
                </div>

                <div class="form-footer text-end mt-4 pt-4 border-top">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

@endsection