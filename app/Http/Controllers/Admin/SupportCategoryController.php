<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportCategory;
use Illuminate\Http\Request;

class SupportCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = SupportCategory::orderBy('name')->get();
        return view('admin.support.categories', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:support_categories,name',
            'status' => 'required|boolean',
        ]);

        SupportCategory::create($validated);

        return redirect()->route('admin.support.categories.index')->with('success', 'Support category created successfully.');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = SupportCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:support_categories,name,' . $category->id,
            'status' => 'required|boolean',
        ]);

        $category->update($validated);

        return redirect()->route('admin.support.categories.index')->with('success', 'Support category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = SupportCategory::findOrFail($id);
        
        // We do not physically prevent deletion if they are used to keep it simple, 
        // but maybe we should just delete it. The user just wants a simple CRUD.
        $category->delete();

        return redirect()->route('admin.support.categories.index')->with('success', 'Support category deleted successfully.');
    }
}
