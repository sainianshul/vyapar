<?php

namespace App\DataTables\Nurses;

use App\Models\User;

class DeletedNurseDataTable extends BaseNursesDataTable
{
    public function dataTable($query)
    {
        $dt = datatables()->eloquent($query);

        $dt = $this->nurseColumn($dt);
        $dt = $this->locationColumn($dt);

        $dt = $this->joinedColumn($dt);
        $dt = $this->phoneColumn($dt);

        $dt = $this->profileStatusColumn($dt);

        // Overriding actions for Restore
        $dt->addColumn('actions', function (User $user) {
            return '
                <div class="d-flex gap-1 justify-content-end">
                    <button type="button"
                        class="btn btn-sm btn-light-success border border-success btn-restore fw-bold"
                        data-id="' . $user->id . '"
                        title="Restore Nurse">
                        <i class="ti ti-eye fs-5 me-1"></i> Restore
                    </button>
                </div>
            ';
        });
        
        // Deleted At Column
        $dt->editColumn('deleted_at', function (User $user) {
            return '
                <div class="fw-semibold text-gray-800">' . $user->deleted_at->format('d M Y') . '</div>
                <div class="text-muted fs-7">' . $user->deleted_at->diffForHumans() . '</div>
            ';
        });

        $dt = $this->filterByName($dt);

        return $dt->rawColumns(['nurse', 'location', 'created_at', 'phone', 'profile_status', 'deleted_at', 'actions']);
    }

    public function query(\App\Models\User $model)
    {
        $query = $model->newQuery()
            ->onlyTrashed()
            ->with('nurseProfile')
            ->where('users.role', 2)
            ->select('users.*');

        // Name search
        if ($search = request('search.value')) {
            $query->where('users.name', 'LIKE', "%{$search}%");
        }

        return $query;
    }
}
