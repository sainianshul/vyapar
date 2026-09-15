<?php

namespace App\DataTables\Request;

use App\Models\CareRequest;
use Yajra\DataTables\Services\DataTable;

class RequestDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            
            // ── Request ID ─────────────────────────────────────────────
            ->editColumn('reference_id', function (CareRequest $request) {
                return '<span class="text-nowrap">' . e($request->reference_id) . '</span>';
            })
            
            // ── User Info ─────────────────────────────────────────────
            ->filterColumn('user', function($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('users.name', 'like', "%{$keyword}%")
                        ->orWhere('users.email', 'like', "%{$keyword}%")
                        ->orWhere('users.phone', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('user', function (CareRequest $request) {
                $user = $request->user;
                if (!$user) return '<span class="text-muted">Unknown</span>';

                return '<span class="text-secondary">' . e($user->name) . '</span>';
            })

            // ── Status ───────────────────────────────────────────────
            ->addColumn('status', function (CareRequest $request) {
                $color = $request->status_color;
                return '<span class="badge badge-outline text-' . $color . ' border-' . $color . ' fs-9 px-2 py-1">' . e($request->status_text) . '</span>';
            })
            
            // ── Date & Time ───────────────────────────────────────────
            ->addColumn('date_time', function (CareRequest $request) {
                $startDate = $request->start_date ? $request->start_date->format('d M Y') : '—';
                $startTime = $request->start_time ?? '';
                return '<span class="text-secondary">' . $startDate . ($startTime ? ' ' . $startTime : '') . '</span>';
            })
            
            // ── Location ───────────────────────────────────────────────
            ->addColumn('location', function (CareRequest $request) {
                return '<span class="text-secondary">' . e($request->city ?: '—') . '</span>';
            })

            // ── Created At ──────────────────────────────────────────
            ->editColumn('created_at', function (CareRequest $request) {
                return '<span class="text-secondary">' . ($request->created_at ? $request->created_at->format('d M Y') : '—') . '</span>';
            })

            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('care_requests.id', $order);
            })
            
            // ── Actions ──────────────────────────────────────────────
            ->addColumn('actions', function (CareRequest $request) {
                $viewUrl = route('admin.requests.show', $request->id); 
                
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
                            class="btn btn-sm btn-icon btn-light-primary border border-primary w-30px h-30px"
                            title="View">
                            <i class="ti ti-eye fs-5"></i>
                        </a>
                    </div>
                ';
            })

            ->rawColumns([
                'reference_id',
                'user',
                'status',
                'date_time',
                'location',
                'created_at',
                'actions',
            ]);
    }

    public function query(CareRequest $model)
    {
        $query = $model->newQuery()
            ->with('user')
            ->leftJoin('users', 'users.id', '=', 'care_requests.user_id')
            ->select([
                'care_requests.id',
                'care_requests.reference_id',
                'care_requests.user_id',
                'care_requests.status',
                'care_requests.start_date',
                'care_requests.start_time',
                'care_requests.city',
                'care_requests.created_at',
            ]);
        
        // Filter by user_id
        if (request()->filled('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter by Today Requests
        if (request('is_today') === '1') {
            $today = \Carbon\Carbon::today()->format('Y-m-d');
            $query->whereBetween('care_requests.created_at', [
                $today . ' 00:00:00',
                $today . ' 23:59:59'
            ]);
        }

        // Filter by selected date
        if (request()->filled('date')) {
            $date = request('date');
            $query->whereBetween('care_requests.created_at', [
                $date . ' 00:00:00',
                $date . ' 23:59:59'
            ]);
        }

        return $query;
    }

    public function filename(): string
    {
        return 'CareRequests_' . date('Y_m_d_His');
    }
}

