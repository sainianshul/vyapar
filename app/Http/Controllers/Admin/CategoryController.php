<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        // Get all categories with their parent
        $categories = Category::with('parent')->orderBy('sort_order')->latest()->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->orWhere('level', '<', 2)->get();
        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = Str::slug($request->name);

        // determine level
        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
            $data['level'] = $parent->level + 1;
        } else {
            $data['level'] = 0;
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('level', '<', 2);
            })->get();

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        if ($request->name !== $category->name) {
            $data['slug'] = Str::slug($request->name);
        }

        // determine level
        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
            $data['level'] = $parent->level + 1;
        } else {
            $data['level'] = 0;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            return back()->with('error', 'Cannot delete category because it has subcategories.');
        }

        if ($category->product_count > 0) {
            return back()->with('error', 'Cannot delete category because it has products.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
