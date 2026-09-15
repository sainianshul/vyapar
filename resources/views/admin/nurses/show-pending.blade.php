@extends('admin.layouts.app')

@section('title', 'Nurse Profile - Pending Onboarding')

@section('content')

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'People'],
                    ['label' => 'Nurses', 'url' => route('admin.nurses.index')],
                    ['label' => $user->name],
                ]" />
                <h2 class="page-title">Pending Profile</h2>
                <div class="text-muted small mt-1">Nurse onboarding is currently incomplete</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.nurses.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-chevron-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    {{-- Profile Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    @if($user->profile_photo)
                        <span class="avatar avatar-xl rounded-circle" style="background-image: url('{{ Storage::url($user->profile_photo) }}')"></span>
                    @else
                        <span class="avatar avatar-xl rounded-circle bg-yellow-lt fw-bold fs-2">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </span>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 class="card-title mb-0 fs-2 fw-bold">{{ $user->name }}</h2>
                        <span class="badge bg-yellow-lt">
                            <i class="ti ti-clock me-1"></i> Pending Onboarding
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-4 flex-wrap text-muted small mt-2">
                        <span><i class="ti ti-phone me-1"></i>{{ $user->phone ?: '—' }}</span>
                        <span><i class="ti ti-mail me-1"></i>{{ $user->email }}</span>
                        <span><i class="ti ti-calendar me-1"></i>Joined {{ $user->created_at->format('d M Y') }}</span>
                        <span><i class="ti ti-clock me-1"></i>Last Login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('admin.nurses.edit', $user->id) }}" class="btn btn-outline-warning btn-sm">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Onboarding Progress --}}
    <div class="card mb-4">
        <div class="card-header">
            <div>
                <h3 class="card-title">Application Progress</h3>
                <div class="text-muted small mt-1">This nurse is currently completing their onboarding steps.</div>
            </div>
        </div>
        <div class="card-body py-4">
            @php
                $steps = [
                    1 => ['name' => 'Basic Profile', 'icon' => 'ti ti-user'],
                    2 => ['name' => 'Care Types', 'icon' => 'ti ti-heart'],
                    3 => ['name' => 'Education', 'icon' => 'ti ti-school'],
                    4 => ['name' => 'Work History', 'icon' => 'ti ti-briefcase'],
                    5 => ['name' => 'Documents', 'icon' => 'ti ti-files'],
                    6 => ['name' => 'Submit', 'icon' => 'ti ti-send']
                ];

                $currentStep = $profile->onboarding_step ?: 1;
                $isCompleted = $profile->is_onboarding_completed;
            @endphp

            <ul class="steps steps-blue my-4">
                @foreach($steps as $stepId => $stepData)
                    @php
                        $isPast = $isCompleted || $stepId < $currentStep;
                        $isCurrent = !$isCompleted && $stepId == $currentStep;
                        $stepClass = $isPast ? 'step-item active' : ($isCurrent ? 'step-item active' : 'step-item');
                    @endphp
                    <li class="{{ $stepClass }}">
                        <div class="h4 m-0"><i class="{{ $stepData['icon'] }} me-1"></i>{{ $stepData['name'] }}</div>
                        <div class="text-secondary small">
                            @if($isPast)
                                Completed
                            @elseif($isCurrent)
                                In Progress
                            @else
                                Pending
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="alert alert-warning d-flex align-items-center mb-0 mt-4" role="alert">
                <i class="ti ti-info-circle fs-2 me-3"></i>
                <div>
                    <h4 class="alert-title mb-1">Waiting for Nurse Action</h4>
                    <div class="text-secondary small">
                        You cannot review or approve this profile until the nurse completes all onboarding steps and submits their application for review.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <x-comments type="{{ \App\Models\Comment::TYPE_NURSE }}" :model-id="$user->id" />
    </div>

@endsection
