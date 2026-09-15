<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Support\StoreTicketRequest;
use App\Http\Requests\Api\Support\ReplyTicketRequest;
use App\Services\SupportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Support Tickets', description: 'API Endpoints for managing support tickets (For both Patients and Nurses)')]
class SupportController extends Controller
{
    public function __construct(
        protected SupportService $supportService
    ) {}

    // ─── Categories ──────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/support/categories',
        operationId: 'getSupportCategories',
        summary: 'List active support categories',
        description: 'Returns a list of active support categories that users can select when creating a ticket.',
        security: [['bearerAuth' => []]],
        tags: ['Support Tickets'],
        responses: [
            new OA\Response(response: 200, description: 'Categories retrieved successfully')
        ]
    )]
    public function categories(): JsonResponse
    {
        $categories = $this->supportService->getActiveCategories();

        return ApiResponse::success('Categories retrieved successfully.', [
            'categories' => $categories,
        ]);
    }

    // ─── Ticket CRUD ─────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/support/tickets',
        operationId: 'getSupportTickets',
        summary: 'List user tickets',
        description: 'Get a paginated list of support tickets for the authenticated user.',
        security: [['bearerAuth' => []]],
        tags: ['Support Tickets'],
        responses: [
            new OA\Response(response: 200, description: 'Tickets retrieved successfully')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $tickets = $this->supportService->listTickets($request->user()->id);

        return ApiResponse::success('Support tickets retrieved successfully.', [
            'tickets' => $tickets,
        ]);
    }

    #[OA\Post(
        path: '/api/v1/support/tickets',
        operationId: 'storeSupportTicket',
        summary: 'Create a new support ticket',
        description: 'Create a new support ticket with subject, description, category, optional priority and attachments.',
        security: [['bearerAuth' => []]],
        tags: ['Support Tickets'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['category', 'subject', 'description'],
                    properties: [
                        new OA\Property(property: 'category', type: 'string', example: 'Technical'),
                        new OA\Property(property: 'subject', type: 'string', example: 'App keeps crashing'),
                        new OA\Property(property: 'description', type: 'string', example: 'My app crashes on startup'),
                        new OA\Property(property: 'priority', type: 'integer', description: '0: Low, 1: Medium, 2: High', example: 1),
                        new OA\Property(property: 'attachments[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary'), description: 'Array of files (images, pdf, doc, docx)')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Ticket created successfully'),
            new OA\Response(response: 422, description: 'Validation errors')
        ]
    )]
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->supportService->createTicket(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success('Support ticket created successfully.', [
            'ticket' => $ticket,
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/support/tickets/{id}',
        operationId: 'showSupportTicket',
        summary: 'Get ticket details and messages',
        description: 'Get full details of a specific ticket with all messages and attachment URLs.',
        security: [['bearerAuth' => []]],
        tags: ['Support Tickets'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Ticket ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ticket details retrieved'),
            new OA\Response(response: 403, description: 'Unauthorized access'),
            new OA\Response(response: 404, description: 'Ticket not found')
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = $this->supportService->getTicket($id, $request->user()->id);

        return ApiResponse::success('Ticket retrieved successfully.', [
            'ticket' => $ticket,
        ]);
    }

    // ─── Chat / Messages ─────────────────────────────────────────

    #[OA\Post(
        path: '/api/v1/support/tickets/{id}/reply',
        operationId: 'replySupportTicket',
        summary: 'Reply to an existing ticket',
        description: 'Send a message to an existing ticket. Supports file attachments (images, pdf, doc, docx).',
        security: [['bearerAuth' => []]],
        tags: ['Support Tickets'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Ticket ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['message'],
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Thank you for your help.'),
                        new OA\Property(property: 'attachments[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary'), description: 'Array of files')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Reply sent successfully'),
            new OA\Response(response: 403, description: 'Unauthorized access'),
            new OA\Response(response: 404, description: 'Ticket not found'),
            new OA\Response(response: 409, description: 'Ticket is closed')
        ]
    )]
    public function reply(ReplyTicketRequest $request, int $id): JsonResponse
    {
        $ticket = $this->supportService->getTicket($id, $request->user()->id);

        $message = $this->supportService->addMessage(
            $ticket,
            $request->user(),
            $request->message,
            $request->file('attachments') ?? []
        );

        return ApiResponse::success('Reply sent successfully.', [
            'message' => $message,
        ]);
    }

    #[OA\Get(
        path: '/api/v1/support/tickets/{id}/messages',
        operationId: 'pollSupportMessages',
        summary: 'Poll for new messages (chat polling)',
        description: 'Get messages for a ticket. Pass `after` query parameter (ISO 8601 timestamp) to fetch only newer messages for efficient polling.',
        security: [['bearerAuth' => []]],
        tags: ['Support Tickets'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Ticket ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'after', description: 'ISO 8601 timestamp. Only messages after this time will be returned.', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Messages retrieved'),
            new OA\Response(response: 403, description: 'Unauthorized access'),
            new OA\Response(response: 404, description: 'Ticket not found')
        ]
    )]
    public function messages(Request $request, int $id): JsonResponse
    {
        $messages = $this->supportService->getMessagesSince(
            $id,
            $request->user()->id,
            $request->query('after')
        );

        return ApiResponse::success('Messages retrieved successfully.', [
            'messages' => $messages,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/support/tickets/{id}/read',
        operationId: 'markSupportMessagesRead',
        summary: 'Mark messages as read',
        description: 'Marks all admin messages on this ticket as read by the user.',
        security: [['bearerAuth' => []]],
        tags: ['Support Tickets'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Ticket ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Messages marked as read'),
            new OA\Response(response: 403, description: 'Unauthorized access'),
            new OA\Response(response: 404, description: 'Ticket not found')
        ]
    )]
    public function markRead(Request $request, int $id): JsonResponse
    {
        $count = $this->supportService->markMessagesAsRead($id, $request->user()->id);

        return ApiResponse::success('Messages marked as read.', [
            'read_count' => $count,
        ]);
    }
}
