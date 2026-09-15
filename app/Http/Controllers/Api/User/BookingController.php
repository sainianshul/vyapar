<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSession;
use App\Services\BookingService;
use App\Services\CancellationService;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected PaymentService $paymentService;
    protected CancellationService $cancellationService;
    protected WalletService $walletService;

    public function __construct(
        BookingService $bookingService,
        PaymentService $paymentService,
        CancellationService $cancellationService,
        WalletService $walletService
    ) {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->cancellationService = $cancellationService;
        $this->walletService = $walletService;
    }

    #[OA\Get(
        path: '/api/v1/user/bookings',
        operationId: 'listUserBookings',
        summary: 'List user bookings',
        description: 'Get a paginated list of bookings for the authenticated user.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        responses: [
            new OA\Response(response: 200, description: 'Bookings retrieved successfully')
        ]
    )]
    public function index(Request $request)
    {
        $bookings = $this->bookingService->listForUser($request->user()->id);

        $bookings->getCollection()->transform(function ($booking) {
            return $booking->getUserBookingArray();
        });

        return ApiResponse::success('Bookings retrieved successfully.', [
            'bookings' => $bookings,
        ]);
    }

    #[OA\Get(
        path: '/api/v1/user/bookings/{booking_id}',
        operationId: 'showUserBooking',
        summary: 'Show booking details',
        description: 'Get details of a specific booking with sessions and nurse info.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking details'),
            new OA\Response(response: 404, description: 'Not found')
        ]
    )]
    public function show(Request $request, int $bookingId)
    {
        $booking = $this->bookingService->getBookingForUser($bookingId, $request->user()->id);

        return ApiResponse::success('Booking retrieved successfully.', [
            'booking' => $booking->getUserBookingDetailArray(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/user/bookings/select-bid',
        operationId: 'selectUserBid',
        summary: 'Select a bid and create booking',
        description: 'Selects a nurse bid to create a booking (pending payment).',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['care_request_id', 'bid_id'],
                properties: [
                    new OA\Property(property: 'care_request_id', type: 'integer', example: 1),
                    new OA\Property(property: 'bid_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Booking created'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Invalid state')
        ]
    )]
    public function selectBid(Request $request)
    {
        $request->validate([
            'care_request_id' => 'required|integer',
            'bid_id' => 'required|integer',
        ]);

        $booking = $this->bookingService->createFromBid(
            $request->care_request_id,
            $request->bid_id,
            $request->user()->id
        );

        return ApiResponse::success('Bid selected. Booking created. Please proceed with payment.', [
            'booking' => $booking,
        ], 201);
    }

    #[OA\Post(
        path: '/api/v1/user/bookings/{booking_id}/pay',
        operationId: 'initiateBookingPayment',
        summary: 'Initiate payment for booking',
        description: 'Calculates wallet/gateway split. If wallet covers full amount, booking is confirmed immediately. Otherwise returns gateway order details for frontend.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payment initiated or completed'),
            new OA\Response(response: 402, description: 'Payment error'),
            new OA\Response(response: 409, description: 'Booking not awaiting payment')
        ]
    )]
    public function initiatePayment(Request $request, int $bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $result = $this->paymentService->initiatePayment($booking, $request->user()->id);

        $message = $result['payment_completed']
            ? 'Payment completed. Booking confirmed.'
            : 'Gateway order created. Complete payment on the payment page.';

        // Generate sessions if payment was completed via wallet
        if ($result['payment_completed']) {
            $this->bookingService->generateSessions($result['booking']);
        }

        return ApiResponse::success($message, $result);
    }

    #[OA\Post(
        path: '/api/v1/user/bookings/{booking_id}/confirm-payment',
        operationId: 'confirmGatewayPayment',
        summary: 'Confirm gateway payment',
        description: 'Called after user completes payment on Razorpay/Stripe. Verifies signature, debits wallet portion, confirms booking, generates sessions.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['gateway_payment_id', 'gateway_signature'],
                properties: [
                    new OA\Property(property: 'gateway_payment_id', type: 'string', example: 'pay_abc123xyz'),
                    new OA\Property(property: 'gateway_signature', type: 'string', example: 'sig_hash_here'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Payment confirmed'),
            new OA\Response(response: 402, description: 'Payment verification failed'),
            new OA\Response(response: 409, description: 'Invalid state')
        ]
    )]
    public function confirmPayment(Request $request, int $bookingId)
    {
        $request->validate([
            'gateway_payment_id' => 'required|string',
            'gateway_signature' => 'required|string',
        ]);

        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $confirmedBooking = $this->paymentService->confirmGatewayPayment(
            $booking,
            $request->gateway_payment_id,
            $request->gateway_signature,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );

        // Generate sessions after successful payment
        $this->bookingService->generateSessions($confirmedBooking);

        return ApiResponse::success('Payment confirmed. Booking is now active.', [
            'booking' => $confirmedBooking->fresh(['sessions']),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/user/bookings/{booking_id}/cancel',
        operationId: 'cancelUserBooking',
        summary: 'Cancel a booking',
        description: 'Cancels booking with slab-based refund. User chooses refund mode: 1 = Wallet, 2 = Bank Account.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: 'Change of plans'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Booking cancelled'),
            new OA\Response(response: 409, description: 'Cannot be cancelled')
        ]
    )]
    public function cancel(Request $request, int $bookingId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->cancellationService->cancelByUser(
            $bookingId,
            $request->user()->id,
            $request->reason
        );

        return ApiResponse::success('Booking cancelled successfully.', $result);
    }


    #[OA\Get(
        path: '/api/v1/user/sessions/today',
        operationId: 'todayUserSessions',
        summary: 'Get today\'s sessions',
        description: 'Returns a list of today\'s sessions for the user with OTPs.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        responses: [
            new OA\Response(response: 200, description: 'Today\'s sessions retrieved')
        ]
    )]
    public function todaySessions(Request $request)
    {
        $sessions = BookingSession::whereHas('booking', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_ACTIVE]);
        })
            ->where('session_date', now()->format('Y-m-d'))
            ->with(['booking.careRequest.careType', 'booking.nurse.user'])
            ->get();

        // Make OTPs visible
        $sessions->makeVisible(['start_otp', 'end_otp']);

        $formattedSessions = $sessions->map(function ($session) {
            return $session->getUserSessionArray();
        });

        return ApiResponse::success('Today\'s sessions retrieved successfully.', [
            'sessions' => $formattedSessions
        ]);
    }

    #[OA\Get(
        path: '/api/v1/user/bookings/{booking_id}/sessions',
        operationId: 'listUserBookingSessions',
        summary: 'List sessions for a booking',
        description: 'Returns all sessions for a specific booking.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
        parameters: [
            new OA\Parameter(name: 'booking_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sessions retrieved')
        ]
    )]
    public function listSessions(Request $request, int $bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $sessions = $booking->sessions()->orderBy('session_date')->get();

        $formattedSessions = $sessions->map(function ($session) {
            return $session->getUserSessionArray();
        });

        return ApiResponse::success('Sessions retrieved successfully.', [
            'sessions' => $formattedSessions
        ]);
    }

    #[OA\Get(
        path: '/api/v1/user/bookings/{booking_id}/sessions/{session_id}',
        operationId: 'showUserBookingSession',
        summary: 'Show particular session details',
        description: 'Returns details of a specific session. Includes OTPs if the session is today.',
        security: [['bearerAuth' => []]],
        tags: ['User Bookings'],
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
        $session = BookingSession::where('booking_id', $bookingId)
            ->where('id', $sessionId)
            ->whereHas('booking', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        if ($session->session_date->isToday()) {
            $session->makeVisible(['start_otp', 'end_otp']);
        }

        return ApiResponse::success('Session details retrieved successfully.', [
            'session' => $session->getUserSessionArray()
        ]);
    }


}
