<?php

namespace App\Http\Controllers\Api\Nurse;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\CancellationService;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected CancellationService $cancellationService;
    protected WalletService $walletService;
    protected WithdrawalService $withdrawalService;

    public function __construct(
        BookingService $bookingService,
        CancellationService $cancellationService,
        WalletService $walletService,
        WithdrawalService $withdrawalService
    ) {
        $this->bookingService = $bookingService;
        $this->cancellationService = $cancellationService;
        $this->walletService = $walletService;
        $this->withdrawalService = $withdrawalService;
    }

    #[OA\Get(
        path: '/api/v1/nurse/bookings',
        operationId: 'listNurseBookings',
        summary: 'List nurse bookings',
        description: 'Paginated list of bookings assigned to the nurse.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        responses: [
            new OA\Response(response: 200, description: 'Bookings retrieved')
        ]
    )]
    public function index(Request $request)
    {
        $nurseId = $request->user()->nurseProfile->id ?? null;

        if (!$nurseId) {
            return ApiResponse::success('Bookings retrieved.', ['bookings' => []]);
        }

        $bookings = $this->bookingService->listForNurse($nurseId);

        $bookings->getCollection()->transform(function ($booking) {
            return $booking->getNurseBookingArray();
        });

        return ApiResponse::success('Bookings retrieved successfully.', [
            'bookings' => $bookings,
        ]);
    }

    #[OA\Get(
        path: '/api/v1/nurse/schedule',
        operationId: 'getNurseSchedule',
        summary: 'Get daily schedule',
        description: 'Get all sessions for a specific date.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        parameters: [
            new OA\Parameter(name: 'date', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Schedule retrieved')
        ]
    )]
    public function schedule(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $nurseId = $request->user()->nurseProfile->id ?? null;

        if (!$nurseId) {
            return ApiResponse::success('Schedule retrieved.', ['sessions' => []]);
        }

        $sessions = $this->bookingService->getNurseSchedule($nurseId, $request->date);

        $formatted = $sessions->map(function ($session) {
            $booking = $session->booking;
            return [
                'session_id' => $session->id,
                'booking_id' => $booking->id,
                'booking_ref' => $booking->reference_id,
                'session_number' => $session->session_number,
                'total_sessions' => $booking->total_sessions,
                'session_date' => $session->session_date->format('Y-m-d'),
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'status' => $session->status,
                'status_text' => $session->status_text,
                'patient_name' => $booking->user->name ?? 'Unknown',
                'patient_phone' => $booking->user->phone ?? null,
                'care_type' => $booking->careRequest->careType->name ?? 'Unknown',
            ];
        });

        return ApiResponse::success('Schedule retrieved successfully.', [
            'date' => $request->date,
            'sessions' => $formatted,
        ]);
    }

    #[OA\Post(
        path: '/api/v1/nurse/sessions/{session_id}/start',
        operationId: 'startNurseSession',
        summary: 'Start session with OTP',
        description: 'Verifies the 6-digit OTP from the patient to start the session.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        parameters: [
            new OA\Parameter(name: 'session_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['otp'],
                properties: [
                    new OA\Property(property: 'otp', type: 'string', minLength: 6, maxLength: 6, example: '123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Session started'),
            new OA\Response(response: 422, description: 'Invalid OTP')
        ]
    )]
    public function startSession(Request $request, int $sessionId)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $nurseId = $request->user()->nurseProfile->id ?? null;
        if (!$nurseId) {
            return ApiResponse::error('Nurse profile not found.', 404);
        }

        $session = $this->bookingService->startSession($sessionId, $request->otp, $nurseId);

        return ApiResponse::success('Session started successfully.', ['session' => $session]);
    }

    #[OA\Post(
        path: '/api/v1/nurse/sessions/{session_id}/end',
        operationId: 'endNurseSession',
        summary: 'End session',
        description: 'Completes a started session with end OTP. If all sessions are done, booking completes and nurse gets paid.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        parameters: [
            new OA\Parameter(name: 'session_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['otp'],
                properties: [
                    new OA\Property(property: 'otp', type: 'string', minLength: 6, maxLength: 6, example: '654321'),
                    new OA\Property(property: 'notes', type: 'string', example: 'Vitals stable, medicine administered.'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Session completed'),
            new OA\Response(response: 409, description: 'Session not started'),
            new OA\Response(response: 422, description: 'Invalid End OTP')
        ]
    )]
    public function endSession(Request $request, int $sessionId)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            'notes' => 'nullable|string|max:1000'
        ]);

        $nurseId = $request->user()->nurseProfile->id ?? null;
        if (!$nurseId) {
            return ApiResponse::error('Nurse profile not found.', 404);
        }

        $session = $this->bookingService->endSession($sessionId, $nurseId, $request->otp, $request->notes);

        return ApiResponse::success('Session completed successfully.', ['session' => $session]);
    }

    #[OA\Post(
        path: '/api/v1/nurse/sessions/{session_id}/force-end',
        operationId: 'forceEndNurseSession',
        summary: 'Force End session',
        description: 'Completes a started session without OTP if nurse is at patient location.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        parameters: [
            new OA\Parameter(name: 'session_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reason', 'latitude', 'longitude'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: 'Patient phone dead'),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 28.704060),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 77.102493),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Session completed'),
            new OA\Response(response: 409, description: 'Distance exceeded or session not started')
        ]
    )]
    public function forceEndSession(Request $request, int $sessionId)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $nurseId = $request->user()->nurseProfile->id ?? null;
        if (!$nurseId) {
            return ApiResponse::error('Nurse profile not found.', 404);
        }

        $session = $this->bookingService->forceEndSession(
            $sessionId, 
            $nurseId, 
            $request->reason, 
            (float) $request->latitude, 
            (float) $request->longitude
        );

        return ApiResponse::success('Session force ended successfully.', ['session' => $session]);
    }

    #[OA\Get(
        path: '/api/v1/nurse/sessions/today',
        operationId: 'todayNurseSessions',
        summary: 'Get today\'s sessions',
        description: 'Returns a list of today\'s sessions for the nurse.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        responses: [
            new OA\Response(response: 200, description: 'Today\'s sessions retrieved')
        ]
    )]
    public function todaySessions(Request $request)
    {
        $nurseId = $request->user()->nurseProfile->id ?? null;
        if (!$nurseId) {
            return ApiResponse::success('Today\'s sessions retrieved successfully.', ['sessions' => []]);
        }

        $sessions = \App\Models\BookingSession::whereHas('booking', function($query) use ($nurseId) {
                $query->where('nurse_id', $nurseId)
                      ->whereIn('status', [\App\Models\Booking::STATUS_CONFIRMED, \App\Models\Booking::STATUS_ACTIVE]);
            })
            ->where('session_date', now()->format('Y-m-d'))
            ->with(['booking'])
            ->get();

        $formattedSessions = $sessions->map(function ($session) {
            return $session->getNurseSessionArray();
        });

        return ApiResponse::success('Today\'s sessions retrieved successfully.', [
            'sessions' => $formattedSessions
        ]);
    }

    #[OA\Get(
        path: '/api/v1/nurse/bookings/{booking_id}/sessions',
        operationId: 'listNurseBookingSessions',
        summary: 'List sessions for a booking',
        description: 'Returns all sessions for a specific booking assigned to the nurse.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sessions retrieved')
        ]
    )]
    public function listSessions(Request $request, int $bookingId)
    {
        $nurseId = $request->user()->nurseProfile->id ?? null;
        if (!$nurseId) {
            return ApiResponse::error('Nurse profile not found.', 404);
        }

        $booking = \App\Models\Booking::where('id', $bookingId)
            ->where('nurse_id', $nurseId)
            ->firstOrFail();

        $sessions = $booking->sessions()->orderBy('session_date')->get();

        $formattedSessions = $sessions->map(function ($session) {
            return $session->getNurseSessionArray();
        });

        return ApiResponse::success('Sessions retrieved successfully.', [
            'sessions' => $formattedSessions
        ]);
    }

    #[OA\Get(
        path: '/api/v1/nurse/bookings/{booking_id}/sessions/{session_id}',
        operationId: 'showNurseBookingSession',
        summary: 'Show particular session details',
        description: 'Returns details of a specific session for the nurse.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'session_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Session details retrieved')
        ]
    )]
    public function showSession(Request $request, int $bookingId, int $sessionId)
    {
        $nurseId = $request->user()->nurseProfile->id ?? null;
        if (!$nurseId) {
            return ApiResponse::error('Nurse profile not found.', 404);
        }

        $session = \App\Models\BookingSession::where('booking_id', $bookingId)
            ->where('id', $sessionId)
            ->whereHas('booking', function ($q) use ($nurseId) {
                $q->where('nurse_id', $nurseId);
            })
            ->with('booking')
            ->firstOrFail();

        return ApiResponse::success('Session details retrieved successfully.', [
            'session' => $session->getNurseSessionArray()
        ]);
    }

    #[OA\Post(
        path: '/api/v1/nurse/bookings/{booking_id}/cancel',
        operationId: 'cancelNurseBooking',
        summary: 'Cancel booking (nurse)',
        description: 'Nurse cancels booking. User gets full refund for remaining sessions. Nurse receives cancellation strike.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: 'Personal emergency'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Booking cancelled'),
            new OA\Response(response: 409, description: 'Cannot cancel')
        ]
    )]
    public function cancel(Request $request, int $bookingId)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $nurseId = $request->user()->nurseProfile->id ?? null;
        if (!$nurseId) {
            return ApiResponse::error('Nurse profile not found.', 404);
        }

        $result = $this->cancellationService->cancelByNurse($bookingId, $nurseId, $request->reason);

        return ApiResponse::success('Booking cancelled.', $result);
    }

    #[OA\Get(
        path: '/api/v1/nurse/wallet',
        operationId: 'getNurseWallet',
        summary: 'Get nurse wallet',
        description: 'Returns wallet balance and paginated transaction/payout history.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Wallet'],
        responses: [
            new OA\Response(response: 200, description: 'Wallet details')
        ]
    )]
    public function wallet(Request $request)
    {
        $userId = $request->user()->id;

        return ApiResponse::success('Wallet details retrieved.', [
            'balance' => $this->walletService->getBalance($userId),
            'transactions' => $this->walletService->getTransactions($userId),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/nurse/wallet/withdraw',
        operationId: 'requestNurseWithdrawal',
        summary: 'Request withdrawal to bank',
        description: 'Nurse requests withdrawal of earnings to bank account. Minimum ₹100.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Wallet'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount', 'account_name', 'account_number', 'ifsc'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 2550.00),
                    new OA\Property(property: 'account_name', type: 'string', example: 'Nurse Name'),
                    new OA\Property(property: 'account_number', type: 'string', example: '1234567890'),
                    new OA\Property(property: 'ifsc', type: 'string', example: 'SBIN0001234'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Withdrawal request created'),
            new OA\Response(response: 422, description: 'Insufficient balance or invalid amount'),
            new OA\Response(response: 409, description: 'Duplicate pending request')
        ]
    )]
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'account_name' => 'required|string|max:150',
            'account_number' => 'required|string|max:50',
            'ifsc' => 'required|string|max:20',
        ]);

        $withdrawal = $this->withdrawalService->requestWithdrawal(
            $request->user()->id,
            (float) $request->amount,
            [
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'ifsc' => $request->ifsc,
            ]
        );

        return ApiResponse::success('Withdrawal request submitted.', [
            'withdrawal' => $withdrawal,
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/nurse/wallet/withdrawals',
        operationId: 'listNurseWithdrawals',
        summary: 'List withdrawal history',
        description: 'Paginated list of nurse withdrawal requests.',
        security: [['bearerAuth' => []]],
        tags: ['Nurse Wallet'],
        responses: [
            new OA\Response(response: 200, description: 'Withdrawal history')
        ]
    )]
    public function withdrawals(Request $request)
    {
        $withdrawals = $this->withdrawalService->getWithdrawals($request->user()->id);

        return ApiResponse::success('Withdrawals retrieved.', [
            'withdrawals' => $withdrawals,
        ]);
    }
}
