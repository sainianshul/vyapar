@extends('admin.layouts.app')
@section('title', 'Add Category')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['url' => route('admin.categories.index'), 'label' => 'Categories'],
                    ['label' => 'Add Category'],
                ]" />
                <h2 class="page-title">Add Category</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">

            {{-- Left Column — Main Info --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-8">
                                <label class="form-label required">Category Name</label>
                                <input type="text" name="name" id="category-name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="e.g. Electronics" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="category-slug"
                                    class="form-control @error('slug') is-invalid @enderror"
                                    value="{{ old('slug') }}" placeholder="auto-generated">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-hint">Leave blank to auto-generate.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Parent Category</label>
                                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                    <option value="">— None (Root Category) —</option>
                                    @foreach($parentCategories as $cat)
                                        <option value="{{ $cat['id'] }}" {{ old('parent_id') == $cat['id'] ? 'selected' : '' }}>
                                            {{ $cat['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order"
                                    class="form-control @error('sort_order') is-invalid @enderror"
                                    value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <label class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="form-check-label">Active</span>
                                </label>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="3" placeholder="Optional description...">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column — Images --}}
            <div class="col-lg-4">

                {{-- Icon Upload --}}
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Icon</h3></div>
                    <div class="card-body">
                        <div class="text-center mb-2">
                            <img id="icon-preview" src="" alt="Icon Preview" class="rounded border d-none" style="max-width:80px; max-height:80px; object-fit:cover;">
                        </div>
                        <input type="file" name="icon" id="icon-input" accept="image/*"
                            class="form-control @error('icon') is-invalid @enderror">
                        @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-hint">PNG, JPG, SVG, WebP. Max 512KB.</small>
                    </div>
                </div>

                {{-- Image Upload --}}
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Image</h3></div>
                    <div class="card-body">
                        <div class="text-center mb-2">
                            <img id="image-preview" src="" alt="Image Preview" class="rounded border d-none" style="max-width:100%; max-height:150px; object-fit:cover;">
                        </div>
                        <input type="file" name="image" id="image-input" accept="image/*"
                            class="form-control @error('image') is-invalid @enderror">
                        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-hint">PNG, JPG, WebP. Max 2MB.</small>
                    </div>
                </div>

            </div>

        </div>

        {{-- Submit --}}
        <div class="row mt-2">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-footer text-end">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Save Category
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
$(function () {
    // Auto-slug from name
    $('#category-name').on('input', function () {
        let slug = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        $('#category-slug').val(slug);
    });

    // Image preview helpers
    function previewFile(inputId, previewId) {
        $('#' + inputId).on('change', function () {
            let file = this.files[0];
            let preview = $('#' + previewId);
            if (file) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    preview.attr('src', e.target.result).removeClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                preview.addClass('d-none').attr('src', '');
            }
        });
    }

    previewFile('icon-input', 'icon-preview');
    previewFile('image-input', 'image-preview');
});
</script>
@endpush
