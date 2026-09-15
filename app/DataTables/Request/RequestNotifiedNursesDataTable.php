<?php

namespace App\DataTables\Request;

use App\Models\NurseRequestCache;
use Yajra\DataTables\Services\DataTable;

class RequestNotifiedNursesDataTable extends DataTable
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
            ->addColumn('nurse', function (NurseRequestCache $cache) {
                $user = $cache->nurse->user ?? null;
                $name = $user->name ?? 'Unknown';
                $avatarHtml = $user ? $user->avatar_html : '<span class="symbol-label bg-light-dark text-dark fw-bold fs-7">UN</span>';
                $avatar = '<div class="symbol symbol-30px symbol-circle me-3">' . $avatarHtml . '</div>';

                return '
                    <div class="d-flex align-items-center">
                        ' . $avatar . '
                        <div class="d-flex flex-column">
                            <span class="text-gray-900 fw-bold fs-7">' . e($name) . '</span>
                            <span class="text-gray-500 fs-8">ID: ' . $cache->nurse_id . '</span>
                        </div>
                    </div>
                ';
            })
            ->addColumn('distance', function (NurseRequestCache $cache) {
                $snapshot = is_string($cache->request_snapshot) ? json_decode($cache->request_snapshot, true) : ($cache->request_snapshot ?? []);
                $dist = $snapshot['distance_to_patient'] ?? null;
                return $dist !== null ? '<span class="text-gray-900 fw-semibold fs-7">' . number_format($dist, 1) . ' km</span>' : '<span class="text-muted fs-8">N/A</span>';
            })
            ->addColumn('status', function (NurseRequestCache $cache) {
                $color = $cache->status_color;
                
                $statusTexts = [
                    NurseRequestCache::STATUS_NOTIFIED => 'Notified',
                    NurseRequestCache::STATUS_VIEWED => 'Viewed',
                    NurseRequestCache::STATUS_BID_PLACED => 'Bid Placed',
                    NurseRequestCache::STATUS_EXPIRED => 'Expired',
                ];
                $text = $statusTexts[$cache->status] ?? 'Unknown';
                
                return '<span class="badge badge-light-' . $color . ' text-' . $color . ' fw-bold fs-8 px-2 py-1">' . $text . '</span>';
            })
            ->editColumn('created_at', function (NurseRequestCache $cache) {
                return '<span class="text-gray-900 fw-semibold fs-7">' . $cache->created_at->format('d M, h:i A') . '</span>';
            })
            ->addColumn('actions', function (NurseRequestCache $cache) {
                $viewUrl = route('admin.nurses.show', $cache->nurse->user_id ?? 0);
                return '
                    <div class="d-flex justify-content-end">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-icon btn-light border border-gray-300 w-25px h-25px" title="View Nurse">
                            <i class="ti ti-eye fs-7 text-gray-700"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['nurse', 'distance', 'status', 'created_at', 'actions']);
    }

    public function query(NurseRequestCache $model)
    {
        return $model->newQuery()
            ->with(['nurse.user'])
            ->where('care_request_id', $this->requestId);
    }
}
