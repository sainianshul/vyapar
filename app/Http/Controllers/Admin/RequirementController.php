<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Requirement\StoreRequirementRequest;
use App\Http\Requests\Admin\Requirement\UpdateRequirementRequest;
use App\Models\Category;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequirementController extends Controller
{
    public function index(\App\DataTables\Requirements\RequirementDataTable $dataTable)
    {
        $categories = Category::root()->orderBy('name')->get();
        return $dataTable->render('admin.requirements.index', compact('categories'));
    }

    public function data(\App\DataTables\Requirements\RequirementDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create()
    {
        $categories = Category::root()->orderBy('name')->get();
        $users = User::users()->active()->orderBy('name')->get();
        return view('admin.requirements.create', compact('categories', 'users'));
    }

    public function store(StoreRequirementRequest $request, \App\Services\RequirementService $requirementService)
    {
        $data = $request->validated();
                                $buyer = User::findOrFail($data['user_id']);
        
        $requirementService->createRequirement(
            $data, 
            $buyer, 
            $request->file('primary_image'), 
            $request->file('additional_images')
        );

        return redirect()->route('admin.requirements.index')
            ->with('success', 'Requirement created successfully.');
    }

    public function show(Requirement $requirement)
    {
        $requirement->load(['user', 'category', 'images' => function ($q) {
            $q->orderBy('is_primary', 'desc')->orderBy('sort_order');
        }]);

        return view('admin.requirements.show', compact('requirement'));
    }

    public function edit(Requirement $requirement)
    {
        $categories = Category::root()->orderBy('name')->get();
        $users = User::users()->active()->orderBy('name')->get();
        
        $selectedCategoryPath = [];
        if ($requirement->category) {
            $path = $requirement->category->ancestors->pluck('id')->toArray();
            $path[] = $requirement->category_id; // add self
            $selectedCategoryPath = $path;
        }

        return view('admin.requirements.edit', compact('requirement', 'categories', 'users', 'selectedCategoryPath'));
    }

    public function update(UpdateRequirementRequest $request, Requirement $requirement)
    {
        $data = $request->safe()->except(['primary_image', 'additional_images', 'remove_images']);
                        
        if ($requirement->title !== $data['title']) {
            $data['slug'] = Str::slug($data['title']);
        }

        $requirement->update($data);

        // Handle image removal
        if ($request->filled('remove_images')) {
            $imagesToRemove = $requirement->images()->whereIn('id', $request->remove_images)->get();
            foreach ($imagesToRemove as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
            
            // Check if primary was removed, set another one as primary
            if (!$requirement->primaryImage && $requirement->images()->exists()) {
                $firstImage = $requirement->images()->first();
                $firstImage->update(['is_primary' => true]);
            }
        }

        $currentMaxSort = $requirement->images()->max('sort_order') ?? -1;

        // Handle new primary image upload (replaces old primary)
        if ($request->hasFile('primary_image')) {
            if ($requirement->primaryImage) {
                Storage::disk('public')->delete($requirement->primaryImage->image_path);
                $requirement->primaryImage->delete();
            }
            $path = $request->file('primary_image')->store('requirements/images', 'public');
            $requirement->images()->create([
                'image_path' => $path,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }

        // Handle new additional images
        if ($request->hasFile('additional_images')) {
            $hasPrimary = $requirement->primaryImage()->exists();
            
            foreach ($request->file('additional_images') as $index => $image) {
                $path = $image->store('requirements/images', 'public');
                $requirement->images()->create([
                    'image_path' => $path,
                    'sort_order' => $currentMaxSort + $index + 1,
                    'is_primary' => (!$hasPrimary && $index === 0 && !$request->hasFile('primary_image')),
                ]);
            }
        }

        return redirect()->route('admin.requirements.index')
            ->with('success', 'Requirement updated successfully.');
    }

    public function destroy(Requirement $requirement)
    {
        $requirement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Requirement deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, Requirement $requirement)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,3,4',
        ]);

        $requirement->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }

    
}
