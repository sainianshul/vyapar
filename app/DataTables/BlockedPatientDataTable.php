<?php

namespace App\DataTables;

use App\Models\User;
use Yajra\DataTables\Services\DataTable;

class BlockedPatientDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── User Info ─────────────────────────────────────────────
            ->addColumn('name', function (User $user) {
                return '
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">' . $user->avatar_html . '</span>
                        <div>
                            <div class="fw-semibold">' . e($user->name) . '</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('email', function (User $user) {
                return '<span class="text-secondary">' . e($user->email ?? '—') . '</span>';
            })
            ->addColumn('phone', function (User $user) {
                return '<span class="text-secondary">' . e($user->phone ?? '—') . '</span>';
            })

            // ── Status ───────────────────────────────────────────────
            ->addColumn('status', function (User $user) {
                $colorMap = [
                    'success' => 'green',
                    'danger' => 'red',
                    'secondary' => 'secondary',
                ];
                $color = $colorMap[$user->status_color] ?? 'secondary';

                return '
                    <span class="badge bg-' . $color . '-lt text-' . $color . '">
                        ' . e($user->status_name) . '
                    </span>
                ';
            })

            // ── Blocked Reason ───────────────────────────────────────────
            ->addColumn('blocked_reason', function (User $user) {
                return $user->blocked_reason ? e($user->blocked_reason) : '<span class="text-secondary">N/A</span>';
            })

            // ── Blocked At ───────────────────────────────────────────
            ->addColumn('blocked_at', function (User $user) {
                $blockedAt = $user->blocked_at ?? null;

                if (!$blockedAt) {
                    return '<span class="text-secondary">N/A</span>';
                }

                if (!($blockedAt instanceof \Carbon\Carbon)) {
                    $blockedAt = \Carbon\Carbon::parse($blockedAt);
                }

                return '<div class="text-secondary">' . $blockedAt->format('d M Y') . '</div>';
            })

            // ── Actions ──────────────────────────────────────────────
            ->addColumn('actions', function (User $user) {

                $viewUrl = route('admin.patients.show', $user->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">

                        <button type="button"
                            class="btn btn-sm btn-outline-success btn-unblock d-inline-flex align-items-center"
                            data-id="' . $user->id . '"
                            data-bs-toggle="tooltip" title="Unblock">
                            <i class="ti ti-lock-open me-1"></i>Unblock
                        </button>

                        <a href="' . $viewUrl . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View">
                            <i class="ti ti-eye"></i>
                        </a>

                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $user->id . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>

                    </div>
                ';
            })

            ->rawColumns([
                'name',
                'email',
                'phone',
                'status',
                'blocked_reason',
                'blocked_at',
                'actions',
            ]);
    }

    public function query(User $model)
    {
        // Only return blocked users
        $query = $model->newQuery()
            ->where('role', User::ROLE_USER)
            ->where('status', User::STATUS_BLOCKED)
            ->select('users.*');

        return $query;
    }

    public function filename(): string
    {
        return 'Blocked_Users_' . date('Y_m_d_His');
    }
}
