@extends('admin.layouts.app')
@section('title', 'Categories')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Categories</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Category
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Parent Category</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Products</th>
                    <th class="w-1">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="text-secondary">{{ $category->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $category->name }}</div>
                        <div class="text-secondary small">{{ $category->slug }}</div>
                    </td>
                    <td>
                        @if($category->parent)
                            <span class="badge bg-blue-lt">{{ $category->parent->name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $category->sort_order }}</td>
                    <td>
                        @if($category->is_active)
                            <span class="badge bg-success-lt">Active</span>
                        @else
                            <span class="badge bg-danger-lt">Inactive</span>
                        @endif
                    </td>
                    <td class="text-secondary">{{ $category->product_count }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-icon btn-outline-warning btn-sm" title="Edit">
                                <i class="ti ti-pencil"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-outline-danger btn-sm" title="Delete">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No categories found.</td>
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
