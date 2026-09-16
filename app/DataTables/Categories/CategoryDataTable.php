<?php

namespace App\DataTables\Categories;

use App\Models\Category;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('slug', 'like', "%{$keyword}%");
            })
            ->editColumn('name', function (Category $category) {
                $prefix = $category->level > 0 ? str_repeat('— ', $category->level) : '';
                return '
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">' . $category->icon_html . '</span>
                        <div>
                            <div class="fw-semibold">
                                <span class="text-muted">' . e($prefix) . '</span>
                                ' . e($category->name) . '
                            </div>
                            <div class="text-secondary small">' . e($category->slug) . '</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('parent', function (Category $category) {
                if ($category->parent) {
                    return '<span class="badge bg-blue-lt">' . e($category->parent->name) . '</span>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->editColumn('sort_order', function (Category $category) {
                return '<span class="text-secondary">' . $category->sort_order . '</span>';
            })
            ->editColumn('product_count', function (Category $category) {
                return '<span class="text-secondary">' . $category->product_count . '</span>';
            })
            ->addColumn('status', function (Category $category) {
                $color = $category->status_color;
                return '
                    <a href="javascript:void(0)" class="badge bg-' . $color . '-lt status-modal-btn text-decoration-none" data-id="' . $category->id . '" data-status="' . $category->is_active . '">
                        <i class="' . $category->status_icon . ' me-1"></i>' . e($category->status_name) . '
                    </a>
                ';
            })
            ->addColumn('actions', function (Category $category) {
                $editUrl = route('admin.categories.edit', $category->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $editUrl . '"
                            class="btn btn-icon btn-sm btn-outline-warning"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </a>

                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $category->id . '" data-name="' . e($category->name) . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'name',
                'parent',
                'sort_order',
                'product_count',
                'status',
                'actions',
            ]);
    }

    public function query(Category $model)
    {
        $query = $model->newQuery()->with('parent');

        if (request()->filled('status')) {
            $query->where('is_active', request('status'));
        }

        // Default order by sort_order
        $query->orderBy('sort_order');

        return $query;
    }

    public function filename(): string
    {
        return 'Categories_' . date('Y_m_d_His');
    }
}
