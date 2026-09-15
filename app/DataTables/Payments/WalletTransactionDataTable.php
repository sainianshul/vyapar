<?php

namespace App\DataTables\Payments;

use App\Models\WalletTransaction;
use Yajra\DataTables\Services\DataTable;

class WalletTransactionDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            
            // ── ID ────────────────────────────────────────────────────────
            ->editColumn('id', function (WalletTransaction $transaction) {
                return '<span class="fw-bold text-gray-800">#' . $transaction->id . '</span>';
            })
            
            // ── Reference ID ──────────────────────────────────────────────
            ->editColumn('reference_id', function (WalletTransaction $transaction) {
                return '<span class="text-gray-600 fs-8 font-monospace">' . e($transaction->reference_id ?? '—') . '</span>';
            })
            
            // ── User / Wallet ─────────────────────────────────────────────
            ->filterColumn('user', function ($query, $keyword) {
                $query->whereHas('wallet.user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('user', function (WalletTransaction $transaction) {
                $user = $transaction->wallet->user ?? null;
                if (!$user) {
                    return '<span class="text-muted">Unknown</span>';
                }
                
                $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
                
                return '
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px symbol-circle me-3">
                            <span class="symbol-label bg-light-primary text-primary fw-semibold fs-5">' . $initial . '</span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-semibold fs-6 lh-1 mb-1">' . e($user->name) . '</span>
                            <span class="text-muted fw-normal fs-7">ID: ' . e($user->id) . '</span>
                        </div>
                    </div>
                ';
            })
            
            // ── Amount & Type ─────────────────────────────────────────────
            ->addColumn('amount', function (WalletTransaction $transaction) {
                $color = $transaction->type == WalletTransaction::TYPE_CREDIT ? 'success' : 'danger';
                $sign = $transaction->type == WalletTransaction::TYPE_CREDIT ? '+' : '-';
                
                return '
                    <div class="fw-bold text-' . $color . ' fs-6">' . $sign . '₹' . number_format($transaction->amount, 2) . '</div>
                    <div class="text-muted fs-8">Bal: ₹' . number_format($transaction->balance_after, 2) . '</div>
                ';
            })
            
            // ── Reason ────────────────────────────────────────────────────
            ->addColumn('reason', function (WalletTransaction $transaction) {
                return '
                    <div class="fw-semibold text-gray-800">' . e($transaction->reason_text) . '</div>
                    <div class="text-muted fs-8 text-truncate" style="max-width: 200px;" title="' . e($transaction->description) . '">' . e($transaction->description) . '</div>
                ';
            })
            
            // ── Related Booking ───────────────────────────────────────────
            ->addColumn('booking', function (WalletTransaction $transaction) {
                if (!$transaction->booking_id) {
                    return '<span class="text-muted">N/A</span>';
                }
                
                $url = route('admin.bookings.show', $transaction->booking_id);
                return '
                    <a href="' . $url . '" class="text-primary fw-semibold text-hover-primary">
                        Booking #' . $transaction->booking_id . '
                    </a>
                ';
            })
            
            // ── Date ──────────────────────────────────────────────────────
            ->editColumn('created_at', function (WalletTransaction $transaction) {
                return '
                    <div class="fw-semibold text-gray-800">' . $transaction->created_at->format('d M Y') . '</div>
                    <div class="text-muted fs-7">' . $transaction->created_at->format('h:i A') . '</div>
                ';
            })
            
            ->rawColumns([
                'id',
                'reference_id',
                'user',
                'amount',
                'reason',
                'booking',
                'created_at',
            ]);
    }

    public function query(WalletTransaction $model)
    {
        $query = $model->newQuery()
            ->with(['wallet.user'])
            ->select('wallet_transactions.*');

        if (request()->filled('type')) {
            $query->where('type', request('type'));
        }

        if (request()->filled('reason')) {
            $query->where('reason', request('reason'));
        }

        if (request()->filled('date')) {
            $query->whereDate('wallet_transactions.created_at', request('date'));
        }

        return $query;
    }
}
