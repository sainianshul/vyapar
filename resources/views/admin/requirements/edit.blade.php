@extends('admin.layouts.app')

@section('title', 'Edit Requirement')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Requirements', 'url' => route('admin.requirements.index')],
                    ['label' => 'Edit Requirement'],
                ]" />
                <h2 class="page-title">Edit Requirement: {{ $requirement->title }}</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.requirements.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.requirements.update', $requirement->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label required">Requirement Title</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                value="{{ old('title', $requirement->title) }}" required placeholder="Enter requirement title">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                rows="5" required placeholder="Requirement description">{{ old('description', $requirement->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Target Budget</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="target_budget" class="form-control @error('target_budget') is-invalid @enderror" 
                                        value="{{ old('target_budget', $requirement->target_budget) }}" required>
                                </div>
                                @error('target_budget') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                                    value="{{ old('city', $requirement->city) }}" placeholder="City name">
                                @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="delivery_pincode" class="form-control @error('delivery_pincode') is-invalid @enderror" 
                                    value="{{ old('delivery_pincode', $requirement->delivery_pincode) }}" placeholder="Pincode">
                                @error('delivery_pincode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Requirement Images</h3>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-4 pb-3 border-bottom">
                            <label class="form-label">Primary Image</label>
                            @if($requirement->primaryImage)
                                <div class="mb-2">
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ asset('storage/' . $requirement->primaryImage->image_path) }}" alt="Primary Image" class="rounded border" style="width: 150px; height: 150px; object-fit: cover;">
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-1">Primary</span>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="primary_image" class="form-control @error('primary_image') is-invalid @enderror" accept="image/*" id="primary-image-input">
                            <div class="form-hint">Upload new image to replace the primary image. Max size 5MB.</div>
                            @error('primary_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div id="primary-image-preview" class="mt-2 d-none">
                                <img src="" class="rounded border" style="width: 150px; height: 150px; object-fit: cover;" alt="Primary Preview">
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Additional Images</label>
                            @php
                                $additionalImages = $requirement->images()->where('is_primary', false)->get();
                            @endphp
                            @if($additionalImages->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label text-muted fs-5">Currently Uploaded</label>
                                    <div class="row g-2">
                                        @foreach($additionalImages as $image)
                                            <div class="col-auto">
                                                <div class="position-relative">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="Additional Image" class="rounded border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                                    <div class="form-check position-absolute top-0 end-0 m-1 bg-white rounded shadow-sm px-1 py-0">
                                                        <input class="form-check-input m-0" style="width:1.2em; height:1.2em;" type="checkbox" name="remove_images[]" value="{{ $image->id }}" id="remove_image_{{ $image->id }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-hint mt-1 text-danger">Check the box on an image to remove it on save.</div>
                                </div>
                            @endif

                            <label class="form-label text-muted fs-5">Upload New</label>
                            <div class="dropzone" id="custom-dropzone" style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.3s ease;">
                                <div class="text-muted pointer-events-none">
                                    <i class="ti ti-upload mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Click or drag images here to upload</p>
                                    <small>Max 5MB per image</small>
                                </div>
                            </div>
                            <input type="file" name="additional_images[]" id="additional-images-input" multiple accept="image/*" class="d-none">
                            @error('additional_images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            
                            <div id="additional-images-preview" class="mt-3 row g-3"></div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label required">Buyer (User)</label>
                            <select name="user_id" class="form-select select2 @error('user_id') is-invalid @enderror" required>
                                <option value="">Select Buyer</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $requirement->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Category</label>
                            
                            <input type="hidden" name="category_id" id="final_category_id" value="{{ old('category_id', $requirement->category_id) }}" required>
                            
                            <div id="category-cascader">
                                <select class="form-select category-select mb-2" data-level="0">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat['id'] }}" {{ isset($selectedCategoryPath[0]) && $selectedCategoryPath[0] == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach (\App\Models\Requirement::getStatusList() as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $requirement->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Minimum Quantity</label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                value="{{ old('quantity', $requirement->quantity) }}" min="1" required>
                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        

                        

                        

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100" id="btn-submit">
                                <i class="ti ti-device-floppy me-1"></i> Update Requirement
                            </button>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(function() {
    // Primary Image Preview
    $('#primary-image-input').on('change', function(e) {
        let file = e.target.files[0];
        let previewContainer = $('#primary-image-preview');
        
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.find('img').attr('src', e.target.result);
                previewContainer.removeClass('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            previewContainer.addClass('d-none');
        }
    });

    // Custom Drag and Drop Multiple Image Uploader
    const dt = new DataTransfer();

    $('#custom-dropzone').on('click', function() {
        $('#additional-images-input').click();
    });

    $('#custom-dropzone').on('dragover', function(e) {
        e.preventDefault();
        $(this).css('background', '#e2e8f0');
    });

    $('#custom-dropzone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).css('background', '#f8fafc');
    });

    $('#custom-dropzone').on('drop', function(e) {
        e.preventDefault();
        $(this).css('background', '#f8fafc');
        let files = e.originalEvent.dataTransfer.files;
        handleFiles(files);
    });

    $('#additional-images-input').on('change', function(e) {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        for (let i = 0; i < files.length; i++) {
            let file = files[i];
            if (file.type.match('image.*')) {
                dt.items.add(file);
                
                let reader = new FileReader();
                reader.onload = function(e) {
                    let html = `
                        <div class="col-auto" data-name="${file.name}">
                            <div class="position-relative">
                                <img src="${e.target.result}" class="rounded border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                <button type="button" class="btn btn-icon btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle shadow-sm btn-remove-image" data-name="${file.name}" style="width: 24px; height: 24px; min-height: 24px;">
                                    <i class="ti ti-x" style="font-size: 14px;"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    $('#additional-images-preview').append(html);
                }
                reader.readAsDataURL(file);
            }
        }
        $('#additional-images-input')[0].files = dt.files;
    }

    $(document).on('click', '.btn-remove-image', function() {
        let name = $(this).data('name');
        $(this).closest('.col-auto').remove();
        
        for (let i = 0; i < dt.items.length; i++) {
            if (dt.items[i].getAsFile().name === name) {
                dt.items.remove(i);
                break;
            }
        }
        $('#additional-images-input')[0].files = dt.files;
    });

    // Cascading Categories
    let selectedCategoryPath = @json($selectedCategoryPath ?? []);
    
    function loadChildren(categoryId, currentLevel, selectedChildId = null) {
        return $.get(`/admin/categories/${categoryId}/children`, function(data) {
            if (data.length > 0) {
                let newLevel = currentLevel + 1;
                let options = '<option value="">Select Subcategory</option>';
                
                data.forEach(function(cat) {
                    let selected = (selectedChildId == cat.id) ? 'selected' : '';
                    options += `<option value="${cat.id}" ${selected}>${cat.name}</option>`;
                });
                
                let newSelect = `<select class="form-select category-select mb-2" data-level="${newLevel}">${options}</select>`;
                $('#category-cascader').append(newSelect);
            }
        });
    }

    $(document).on('change', '.category-select', function() {
        let select = $(this);
        let categoryId = select.val();
        let currentLevel = parseInt(select.data('level'));
        
        // Remove any next level selects
        select.nextAll('select.category-select').remove();
        
        // Set final category id
        let finalId = categoryId;
        if (!finalId && currentLevel > 0) {
            finalId = select.prev('select.category-select').val();
        }
        $('#final_category_id').val(finalId);
        
        if (categoryId) {
            loadChildren(categoryId, currentLevel);
        }
    });

    // Initial Load for Edit (Rebuild cascading dropdowns based on path)
    if (selectedCategoryPath.length > 1) {
        let loadSequence = Promise.resolve();
        
        for (let i = 0; i < selectedCategoryPath.length - 1; i++) {
            let parentId = selectedCategoryPath[i];
            let childId = selectedCategoryPath[i + 1];
            let level = i;
            
            loadSequence = loadSequence.then(() => {
                return loadChildren(parentId, level, childId);
            });
        }
    }
});
</script>
@endpush
