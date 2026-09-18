<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use App\DataTables\Feedbacks\FeedbackDataTable;

class FeedbackController extends Controller
{
    public function index(FeedbackDataTable $dataTable)
    {
        return $dataTable->render('admin.feedbacks.index');
    }

    public function data(FeedbackDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'feedback' => 'required|string',
            'status' => 'required|string|in:active,inactive,draft',
        ]);

        Feedback::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Feedback created successfully.'
        ]);
    }

    public function update(Request $request, Feedback $feedback)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'feedback' => 'required|string',
            'status' => 'required|string|in:active,inactive,draft',
        ]);

        $feedback->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Feedback updated successfully.'
        ]);
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, Feedback $feedback)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,draft',
        ]);

        $feedback->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }
}
