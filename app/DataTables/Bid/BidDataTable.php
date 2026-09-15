<?php

namespace App\DataTables\Bid;

use App\Models\RequestBid;
use Yajra\DataTables\Services\DataTable;

class BidDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── ID ───────────────────────────────────────────────────
            ->addColumn('id', function (RequestBid $bid) {
                return '<span class="fw-bold text-gray-800">#' . $bid->id . '</span>';
            })

            // ── Care Request Info ────────────────────────────────────
            ->addColumn('care_request', function (RequestBid $bid) {
                $careRequest = $bid->careRequest;
                if (!$careRequest)
                    return '<span class="text-muted">Unknown</span>';

                $title = $careRequest->reference_id ?? 'Request #' . $careRequest->id;
                $url = route('admin.requests.show', $careRequest->id);

                return '<a href="' . $url . '" class="text-body text-decoration-none fw-medium">' . e($title) . '</a>';
            })

            // ── Nurse Info ───────────────────────────────────────────
            ->addColumn('nurse', function (RequestBid $bid) {
                $nurse = $bid->nurse;
                if (!$nurse || !$nurse->user)
                    return '<span class="text-muted">Unknown</span>';

                $user = $nurse->user;
                $url = route('admin.nurses.show', $user->id);

                return '<a href="' . $url . '" class="text-body text-decoration-none">' . e($user->name) . '</a>';
            })

            // ── Amount ──────────────────────────────────────────────
            ->addColumn('amount', function (RequestBid $bid) {
                return '<span class="text-secondary">₹' . number_format($bid->total_amount, 2) . '</span>';
            })

            // ── Status ──────────────────────────────────────────────
            ->addColumn('status', function (RequestBid $bid) {
                $color = $bid->status_color;
                return '<span class="badge badge-outline text-' . $color . ' border-' . $color . ' fs-9 px-2 py-1">' . e($bid->status_text) . '</span>';
            })

            // ── Created At ──────────────────────────────────────────
            ->editColumn('created_at', function (RequestBid $bid) {
                return '<span class="text-secondary">' . ($bid->created_at ? $bid->created_at->format('d M Y') : '—') . '</span>';
            })

            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('request_bids.id', $order);
            })

            // ── Actions ─────────────────────────────────────────────
            ->addColumn('actions', function (RequestBid $bid) {
                $viewUrl = route('admin.bids.show', $bid->id);

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
                'id',
                'care_request',
                'nurse',
                'amount',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(RequestBid $model)
    {
        $query = $model->newQuery()->with(['careRequest', 'nurse.user'])->select('request_bids.*');

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter by date
        if (request()->filled('date')) {
            $date = request('date');
            $query->whereBetween('request_bids.created_at', [
                $date . ' 00:00:00',
                $date . ' 23:59:59'
            ]);
        }

        // Filter today
        if (request()->filled('today') && request('today') == true) {
            $today = today()->format('Y-m-d');
            $query->whereBetween('request_bids.created_at', [
                $today . ' 00:00:00',
                $today . ' 23:59:59'
            ]);
        }

        return $query;
    }
}
