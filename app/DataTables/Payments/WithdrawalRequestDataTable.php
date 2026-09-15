<?php

namespace App\DataTables\Payments;

use App\Models\WithdrawalRequest;
use Yajra\DataTables\Services\DataTable;

class WithdrawalRequestDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            
            // ── ID ────────────────────────────────────────────────────────
            ->editColumn('id', function (WithdrawalRequest $payout) {
                return '<span class="fw-bold text-gray-800">#' . $payout->id . '</span>';
            })
            
            // ── Nurse ─────────────────────────────────────────────────────
            ->filterColumn('user', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('user', function (WithdrawalRequest $payout) {
                $user = $payout->user;
                if (!$user) {
                    return '<span class="text-muted">Unknown</span>';
                }
                
                $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
                $url = route('admin.nurses.show', $user->id); // Assuming withdrawal is always nurse here
                
                return '
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px symbol-circle me-3">
                            <span class="symbol-label bg-light-info text-info fw-semibold fs-5">' . $initial . '</span>
                        </div>
                        <div class="d-flex flex-column">
                            <a href="' . $url . '" class="text-gray-800 fw-semibold fs-6 lh-1 mb-1 text-hover-primary">' . e($user->name) . '</a>
                            <span class="text-muted fw-normal fs-7">ID: ' . e($user->id) . '</span>
                        </div>
                    </div>
                ';
            })
            
            // ── Amount ────────────────────────────────────────────────────
            ->addColumn('amount', function (WithdrawalRequest $payout) {
                return '<div class="fw-bold text-gray-900 fs-6">₹' . number_format($payout->amount, 2) . '</div>';
            })
            
            // ── Bank Details ──────────────────────────────────────────────
            ->addColumn('bank_details', function (WithdrawalRequest $payout) {
                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-semibold fs-7">' . e($payout->bank_account_name) . '</span>
                        <span class="text-muted fs-8 font-monospace">' . e($payout->masked_account) . ' • ' . e($payout->bank_ifsc) . '</span>
                    </div>
                ';
            })
            
            // ── Status ────────────────────────────────────────────────────
            ->editColumn('status', function (WithdrawalRequest $payout) {
                $colors = [
                    WithdrawalRequest::STATUS_PENDING => 'warning',
                    WithdrawalRequest::STATUS_PROCESSING => 'primary',
                    WithdrawalRequest::STATUS_COMPLETED => 'success',
                    WithdrawalRequest::STATUS_FAILED => 'danger',
                    WithdrawalRequest::STATUS_REJECTED => 'dark',
                ];
                
                $color = $colors[$payout->status] ?? 'secondary';
                
                return '<span class="badge badge-light-' . $color . ' border border-' . $color . ' fw-bold px-3 py-2">' . e($payout->status_text) . '</span>';
            })
            
            // ── Processed Info ────────────────────────────────────────────
            ->addColumn('processed_info', function (WithdrawalRequest $payout) {
                if ($payout->isPending()) {
                    return '<span class="text-muted fs-8">Waiting for action</span>';
                }
                
                $admin = $payout->processedBy ? $payout->processedBy->name : 'System';
                $date = $payout->processed_at ? $payout->processed_at->format('d M Y') : 'Unknown';
                
                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-semibold fs-7">By: ' . e($admin) . '</span>
                        <span class="text-muted fs-8">On: ' . $date . '</span>
                    </div>
                ';
            })
            
            // ── Created At ────────────────────────────────────────────────
            ->editColumn('created_at', function (WithdrawalRequest $payout) {
                return '
                    <div class="fw-semibold text-gray-800">' . $payout->created_at->format('d M Y') . '</div>
                    <div class="text-muted fs-7">' . $payout->created_at->diffForHumans() . '</div>
                ';
            })
            
            ->rawColumns([
                'id',
                'user',
                'amount',
                'bank_details',
                'status',
                'processed_info',
                'created_at',
            ]);
    }

    public function query(WithdrawalRequest $model)
    {
        $query = $model->newQuery()
            ->with(['user', 'processedBy'])
            ->select('withdrawal_requests.*');

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('date')) {
            $query->whereDate('created_at', request('date'));
        }

        return $query;
    }
}
