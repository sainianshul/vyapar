@extends('admin.layouts.app')

@section('title', 'Nurse Profile - Rejected')

@section('content')

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Nurses', 'url' => route('admin.nurses.index')],
                    ['label' => $user->name],
                    ['label' => 'Rejected Profile'],
                ]" />
                <h2 class="page-title">Rejected Profile</h2>
                <div class="text-muted small mt-1">This nurse's application was declined</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.nurses.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-chevron-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div>
                <h3 class="card-title">Application Rejected</h3>
                <div class="text-muted small mt-1">This nurse's application was declined by the administrator.</div>
            </div>
            <div class="card-actions">
                <a href="{{ route('admin.nurses.edit', $user->id) }}" class="btn btn-outline-warning btn-sm">
                    <i class="ti ti-edit me-1"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-4">
                        @if($user->profile_photo)
                            <span class="avatar avatar-lg rounded-circle me-3" style="background-image: url('{{ Storage::url($user->profile_photo) }}')"></span>
                        @else
                            <span class="avatar avatar-lg rounded-circle bg-red-lt fw-bold fs-3 me-3">
                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                            </span>
                        @endif
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $user->name }}</h3>
                            <div class="text-muted small">ID: #{{ $user->id }}</div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Email</span>
                            <span class="fw-semibold">{{ $user->email }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Phone</span>
                            <span class="fw-semibold">{{ $user->phone ?: '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Joined</span>
                            <span class="fw-semibold">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <h4 class="fw-bold mb-3">Rejection Details</h4>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="ti ti-circle-x fs-2 me-3"></i>
                        <div>
                            <h4 class="alert-title mb-1">Reason for Rejection</h4>
                            <div class="text-secondary small">
                                {{ $profile->rejection_reason ?: 'No specific reason provided by the administrator.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <x-comments type="{{ \App\Models\Comment::TYPE_NURSE }}" :model-id="$user->id" />
    </div>

@endsection
