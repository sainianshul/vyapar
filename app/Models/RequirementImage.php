<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementImage extends Model
{
    protected $fillable = [
        'requirement_id',
        'image_path',
        'sort_order',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'url' => asset('storage/' . $this->image_path),
            'sort_order' => $this->sort_order,
        ];
    }}
