<?php

namespace App\DataTables;

use App\Models\User;
use Yajra\DataTables\Services\DataTable;

class PatientDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── User Info ─────────────────────────────────────────────
            ->filterColumn('name', function($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('name', function (User $user) {
                return '
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">' . $user->avatar_html . '</span>
                        <div>
                            <div class="fw-semibold">' . e($user->name) . '</div>
                        </div>
                    </div>
                ';
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

                $statuses = User::getStatusList();
                $options = '';
                foreach ($statuses as $val => $label) {
                    $active = ($val == $user->status) ? ' active' : '';
                    $options .= '<a class="dropdown-item status-change-item' . $active . '" href="#" data-id="' . $user->id . '" data-status="' . $val . '">' . e($label) . '</a>';
                }

                return '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-' . $color . ' dropdown-toggle" data-bs-toggle="dropdown">
                            ' . e($user->status_name) . '
                        </button>
                        <div class="dropdown-menu">
                            ' . $options . '
                        </div>
                    </div>
                ';
            })

            // ── Last Login ───────────────────────────────────────────
            ->addColumn('last_login_at', function (User $user) {

                if (!$user->last_login_at) {
                    return '<span class="text-secondary">Never</span>';
                }

                return '<div class="text-secondary">' . $user->last_login_at->format('d M Y') . '</div>';
            })

            // ── Joined Date ──────────────────────────────────────────
            ->editColumn('created_at', function (User $user) {

                return '<div class="text-secondary">' . $user->created_at->format('d M Y') . '</div>';
            })

            // ── Actions ──────────────────────────────────────────────
            ->addColumn('actions', function (User $user) {

                $viewUrl = route('admin.patients.show', $user->id);
                $editUrl = route('admin.patients.edit', $user->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View">
                            <i class="ti ti-eye"></i>
                        </a>

                        <a href="' . $editUrl . '"
                            class="btn btn-icon btn-sm btn-outline-warning"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
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
                'phone',
                'status',
                'last_login_at',
                'created_at',
                'actions',
            ]);
    }

    public function query(User $model)
    {
        $query = $model->newQuery()
            ->where('role', User::ROLE_USER)
            ->select('users.*');

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Users_' . date('Y_m_d_His');
    }
}