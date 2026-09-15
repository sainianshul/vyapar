<?php

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;

class CareTypeDataTable extends DataTable
{

    public function index()
    {
        return view('admin.care-types.index');
    }
}
