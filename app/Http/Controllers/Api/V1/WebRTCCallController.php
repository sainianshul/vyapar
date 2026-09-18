<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\WebRTCCallLog;
use App\Models\User;
use App\Models\Lead;
use App\Events\CallInitiated;
use App\Events\CallAnswered;
use App\Events\CallSignal;
use App\Events\CallEnded;
use App\Helpers\ApiResponse;
use App\Services\LeadService;
use OpenApi\Attributes as OA;

class WebRTCCallController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    #[OA\Post(
        path: '/api/v1/call/initiate',
        operationId: 'initiateCall',
        summary: 'Start a new WebRTC call',
        security: [['bearerAuth' => []]],
        tags: ['WebRTC Calling'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['receiver_id', 'call_type'],
                    properties: [
                        new OA\Property(property: 'receiver_id', type: 'integer', example: 2),
                        new OA\Property(property: 'call_type', type: 'string', enum: ['audio', 'video'], example: 'audio'),
                        new OA\Property(property: 'product_id', type: 'integer', nullable: true),
                        new OA\Property(property: 'requirement_id', type: 'integer', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Call initiated successfully'),
            new OA\Response(response: 409, description: 'User is busy'),
        ]
    )]
    public function initiateCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id'    => 'required|exists:users,id',
            'call_type'      => 'required|in:audio,video',
            'product_id'     => 'nullable|exists:products,id',
            'requirement_id' => 'nullable|exists:requirements,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $caller     = $request->user();
        $callerId   = $caller->id;
        $receiverId = $request->receiver_id;

        if ($callerId == $receiverId) {
            return ApiResponse::error('You cannot call yourself.', 400);
        }

        try {
            // Check if receiver is already on an active call
            $activeCall = WebRTCCallLog::activeForUser($receiverId)->first();
            if ($activeCall) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User is busy on another call.',
                    'busy'    => true,
                ], 409);
            }

            // Check if caller is already on an active call
            $callerActiveCall = WebRTCCallLog::activeForUser($callerId)->first();
            if ($callerActiveCall) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You are already on a call.',
                    'busy'    => true,
                ], 409);
            }

            // Create call log
            $callLog = WebRTCCallLog::create([
                'caller_id'      => $callerId,
                'receiver_id'    => $receiverId,
                'product_id'     => $request->product_id,
                'requirement_id' => $request->requirement_id,
                'call_type'      => $request->call_type,
                'status'         => 'ringing',
            ]);

            // AUTO CAPTURE LEAD
            try {
                $this->leadService->captureLead([
                    'buyer_id'       => $callerId,
                    'seller_id'      => $receiverId,
                    'product_id'     => $request->product_id,
                    'requirement_id' => $request->requirement_id,
                    'source'         => Lead::SOURCE_CALL, // Hot Lead
                ]);
            } catch (\Exception $e) {
                // Silently ignore lead capture errors
            }

            $callerAvatar = $caller->profile_photo ? asset('storage/' . $caller->profile_photo) : null;

            // Broadcast incoming call to receiver via Pusher
            event(new CallInitiated(
                $callLog->id,
                $callerId,
                $caller->name,
                $callerAvatar,
                $request->call_type,
                $receiverId,
                $request->product_id,
                $request->requirement_id
            ));

            // TODO: FCM Push Notification can be added here for killed/background apps

            return ApiResponse::success('Call initiated successfully.', [
                'call_id'     => $callLog->id,
                'caller_id'   => $callerId,
                'receiver_id' => $receiverId,
                'call_type'   => $request->call_type,
                'status'      => 'ringing',
            ]);

        } catch (\Exception $e) {
            Log::error("Error initiating call: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/call/answer',
        operationId: 'answerCall',
        summary: 'Accept or reject an incoming call',
        security: [['bearerAuth' => []]],
        tags: ['WebRTC Calling'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['call_id', 'action'],
                    properties: [
                        new OA\Property(property: 'call_id', type: 'integer'),
                        new OA\Property(property: 'action', type: 'string', enum: ['accept', 'reject']),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Call answered successfully'),
        ]
    )]
    public function answerCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required|exists:webrtc_call_logs,id',
            'action'  => 'required|in:accept,reject',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $user   = $request->user();
        $userId = $user->id;

        try {
            $callLog = WebRTCCallLog::find($request->call_id);

            if ($callLog->receiver_id != $userId) {
                return ApiResponse::error('Only the receiver can answer this call.', 403);
            }

            if (!in_array($callLog->status, ['initiated', 'ringing'])) {
                return ApiResponse::error('Call is no longer available. Status: ' . $callLog->status, 410);
            }

            if ($request->action === 'accept') {
                $callLog->status     = 'answered';
                $callLog->started_at = now();
                $callLog->save();
            } else {
                $callLog->status     = 'rejected';
                $callLog->end_reason = 'rejected';
                $callLog->ended_at   = now();
                $callLog->save();
            }

            event(new CallAnswered(
                $callLog->id,
                $userId,
                $user->name,
                $request->action,
                $callLog->caller_id
            ));

            return ApiResponse::success('Call ' . $request->action . 'ed successfully.', [
                'call_id'   => $callLog->id,
                'status'    => $callLog->status,
                'call_type' => $callLog->call_type,
            ]);

        } catch (\Exception $e) {
            Log::error("Error answering call: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/call/signal',
        operationId: 'relaySignal',
        summary: 'Relay WebRTC SDP/ICE signal via Pusher',
        security: [['bearerAuth' => []]],
        tags: ['WebRTC Calling'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['call_id', 'signal_type', 'signal_data'],
                    properties: [
                        new OA\Property(property: 'call_id', type: 'integer'),
                        new OA\Property(property: 'signal_type', type: 'string', enum: ['offer', 'answer', 'ice-candidate']),
                        new OA\Property(property: 'signal_data', type: 'string', description: 'JSON string of SDP/ICE'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Signal relayed'),
        ]
    )]
    public function relaySignal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_id'     => 'required|exists:webrtc_call_logs,id',
            'signal_type' => 'required|in:offer,answer,ice-candidate',
            'signal_data' => 'required',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $userId = $request->user()->id;

        try {
            $callLog = WebRTCCallLog::find($request->call_id);

            if (!$callLog->isParticipant($userId)) {
                return ApiResponse::error('You are not a participant in this call.', 403);
            }

            $targetUserId = $callLog->getOtherUserId($userId);

            event(new CallSignal(
                $callLog->id,
                $userId,
                $targetUserId,
                $request->signal_type,
                $request->signal_data
            ));

            return ApiResponse::success('Signal relayed.');

        } catch (\Exception $e) {
            Log::error("Error relaying signal: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/call/end',
        operationId: 'endCall',
        summary: 'End an active call',
        security: [['bearerAuth' => []]],
        tags: ['WebRTC Calling'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['call_id'],
                    properties: [
                        new OA\Property(property: 'call_id', type: 'integer'),
                        new OA\Property(property: 'reason', type: 'string', enum: ['caller_ended', 'receiver_ended', 'error'], nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Call ended'),
        ]
    )]
    public function endCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required|exists:webrtc_call_logs,id',
            'reason'  => 'nullable|in:caller_ended,receiver_ended,missed,rejected,error',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $userId = $request->user()->id;

        try {
            $callLog = WebRTCCallLog::find($request->call_id);

            if (!$callLog->isParticipant($userId)) {
                return ApiResponse::error('You are not a participant in this call.', 403);
            }

            if (in_array($callLog->status, ['ended', 'rejected', 'missed'])) {
                return ApiResponse::success('Call already ended.');
            }

            $reason = $request->reason ?: (($userId == $callLog->caller_id) ? 'caller_ended' : 'receiver_ended');

            $callLog->endCall($reason);

            $targetUserId = $callLog->getOtherUserId($userId);

            event(new CallEnded(
                $callLog->id,
                $userId,
                $reason,
                $targetUserId,
                $callLog->duration_seconds
            ));

            return ApiResponse::success('Call ended.', [
                'call_id'          => $callLog->id,
                'duration_seconds' => $callLog->duration_seconds,
                'end_reason'       => $reason,
            ]);

        } catch (\Exception $e) {
            Log::error("Error ending call: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/call/missed',
        operationId: 'missedCall',
        summary: 'Mark ringing call as missed (timeout)',
        security: [['bearerAuth' => []]],
        tags: ['WebRTC Calling'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['call_id'],
                    properties: [
                        new OA\Property(property: 'call_id', type: 'integer'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Call marked as missed'),
        ]
    )]
    public function missedCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required|exists:webrtc_call_logs,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $user   = $request->user();
        $userId = $user->id;

        try {
            $callLog = WebRTCCallLog::find($request->call_id);

            if ($callLog->caller_id != $userId) {
                return ApiResponse::error('Only the caller can mark a call as missed.', 403);
            }

            if (!in_array($callLog->status, ['initiated', 'ringing'])) {
                return ApiResponse::success('Call status already updated: ' . $callLog->status);
            }

            $callLog->status     = 'missed';
            $callLog->end_reason = 'missed';
            $callLog->ended_at   = now();
            $callLog->save();

            event(new CallEnded(
                $callLog->id,
                $userId,
                'missed',
                $callLog->receiver_id,
                null
            ));

            // TODO: Send missed call FCM notification

            return ApiResponse::success('Call marked as missed.');

        } catch (\Exception $e) {
            Log::error("Error marking missed call: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/call/history',
        operationId: 'callHistory',
        summary: 'Get call history for logged-in user',
        security: [['bearerAuth' => []]],
        tags: ['WebRTC Calling'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Call history fetched'),
        ]
    )]
    public function callHistory(Request $request)
    {
        $userId = $request->user()->id;

        try {
            $perPage = $request->input('per_page', 20);

            $calls = WebRTCCallLog::forUser($userId)
                ->with(['caller:id,name,profile_photo,phone', 'receiver:id,name,profile_photo,phone', 'product:id,title', 'requirement:id,title'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $formattedCalls = $calls->getCollection()->map(function ($call) use ($userId) {
                $isOutgoing  = ($call->caller_id == $userId);
                $otherUser   = $isOutgoing ? $call->receiver : $call->caller;
                $otherAvatar = $otherUser && $otherUser->profile_photo
                    ? asset('storage/' . $otherUser->profile_photo)
                    : null;

                return [
                    'call_id'          => $call->id,
                    'direction'        => $isOutgoing ? 'outgoing' : 'incoming',
                    'call_type'        => $call->call_type,
                    'status'           => $call->status,
                    'other_user'       => [
                        'id'     => $otherUser ? $otherUser->id : null,
                        'name'   => $otherUser ? $otherUser->name : 'Unknown',
                        'phone'  => $otherUser ? $otherUser->phone : null,
                        'avatar' => $otherAvatar,
                    ],
                    'product'          => $call->product,
                    'requirement'      => $call->requirement,
                    'duration_seconds' => $call->duration_seconds,
                    'started_at'       => $call->started_at ? $call->started_at->format('Y-m-d H:i:s') : null,
                    'ended_at'         => $call->ended_at ? $call->ended_at->format('Y-m-d H:i:s') : null,
                    'created_at'       => $call->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return ApiResponse::success('Call history', [
                'history' => $formattedCalls,
                'pagination'  => [
                    'current_page' => $calls->currentPage(),
                    'last_page'    => $calls->lastPage(),
                    'per_page'     => $calls->perPage(),
                    'total'        => $calls->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching call history: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/call/active',
        operationId: 'activeCall',
        summary: 'Check if user has an active call',
        security: [['bearerAuth' => []]],
        tags: ['WebRTC Calling'],
        responses: [
            new OA\Response(response: 200, description: 'Active call status'),
        ]
    )]
    public function activeCall(Request $request)
    {
        $userId = $request->user()->id;

        try {
            $activeCall = WebRTCCallLog::activeForUser($userId)
                ->with(['caller:id,name,profile_photo', 'receiver:id,name,profile_photo', 'product:id,title'])
                ->first();

            if (!$activeCall) {
                return ApiResponse::success('No active call', [
                    'has_active' => false,
                    'active_call' => null,
                ]);
            }

            $otherUser = ($activeCall->caller_id == $userId)
                ? $activeCall->receiver
                : $activeCall->caller;

            $otherAvatar = $otherUser && $otherUser->profile_photo
                ? asset('storage/' . $otherUser->profile_photo)
                : null;

            return ApiResponse::success('Active call found', [
                'has_active' => true,
                'active_call' => [
                    'call_id'    => $activeCall->id,
                    'call_type'  => $activeCall->call_type,
                    'status'     => $activeCall->status,
                    'direction'  => ($activeCall->caller_id == $userId) ? 'outgoing' : 'incoming',
                    'other_user' => [
                        'id'     => $otherUser ? $otherUser->id : null,
                        'name'   => $otherUser ? $otherUser->name : 'Unknown',
                        'avatar' => $otherAvatar,
                    ],
                    'product'    => $activeCall->product,
                    'started_at' => $activeCall->started_at ? $activeCall->started_at->format('Y-m-d H:i:s') : null,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Error checking active call: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/cron/calls/cleanup',
        operationId: 'cronCleanupCalls',
        summary: 'Webhook to automatically mark stale ringing calls as missed',
        tags: ['Cron Jobs'],
        responses: [
            new OA\Response(response: 200, description: 'Cleanup executed'),
        ]
    )]
    public function cronCleanupCalls(Request $request)
    {
        try {
            $threshold = now()->subMinutes(2);

            $staleCalls = WebRTCCallLog::whereIn('status', ['initiated', 'ringing'])
                ->where('created_at', '<', $threshold)
                ->get();

            $count = 0;

            foreach ($staleCalls as $callLog) {
                $callLog->status     = 'missed';
                $callLog->end_reason = 'missed';
                $callLog->ended_at   = now();
                $callLog->save();

                event(new CallEnded(
                    $callLog->id,
                    $callLog->caller_id, // We just pass caller as the actor
                    'missed',
                    $callLog->receiver_id,
                    null
                ));

                // Send to receiver as well just in case they need to clear their UI
                event(new CallEnded(
                    $callLog->id,
                    $callLog->caller_id,
                    'missed',
                    $callLog->caller_id,
                    null
                ));

                $count++;
            }

            return ApiResponse::success('Cron executed successfully.', [
                'cleaned_up_count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error("Error running calls cleanup cron: " . $e->getMessage());
            return ApiResponse::error('Server error: ' . $e->getMessage(), 500);
        }
    }
}
