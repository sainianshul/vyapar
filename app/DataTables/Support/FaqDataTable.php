<?php

namespace App\DataTables\Support;

use App\Models\Faq;
use Yajra\DataTables\Services\DataTable;

class FaqDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('question', function (Faq $faq) {
                return '<div class="fw-bold text-gray-800">' . e($faq->question) . '</div>';
            })
            ->addColumn('category', function (Faq $faq) {
                return '<span class="badge badge-light-primary border border-primary fw-bold px-2 py-1"><i class="ti ti-eye fs-8 text-primary me-1"></i> ' . e($faq->supportCategory->name ?? 'N/A') . '</span>';
            })
            ->addColumn('status', function (Faq $faq) {
                return '
                    <span class="badge badge-light-' . $faq->status_color . ' border border-' . $faq->status_color . ' fw-bold px-3 py-2">
                        <i class="ki-outline ' . $faq->status_icon . ' fs-6 text-' . $faq->status_color . ' me-1"></i>
                        ' . e($faq->status_name) . '
                    </span>
                ';
            })
            ->editColumn('created_at', function (Faq $faq) {
                return '
                    <div class="fw-semibold text-gray-800">' . ($faq->created_at ? $faq->created_at->format('d M Y') : 'N/A') . '</div>
                    <div class="text-muted fs-7">' . ($faq->created_at ? $faq->created_at->format('h:i A') : '') . '</div>
                ';
            })
            ->addColumn('actions', function (Faq $faq) {
                $editUrl = route('admin.support.faqs.edit', $faq->id);
                $deleteUrl = route('admin.support.faqs.destroy', $faq->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $editUrl . '"
                            class="btn btn-sm btn-icon btn-light-warning border border-warning w-30px h-30px"
                            title="Edit">
                            <i class="ti ti-eye fs-5"></i>
                        </a>
                        <button type="button"
                            class="btn btn-sm btn-icon btn-light-danger border border-danger w-30px h-30px"
                            onclick="confirmDelete(\'' . $deleteUrl . '\')"
                            title="Delete">
                            <i class="ti ti-eye fs-5"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['question', 'category', 'status', 'created_at', 'actions']);
    }

    public function query(Faq $model)
    {
        $query = $model->newQuery()->with('supportCategory');

        // Filter by category
        if (request()->filled('support_category_id')) {
            $query->where('support_category_id', request('support_category_id'));
        }

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Faqs_' . date('Y_m_d_His');
    }
}
