<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(\App\DataTables\Products\ProductDataTable $dataTable)
    {
        $categories = Category::root()->orderBy('name')->get();
        return $dataTable->render('admin.products.index', compact('categories'));
    }

    public function data(\App\DataTables\Products\ProductDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create()
    {
        $categories = Category::root()->orderBy('name')->get();
        $users = User::users()->active()->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'users'));
    }

    public function store(StoreProductRequest $request, \App\Services\ProductService $productService)
    {
        $data = $request->validated();
        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_verified'] = $request->boolean('is_verified');
        $seller = User::findOrFail($data['user_id']);
        
        $productService->createProduct(
            $data, 
            $seller, 
            $request->file('primary_image'), 
            $request->file('additional_images')
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product, \App\DataTables\Products\ProductViewDataTable $viewDataTable, \App\DataTables\Products\ProductLeadDataTable $leadDataTable)
    {
        $product->load(['seller', 'category', 'images' => function ($q) {
            $q->orderBy('is_primary', 'desc')->orderBy('sort_order');
        }]);

        $viewTable = $viewDataTable->with('product_id', $product->id)->html();
        $leadTable = $leadDataTable->with('product_id', $product->id)->html();

        return view('admin.products.show', compact('product', 'viewTable', 'leadTable'));
    }

    public function viewsData(Product $product, \App\DataTables\Products\ProductViewDataTable $dataTable)
    {
        return $dataTable->with('product_id', $product->id)->ajax();
    }

    public function leadsData(Product $product, \App\DataTables\Products\ProductLeadDataTable $dataTable)
    {
        return $dataTable->with('product_id', $product->id)->ajax();
    }

    public function edit(Product $product)
    {
        $categories = Category::root()->orderBy('name')->get();
        $users = User::users()->active()->orderBy('name')->get();
        
        $selectedCategoryPath = [];
        if ($product->category) {
            $path = $product->category->ancestors->pluck('id')->toArray();
            $path[] = $product->category_id; // add self
            $selectedCategoryPath = $path;
        }

        return view('admin.products.edit', compact('product', 'categories', 'users', 'selectedCategoryPath'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->safe()->except(['primary_image', 'additional_images', 'remove_images']);
        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_verified'] = $request->boolean('is_verified');

        if ($product->title !== $data['title']) {
            $data['slug'] = Str::slug($data['title']);
        }

        $product->update($data);

        // Handle image removal
        if ($request->filled('remove_images')) {
            $imagesToRemove = $product->images()->whereIn('id', $request->remove_images)->get();
            foreach ($imagesToRemove as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
            
            // Check if primary was removed, set another one as primary
            if (!$product->primaryImage && $product->images()->exists()) {
                $firstImage = $product->images()->first();
                $firstImage->update(['is_primary' => true]);
            }
        }

        $currentMaxSort = $product->images()->max('sort_order') ?? -1;

        // Handle new primary image upload (replaces old primary)
        if ($request->hasFile('primary_image')) {
            if ($product->primaryImage) {
                Storage::disk('public')->delete($product->primaryImage->image_path);
                $product->primaryImage->delete();
            }
            $path = $request->file('primary_image')->store('products/images', 'public');
            $product->images()->create([
                'image_path' => $path,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }

        // Handle new additional images
        if ($request->hasFile('additional_images')) {
            $hasPrimary = $product->primaryImage()->exists();
            
            foreach ($request->file('additional_images') as $index => $image) {
                $path = $image->store('products/images', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => $currentMaxSort + $index + 1,
                    'is_primary' => (!$hasPrimary && $index === 0 && !$request->hasFile('primary_image')),
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,3,4',
        ]);

        $product->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }

    public function updateFeatured(Request $request, Product $product)
    {
        $request->validate([
            'is_featured' => 'required|boolean',
        ]);

        $product->update([
            'is_featured' => $request->is_featured,
            'featured_at' => $request->is_featured ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Featured status updated successfully.'
        ]);
    }

}
