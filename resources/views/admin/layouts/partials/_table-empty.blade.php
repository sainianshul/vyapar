{{--
Reusable empty-state partial.
Usage: @include('admin.layouts.partials._table-empty', ['id' => 'patients-empty'])
The $id variable MUST match the ID you target in JS: $('#patients-empty').removeClass('d-none')
--}}
@php $emptyId = $id ?? 'table-empty'; @endphp
<div id="{{ $emptyId }}" class="d-none">
    <div class="empty py-5">
        <div class="empty-icon">
            <i class="ti ti-database-off" style="font-size: 2.5rem; color: var(--tblr-secondary);"></i>
        </div>
        <p class="empty-title">No records found</p>
        <p class="empty-subtitle text-secondary">
            Try adjusting your search or filters
        </p>
    </div>
</div>