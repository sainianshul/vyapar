<?php

namespace App\DataTables\Nurses;

use App\Models\User;
use App\Models\NurseProfile;

class AllNursesDataTable extends BaseNursesDataTable
{
    // No profileStatuses filter — sab aayenge

    public function dataTable($query)
    {
        $dt = datatables()->eloquent($query);

        $dt = $this->nurseColumn($dt);
        $dt = $this->locationColumn($dt);
        $dt = $this->joinedColumn($dt);
        $dt = $this->phoneColumn($dt);
        $dt = $this->profileStatusColumn($dt);
        $dt = $this->actionsColumn($dt);
        $dt = $this->filterByName($dt);

        return $dt->rawColumns(['nurse', 'location', 'phone', 'profile_status', 'created_at', 'actions']);
    }

    public function query(\App\Models\User $model)
    {
        $query = parent::query($model);

        // Status dropdown filter — sirf All page pe
        if (request()->filled('profile_status')) {
            $query->whereHas('nurseProfile', function ($q) {
                $q->where('status', request('profile_status'));
            });
        }

        return $query;
    }
}
