@extends('admin.layouts.app')
@section('title', 'Categories')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Categories'],
                ]" />
                <h2 class="page-title">Categories</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Category
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1">#</th>
                        <th>Category</th>
                        <th>Parent</th>
                        <th>Sort</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="text-secondary">{{ $category->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($category->icon)
                                    <img src="{{ Storage::url($category->icon) }}" alt="" class="rounded" width="32" height="32" style="object-fit:cover;">
                                @else
                                    <span class="avatar avatar-sm rounded bg-primary-lt">
                                        <i class="ti ti-category"></i>
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-semibold">
                                        @if($category->level > 0)
                                            <span class="text-muted">{{ str_repeat('— ', $category->level) }}</span>
                                        @endif
                                        {{ $category->name }}
                                    </div>
                                    <div class="text-secondary small">{{ $category->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($category->parent)
                                <span class="badge bg-blue-lt">{{ $category->parent->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-secondary">{{ $category->sort_order }}</td>
                        <td class="text-secondary">{{ $category->product_count }}</td>
                        <td>
                            <label class="form-check form-switch mb-0">
                                <input class="form-check-input toggle-status" type="checkbox"
                                    data-id="{{ $category->id }}"
                                    {{ $category->is_active ? 'checked' : '' }}>
                            </label>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-icon btn-ghost-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-icon btn-ghost-danger btn-sm btn-delete"
                                    data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                    data-bs-toggle="tooltip" title="Delete">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="empty">
                                <div class="empty-icon"><i class="ti ti-category" style="font-size: 2rem;"></i></div>
                                <p class="empty-title">No categories yet</p>
                                <p class="empty-subtitle text-secondary">Create your first category to get started.</p>
                                <div class="empty-action">
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                                        <i class="ti ti-plus me-1"></i>Add Category
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="card-footer d-flex align-items-center">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
$(function () {

    // Toggle Status (AJAX)
    $(document).on('change', '.toggle-status', function () {
        let checkbox = $(this);
        let id = checkbox.data('id');

        $.ajax({
            url: '/admin/categories/' + id + '/toggle-status',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                showToast(res.message, 'success');
            },
            error: function () {
                // Revert checkbox on failure
                checkbox.prop('checked', !checkbox.prop('checked'));
                showToast('Failed to update status', 'error');
            }
        });
    });

    // Delete with SweetAlert
    $(document).on('click', '.btn-delete', function () {
        let id = $(this).data('id');
        let name = $(this).data('name');
        $(this).tooltip('hide');

        Swal.fire({
            title: 'Delete "' + name + '"?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
            buttonsStyling: false,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '/admin/categories/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    showToast(res.message, 'success');
                    setTimeout(() => location.reload(), 800);
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'Something went wrong';
                    showToast(msg, 'error');
                }
            });
        });
    });

});
</script>
@endpush
