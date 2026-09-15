<?php

namespace App\DataTables;

use App\Models\LoginHistory;
use App\Models\User;
use Yajra\DataTables\Services\DataTable;

class LoginHistoryDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── Name ───────────────────────────────────────────────
            ->addColumn('user_name', function (LoginHistory $history) {
                $user = $history->user;
                $name = trim((string) ($user?->name));
                
                if ($name === '') {
                    $name = 'Unknown User';
                }

                return e($name);
            })

            // ── Type (Role) ────────────────────────────────────────
            ->addColumn('user_type', function (LoginHistory $history) {
                $user = $history->user;
                $role = (int) ($user?->role);
                
                $roleBadge = 'Guest';

                if ($role === User::ROLE_ADMIN) {
                    $roleBadge = 'Admin';
                } elseif ($role === User::ROLE_USER) {
                    $roleBadge = 'Patient';
                } elseif ($role === User::ROLE_NURSE) {
                    $roleBadge = 'Nurse';
                }

                return $roleBadge;
            })

            // ── Phone ───────────────────────────────────────────────
            ->addColumn('user_phone', function (LoginHistory $history) {
                $user = $history->user;
                $phone = trim((string) ($user?->phone));
                
                if ($phone === '') {
                    return '<span class="text-muted small">No phone</span>';
                }

                return '<span class="text-body">' . e($phone) . '</span>';
            })

            // ── IP Address ─────────────────────────────────────────
            ->addColumn('ip', function (LoginHistory $history) {
                $ip = trim((string) $history->ip_address);
                if ($ip === '') {
                    $ip = '—';
                }
                return e($ip);
            })

            // ── Status ─────────────────────────────────────────────
            ->addColumn('status_badge', function (LoginHistory $history) {
                return (int) $history->status === 1
                    ? '<span class="badge badge-outline text-green border-green fs-9 px-2 py-1">Success</span>'
                    : '<span class="badge badge-outline text-red border-red fs-9 px-2 py-1">Failed</span>';
            })

            // ── Login Time ─────────────────────────────────────────
            ->editColumn('logged_in_at', function (LoginHistory $history) {
                if (!$history->logged_in_at) {
                    return '<span class="text-muted">—</span>';
                }
                return $history->logged_in_at->format('d M Y, h:i A');
            })

            // ── Search By User Name or Phone ──────────────────────────
            ->filterColumn('user', function ($query, $keyword) {

                $query->whereHas('user', function ($q) use ($keyword) {

                    $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })

            // ── Actions ───────────────────────────────────────────
            ->addColumn('actions', function (LoginHistory $history) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . route('admin.login-history.show', $history->id) . '"
                            class="btn btn-sm btn-icon btn-light-primary border border-primary w-30px h-30px"
                            data-bs-toggle="tooltip" title="View">
                            <i class="ti ti-eye fs-5"></i>
                        </a>
                    </div>
                ';
            })

            ->rawColumns([
                'user_name',
                'user_type',
                'user_phone',
                'ip',
                'status_badge',
                'logged_in_at',
                'actions',
            ]);
    }

    public function query(LoginHistory $model)
    {
        $query = $model->newQuery()
            ->with('user')
            ->select('login_histories.*');

        // ── Search By User Name or Phone ─────────────────────────────
        if ($search = request('search.value')) {

            $query->whereHas('user', function ($q) use ($search) {

                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // ── Status Filter ────────────────────────────────────────
        if (request()->filled('status')) {

            $query->where('status', request('status'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Login_History_' . date('Y_m_d_His');
    }
}