<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\System\CommunicationLogDataTable;
use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use Illuminate\Http\Request;

class CommunicationLogController extends Controller
{
    /**
     * Display a listing of the logs.
     */
    public function index(CommunicationLogDataTable $dataTable)
    {
        return $dataTable->render('admin.communication_logs.index');
    }

    /**
     * Remove the specified log from storage.
     */
    public function destroy($id)
    {
        $log = CommunicationLog::findOrFail($id);
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log deleted successfully.'
        ]);
    }

    /**
     * Empty (truncate) all logs from the database.
     */
    public function truncate()
    {
        CommunicationLog::truncate();

        return redirect()->route('admin.communication-logs.index')
            ->with('success', 'All communication logs have been successfully cleared.');
    }
}
