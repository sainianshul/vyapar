<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CareTypeRequest;
use App\Models\CareType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CareTypeController extends Controller
{
    public function index()
    {
        $careTypes = CareType::orderBy('id', 'desc')->get();

        return view('admin.care-types.index', compact('careTypes'));
    }

    public function create()
    {
        return view('admin.care-types.create');
    }

    public function store(CareTypeRequest $request)
    {
        $status = $request->has('status') ? $request->status : ($request->action === 'publish' ? CareType::STATUS_ACTIVE : CareType::STATUS_INACTIVE);
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('care-types', 'public');
        }

        CareType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'commision_type' => $request->commision_type,
            'commision_value' => $request->commision_value,
            'image_path' => $imagePath,
            'status' => $status,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.services.care-types.index')
            ->with('success', 'Care Type created successfully.');
    }

    public function show(CareType $careType)
    {
        return view('admin.care-types.show', compact('careType'));
    }

    public function edit(CareType $careType)
    {
        return view('admin.care-types.edit', compact('careType'));
    }

    public function update(CareTypeRequest $request, CareType $careType)
    {
        if ($request->has('status')) {
            $status = $request->status;
        } else {
            $status = $request->action === 'publish' ? CareType::STATUS_ACTIVE : CareType::STATUS_INACTIVE;
        }

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'commision_type' => $request->commision_type,
            'commision_value' => $request->commision_value,
            'status' => $status,
        ];

        if ($request->hasFile('image')) {
            if ($careType->image_path) {
                Storage::disk('public')->delete($careType->image_path);
            }
            $data['image_path'] = $request->file('image')->store('care-types', 'public');
        }

        if ($request->filled('remove_image') && $careType->image_path) {
            Storage::disk('public')->delete($careType->image_path);
            $data['image_path'] = null;
        }

        $careType->update($data);

        return redirect()
            ->route('admin.services.care-types.index')
            ->with('success', 'Care Type updated successfully.');
    }

    public function destroy(CareType $careType)
    {
        $careType->delete();

        return response()->json(['success' => true, 'message' => 'Care Type removed successfully.']);
    }
}