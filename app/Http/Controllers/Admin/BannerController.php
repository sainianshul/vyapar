<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\DataTables\Banners\BannerDataTable;

use App\Models\Category;
use App\Models\User;

class BannerController extends Controller
{
    public function index(BannerDataTable $dataTable)
    {
        $categories = Category::select('id', 'name')->get();
        $sellers = User::where('role', User::ROLE_USER)->select('id', 'name')->get();
        return $dataTable->render('admin.banners.index', compact('categories', 'sellers'));
    }

    public function data(BannerDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'required|integer',
            'reference_id' => 'nullable|integer',
            'status' => 'required|string|in:active,inactive,draft',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        Banner::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully.'
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'required|integer',
            'reference_id' => 'nullable|integer',
            'status' => 'required|string|in:active,inactive,draft',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully.'
        ]);
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, Banner $banner)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,draft',
        ]);

        $banner->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }
}
