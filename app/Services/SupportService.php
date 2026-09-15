<?php

namespace App\Services;

use App\Exceptions\Support\InvalidCategoryException;
use App\Exceptions\Support\TicketClosedException;
use App\Exceptions\Support\TicketNotFoundException;
use App\Exceptions\Support\UnauthorizedTicketAccessException;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupportService
{
    // ─── Ticket CRUD ─────────────────────────────────────────────

    /**
     * Create a new support ticket with an initial message.
     *
     * @throws InvalidCategoryException
     */
    public function createTicket(User $user, array $data): SupportTicket
    {
        $this->validateCategory($data['category'] ?? '');

        return DB::transaction(function () use ($user, $data) {
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'category' => $data['category'],
                'subject' => $data['subject'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? SupportTicket::PRIORITY_LOW,
                'status' => SupportTicket::STATUS_PENDING,
            ]);

            // Add the initial description as the first message
            $this->addMessage(
                $ticket,
                $user,
                $data['description'],
                $data['attachments'] ?? [],
                false,
                true
            );

            return $ticket->fresh();
        });
    }

    /**
     * Get a user's support tickets (paginated).
     */
    public function listTickets(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return SupportTicket::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single ticket with messages.
     *
     * @throws TicketNotFoundException
     * @throws UnauthorizedTicketAccessException
     */
    public function getTicket(int $ticketId, int $userId): SupportTicket
    {
        $ticket = SupportTicket::with(['messages' => function ($q) {
            $q->with('user:id,name,profile_photo')
                ->orderBy('created_at', 'asc');
        }])->find($ticketId);

        if (!$ticket) {
            throw new TicketNotFoundException();
        }

        if ((int) $ticket->user_id !== $userId) {
            throw new UnauthorizedTicketAccessException();
        }

        return $ticket;
    }

    // ─── Messages ────────────────────────────────────────────────

    /**
     * Add a message to an existing ticket.
     *
     * @throws UnauthorizedTicketAccessException
     * @throws TicketClosedException
     */
    public function addMessage(
        SupportTicket $ticket,
        User $user,
        string $message,
        array $attachments = [],
        bool $isAdmin = false,
        bool $isInitial = false
    ): SupportTicketMessage {
        if (!$isAdmin && (int) $ticket->user_id !== (int) $user->id) {
            throw new UnauthorizedTicketAccessException();
        }

        if (!$isInitial && $ticket->isClosed()) {
            throw new TicketClosedException();
        }

        $uploadedAttachments = $this->processAttachments($attachments);

        $ticketMessage = SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $message,
            'attachments' => !empty($uploadedAttachments) ? $uploadedAttachments : null,
            'is_admin' => $isAdmin,
        ]);

        // If a user replies, change status to PENDING
        // If an admin replies, change status to OPEN
        if (!$isInitial) {
            $ticket->status = $isAdmin ? SupportTicket::STATUS_OPEN : SupportTicket::STATUS_PENDING;
            $ticket->save();
        }

        return $ticketMessage->fresh(['user:id,name,profile_photo']);
    }

    /**
     * Get messages for polling — returns only messages created after a given timestamp.
     *
     * @throws TicketNotFoundException
     * @throws UnauthorizedTicketAccessException
     */
    public function getMessagesSince(int $ticketId, int $userId, ?string $after = null): Collection
    {
        $ticket = SupportTicket::find($ticketId);

        if (!$ticket) {
            throw new TicketNotFoundException();
        }

        if ((int) $ticket->user_id !== $userId) {
            throw new UnauthorizedTicketAccessException();
        }

        $query = SupportTicketMessage::where('support_ticket_id', $ticketId)
            ->with('user:id,name,profile_photo')
            ->orderBy('created_at', 'asc');

        if ($after) {
            $query->after($after);
        }

        return $query->get();
    }

    /**
     * Mark all admin messages as read for a given ticket (from the user's perspective).
     *
     * @throws TicketNotFoundException
     * @throws UnauthorizedTicketAccessException
     */
    public function markMessagesAsRead(int $ticketId, int $userId): int
    {
        $ticket = SupportTicket::find($ticketId);

        if (!$ticket) {
            throw new TicketNotFoundException();
        }

        if ((int) $ticket->user_id !== $userId) {
            throw new UnauthorizedTicketAccessException();
        }

        // Mark admin messages as read (the user is reading admin replies)
        return SupportTicketMessage::where('support_ticket_id', $ticketId)
            ->where('is_admin', true)
            ->unread()
            ->update(['read_at' => now()]);
    }

    // ─── Status ──────────────────────────────────────────────────

    /**
     * Change the status of a ticket.
     *
     * @throws UnauthorizedTicketAccessException
     */
    public function updateStatus(SupportTicket $ticket, int $status, ?User $admin = null): bool
    {
        if ($admin && !$admin->isAdmin()) {
            throw new UnauthorizedTicketAccessException('Only admins can arbitrarily change ticket status.');
        }

        $ticket->status = $status;

        if (in_array($status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CANCELLED, SupportTicket::STATUS_DEFERRED], true)) {
            $ticket->resolved_at = now();
        } else {
            $ticket->resolved_at = null;
        }

        return $ticket->save();
    }

    // ─── Categories ──────────────────────────────────────────────

    /**
     * Get all active support categories.
     */
    public function getActiveCategories(): Collection
    {
        return SupportCategory::where('status', SupportCategory::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Validate that a category name exists and is active.
     *
     * @throws InvalidCategoryException
     */
    private function validateCategory(string $category): void
    {
        if (empty($category)) {
            throw new InvalidCategoryException('Category is required.');
        }

        // Check against dynamic DB categories first, then fall back to legacy constants
        $exists = SupportCategory::where('name', $category)
            ->where('status', 1)
            ->exists();

        if (!$exists) {
            // Check legacy constants for backward compatibility
            $legacyCategories = [
                SupportTicket::CAT_TECHNICAL,
                SupportTicket::CAT_REFUND,
                SupportTicket::CAT_CANCELLATION,
                SupportTicket::CAT_GENERAL,
                SupportTicket::CAT_OTHER,
            ];

            if (!in_array($category, $legacyCategories, true)) {
                throw new InvalidCategoryException();
            }
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Process and store uploaded attachments.
     *
     * @param  array<UploadedFile|mixed>  $attachments
     * @return array<string>  Array of stored file paths.
     */
    private function processAttachments(array $attachments): array
    {
        $uploadedPaths = [];

        foreach ($attachments as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->store('support_attachments', 'public');

                if ($path !== false) {
                    $uploadedPaths[] = $path;
                }
            }
        }

        return $uploadedPaths;
    }
}
