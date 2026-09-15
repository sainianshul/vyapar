<?php
$request = request();
$query = App\Models\CareType::query();

$sortField = $request->input('sort_field', 'id');
$sortDirection = $request->input('sort_direction', 'desc');
$query->orderBy($sortField, $sortDirection);

$careTypes = $query->paginate($request->input('per_page', 15))
    ->through(function ($careType) {
        return [
            'id' => $careType->id,
            'name' => $careType->name,
            'slug' => $careType->slug,
            'description' => $careType->description,
            'commision_type' => $careType->commision_type,
            'commision_value' => $careType->commision_value,
            'commission_text' => $careType->commission_text,
            'status' => $careType->status,
            'status_text' => $careType->status_text,
            'image_path' => $careType->image_path ? asset('storage/' . $careType->image_path) : null,
            'created_at' => clone $careType->created_at,
        ];
    })
    ->withQueryString();

echo "Success\n";
