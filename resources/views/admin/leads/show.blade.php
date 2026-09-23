@extends('admin.layouts.app')

@section('title', 'Lead Details')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Leads', 'url' => 'javascript:history.back()'],
                    ['label' => 'Lead #' . $lead->id],
                ]" />
                <h2 class="page-title">Lead #{{ $lead->id }}</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="datagrid">
                {{-- Product/Requirement --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">Lead For</div>
                    <div class="datagrid-content">
                        @if($lead->product)
                            <span class="badge bg-secondary-lt mb-1">Product</span><br>
                            <a href="{{ route('admin.products.show', $lead->product_id) }}" class="text-reset fw-medium">
                                {{ $lead->product->title }}
                            </a>
                        @elseif($lead->requirement)
                            <span class="badge bg-secondary-lt mb-1">Requirement</span><br>
                            <a href="{{ route('admin.requirements.show', $lead->requirement_id) }}" class="text-reset fw-medium">
                                {{ $lead->requirement->title }}
                            </a>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">Status</div>
                    <div class="datagrid-content">
                        @php
                            $colors = [
                                \App\Models\Lead::STATUS_NEW => 'blue',
                                \App\Models\Lead::STATUS_CONTACTED => 'orange',
                                \App\Models\Lead::STATUS_CONVERTED => 'green',
                                \App\Models\Lead::STATUS_REJECTED => 'red',
                            ];
                            $color = $colors[$lead->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}-lt">{{ $lead->status_name }}</span>
                    </div>
                </div>

                {{-- Date --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">Created At</div>
                    <div class="datagrid-content">{{ $lead->created_at ? $lead->created_at->format('d M Y, h:i A') : 'N/A' }}</div>
                </div>

                {{-- From --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">From (Buyer)</div>
                    <div class="datagrid-content">
                        @if($lead->buyer)
                            <a href="{{ route('admin.users.show', $lead->buyer->id) }}" class="text-reset fw-medium">{{ $lead->buyer->name }}</a><br>
                            <span class="text-secondary">{{ $lead->buyer->phone ?? 'No Phone' }}</span>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                </div>

                {{-- To --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">To (Seller)</div>
                    <div class="datagrid-content">
                        @if($lead->seller)
                            <a href="{{ route('admin.users.show', $lead->seller->id) }}" class="text-reset fw-medium">{{ $lead->seller->name }}</a><br>
                            <span class="text-secondary">{{ $lead->seller->phone ?? 'No Phone' }}</span>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                </div>

                {{-- Source --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">Source</div>
                    <div class="datagrid-content">{{ $lead->source_name }}</div>
                </div>

                {{-- Temperature --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">Temperature</div>
                    <div class="datagrid-content">{{ $lead->temperature_name }}</div>
                </div>

                {{-- Quantity --}}
                <div class="datagrid-item">
                    <div class="datagrid-title">Quantity Required</div>
                    <div class="datagrid-content">{{ $lead->quantity ?? 'N/A' }}</div>
                </div>
            </div>

            {{-- Message --}}
            @if($lead->message)
                <div class="mt-4">
                    <div class="datagrid-title mb-2">Message</div>
                    <div class="p-3 bg-light rounded text-secondary">
                        {{ $lead->message }}
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
