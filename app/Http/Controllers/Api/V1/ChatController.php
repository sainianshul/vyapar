<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use App\Helpers\ApiResponse;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ChatController extends Controller
{
    #[OA\Get(
        path: '/api/v1/chat/conversations',
        operationId: 'getConversations',
        summary: 'Get list of conversations for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Conversations fetched successfully'),
        ]
    )]
    public function getConversations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $conversations = Conversation::where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->with(['buyer:id,name,profile_photo', 'seller:id,name,profile_photo', 'product:id,title', 'requirement:id,title'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $formatted = $conversations->map(function ($conv) use ($userId) {
            $otherUser = $conv->buyer_id === $userId ? $conv->seller : $conv->buyer;
            $unreadCount = $conv->buyer_id === $userId ? $conv->buyer_unread_count : $conv->seller_unread_count;

            return [
                'id' => $conv->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'profile_photo' => $otherUser->profile_photo ? asset('storage/' . $otherUser->profile_photo) : null,
                ],
                'product' => $conv->product,
                'requirement' => $conv->requirement,
                'last_message_at' => $conv->last_message_at,
                'unread_count' => $unreadCount,
            ];
        });

        return ApiResponse::success('Conversations fetched successfully', [
            'conversations' => $formatted,
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/v1/chat/conversations',
        operationId: 'startConversation',
        summary: 'Start a new conversation or get existing one',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['seller_id'],
                    properties: [
                        new OA\Property(property: 'seller_id', type: 'integer', example: 2),
                        new OA\Property(property: 'product_id', type: 'integer', nullable: true, example: 1),
                        new OA\Property(property: 'requirement_id', type: 'integer', nullable: true, example: null),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Conversation ready'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function startConversation(Request $request): JsonResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|exists:users,id', 
            'product_id' => 'nullable|integer|exists:products,id',
            'requirement_id' => 'nullable|integer|exists:requirements,id',
        ]);

        $buyerId = $request->user()->id;
        $sellerId = $request->seller_id;

        if ($buyerId == $sellerId) {
            return ApiResponse::error('You cannot chat with yourself', 400);
        }

        $conversation = Conversation::firstOrCreate(
            [
                'product_id' => $request->product_id,
                'requirement_id' => $request->requirement_id,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
            ],
            [
                'status' => Conversation::STATUS_ACTIVE,
            ]
        );

        // --- AUTO CAPTURE LEAD ---
        // Jab bhi koi conversation start hogi, backend automatically lead capture kar lega.
        // Frontend ko alag se Lead API hit karne ki zaroorat nahi hai.
        try {
            app(\App\Services\LeadService::class)->captureLead([
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'product_id' => $request->product_id,
                'requirement_id' => $request->requirement_id,
                'source' => \App\Models\Lead::SOURCE_CHAT,
            ]);
        } catch (\Exception $e) {
            // Silently ignore if lead capture fails (e.g. chatting with self which shouldn't happen here)
        }

        $conversation->load(['buyer:id,name,profile_photo', 'seller:id,name,profile_photo', 'product:id,title', 'requirement:id,title']);

        return ApiResponse::success('Conversation ready', [
            'conversation' => $conversation
        ]);
    }

    #[OA\Get(
        path: '/api/v1/chat/conversations/{id}/messages',
        operationId: 'getMessages',
        summary: 'Get messages of a conversation',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Messages fetched successfully'),
        ]
    )]
    public function getMessages(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('buyer_id', $userId)->orWhere('seller_id', $userId);
            })->firstOrFail();

        // Mark as read
        $conversation->markAsReadFor($userId);

        $messages = $conversation->messages()->orderByDesc('created_at')->paginate(50);

        return ApiResponse::success('Messages fetched', [
            'messages' => collect($messages->items())->reverse()->map(fn($m) => $m->toApiResponse())->values(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'has_more' => $messages->hasMorePages(),
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/v1/chat/conversations/{id}/messages',
        operationId: 'sendMessage',
        summary: 'Send a message to a conversation',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['type'],
                    properties: [
                        new OA\Property(property: 'type', type: 'integer', description: '1:Text, 2:Image, 3:CallLog'),
                        new OA\Property(property: 'body', type: 'string', nullable: true),
                        new OA\Property(property: 'media', type: 'string', format: 'binary', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Message sent successfully'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'integer', Rule::in([Message::TYPE_TEXT, Message::TYPE_IMAGE, Message::TYPE_CALLLOG])],
            'body' => 'required_if:type,1|string|nullable',
            'media' => 'required_if:type,2|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $userId = $request->user()->id;

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('buyer_id', $userId)->orWhere('seller_id', $userId);
            })->firstOrFail();

        $mediaPath = null;
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('chat_media', 'public');
        }

        DB::beginTransaction();
        try {
            $message = $conversation->messages()->create([
                'sender_id' => $userId,
                'type' => $request->type,
                'body' => $request->body,
                'media_path' => $mediaPath,
            ]);

            $conversation->update(['last_message_at' => now()]);
            $conversation->incrementUnreadForOther($userId);

            DB::commit();

            // Broadcast Event via Pusher
            $receiverId = $conversation->buyer_id === $userId ? $conversation->seller_id : $conversation->buyer_id;
            broadcast(new MessageSent($message, $receiverId))->toOthers();

            return ApiResponse::success('Message sent', [
                'message' => $message->toApiResponse()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($mediaPath) {
                Storage::disk('public')->delete($mediaPath);
            }
            return ApiResponse::error('Failed to send message: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/chat/conversations/{id}/read',
        operationId: 'markConversationAsRead',
        summary: 'Mark conversation as read',
        security: [['bearerAuth' => []]],
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Marked as read'),
        ]
    )]
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('buyer_id', $userId)->orWhere('seller_id', $userId);
            })->firstOrFail();

        $conversation->markAsReadFor($userId);

        return ApiResponse::success('Marked as read');
    }
}
