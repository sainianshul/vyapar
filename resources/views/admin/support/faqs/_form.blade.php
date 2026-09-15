<div class="row mb-6">
    <label class="col-lg-4 col-form-label required fw-semibold ">Category</label>
    <div class="col-lg-8">
        <select name="support_category_id" class="form-select form-select" data-placeholder="Select a category" required>
            <option value=""></option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('support_category_id', $faq->support_category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('support_category_id') <div class="text-danger mt-2">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label required fw-semibold ">Question</label>
    <div class="col-lg-8">
        <input type="text" name="question" class="form-control form-control" placeholder="Enter question" value="{{ old('question', $faq->question ?? '') }}" required />
        @error('question') <div class="text-danger mt-2">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label required fw-semibold ">Answer</label>
    <div class="col-lg-8">
        <textarea name="answer" class="form-control form-control" rows="4" placeholder="Enter answer" required>{{ old('answer', $faq->answer ?? '') }}</textarea>
        @error('answer') <div class="text-danger mt-2">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label required fw-semibold ">Status</label>
    <div class="col-lg-8">
        <select name="status" class="form-select form-select" required>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" {{ old('status', $faq->status ?? \App\Models\Faq::STATUS_DRAFT) == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status') <div class="text-danger mt-2">{{ $message }}</div> @enderror
    </div>
</div>
