<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Support\TicketDataTable;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    protected $supportService;

    public function __construct(SupportService $supportService)
    {
        $this->supportService = $supportService;
    }

    /**
     * Display a listing of tickets.
     */
    public function index(TicketDataTable $dataTable)
    {
        return $dataTable->render('admin.support.index');
    }

    /**
     * Get pending tickets count
     */
    public function pendingCount()
    {
        $count = SupportTicket::where('status', SupportTicket::STATUS_PENDING)->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Show ticket details and chat interface.
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages.user'])->findOrFail($id);

        // Auto assign to me if it is pending
        if ($ticket->status === SupportTicket::STATUS_PENDING) {
            $this->supportService->updateStatus($ticket, SupportTicket::STATUS_OPEN, Auth::user());
            $ticket->assigned_to = Auth::id();
            $ticket->save();
        }

        return view('admin.support.show', compact('ticket'));
    }

    /**
     * Reply to a ticket as admin.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $ticket = SupportTicket::findOrFail($id);

        try {
            $this->supportService->addMessage(
                $ticket,
                Auth::user(),
                $request->message,
                $request->file('attachments') ?? [],
                true // isAdmin = true
            );

            return redirect()->back()->with('success', 'Reply sent successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3,4,5'
        ]);

        $ticket = SupportTicket::findOrFail($id);

        try {
            $this->supportService->updateStatus($ticket, (int) $request->status, Auth::user());
            return redirect()->back()->with('success', 'Ticket status updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
