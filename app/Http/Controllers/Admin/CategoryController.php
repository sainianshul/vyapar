<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(\App\DataTables\Categories\CategoryDataTable $dataTable)
    {
        return $dataTable->render('admin.categories.index');
    }

    public function data(\App\DataTables\Categories\CategoryDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create()
    {
        $parentCategories = $this->getParentOptions();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;


        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories/images', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $parentCategories = $this->getParentOptions($category->id);
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');


        // Handle image removal
        if ($request->boolean('remove_image') && $category->image) {
            Storage::disk('public')->delete($category->image);
            $data['image'] = null;
        }

        // Handle image upload (new file replaces old)
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories/images', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: this category has subcategories. Delete them first.'
            ], 422);
        }

        if ($category->product_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: this category has products linked to it.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, Category $category)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $category->update(['is_active' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }

    public function updateFeatured(Request $request, Category $category)
    {
        $request->validate([
            'is_featured' => 'required|boolean',
        ]);

        $category->update(['is_featured' => $request->is_featured]);

        return response()->json([
            'success' => true,
            'message' => 'Featured status updated successfully.'
        ]);
    }

    /**
     * Get categories for parent dropdown with indentation.
     * Excludes the given category (and its children) to prevent circular refs.
     */
    private function getParentOptions(?int $excludeId = null): array
    {
        $categories = Category::root()
            ->with('children.children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($categories as $cat) {
            if ($cat->id === $excludeId) continue;
            $options[] = ['id' => $cat->id, 'name' => $cat->name, 'level' => 0];
            $this->addChildOptions($options, $cat, 1, $excludeId);
        }

        return $options;
    }

    private function addChildOptions(array &$options, Category $parent, int $depth, ?int $excludeId): void
    {
        foreach ($parent->children as $child) {
            if ($child->id === $excludeId) continue;
            $prefix = str_repeat('— ', $depth);
            $options[] = ['id' => $child->id, 'name' => $prefix . $child->name, 'level' => $depth];
            if ($child->children->count() > 0 && $depth < 3) {
                $this->addChildOptions($options, $child, $depth + 1, $excludeId);
            }
        }
    }
}
