<?php

namespace App\DataTables\Request;

use App\Models\RequestBid;
use Yajra\DataTables\Services\DataTable;

class RequestBidsDataTable extends DataTable
{
    protected $requestId;

    public function withRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('nurse', function($query, $keyword) {
                $query->whereHas('nurse.user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('nurse', function (RequestBid $bid) {
                $user = $bid->nurse->user;
                $name = $user->name ?? 'Unknown';
                $userId = $user->id ?? 0;
                $avatar = '<div class="symbol symbol-30px symbol-circle me-3">' . ($user->avatar_html ?? '<span class="symbol-label bg-light-info text-info fw-bold">N</span>') . '</div>';

                $distanceHtml = '';
                if ($bid->distance_km) {
                    $distanceHtml = '<div class="mt-1 d-flex align-items-center text-gray-700 fs-8"><i class="ti ti-eye fs-8 me-1 text-gray-500"></i>' . $bid->distance_km . ' km away</div>';
                }

                return '
                    <div class="d-flex align-items-start">
                        ' . $avatar . '
                        <div class="d-flex flex-column">
                            <a href="' . route('admin.nurses.show', $userId) . '" class="text-gray-900 text-hover-primary fw-bold fs-7">' . e($name) . '</a>
                            <span class="text-gray-500 fs-8">ID: ' . $bid->nurse_id . '</span>
                            ' . $distanceHtml . '
                        </div>
                    </div>
                ';
            })
            ->editColumn('nurse_amount', function (RequestBid $bid) {
                return '<span class="text-gray-900 fw-bold fs-7">₹' . number_format($bid->nurse_amount, 2) . '</span>';
            })
            ->editColumn('commission_amount', function (RequestBid $bid) {
                return '<span class="text-gray-900 fw-semibold fs-7">₹' . number_format($bid->commission_amount, 2) . '</span>';
            })
            ->editColumn('total_amount', function (RequestBid $bid) {
                return '<span class="text-gray-900 fw-bold fs-7">₹' . number_format($bid->total_amount, 2) . '</span>';
            })
            ->addColumn('status', function (RequestBid $bid) {
                $color = $bid->status_color;
                return '<span class="badge badge-light-' . $color . ' text-' . $color . ' fw-bold fs-8 px-2 py-1 border border-' . $color . '">' . ($bid->status_text ?? 'Unknown') . '</span>';
            })
            ->addColumn('actions', function (RequestBid $bid) {
                $viewUrl = route('admin.bids.show', $bid->id);
                return '
                    <div class="d-flex justify-content-end">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-icon btn-light-primary border border-primary w-25px h-25px me-1" title="View Bid">
                            <i class="ti ti-eye fs-7 text-primary"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['nurse', 'nurse_amount', 'commission_amount', 'total_amount', 'status', 'actions']);
    }

    public function query(RequestBid $model)
    {
        return $model->newQuery()
            ->with(['nurse.user'])
            ->where('care_request_id', $this->requestId);
    }
}
