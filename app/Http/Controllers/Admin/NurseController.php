<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\DataTables\Nurses\AllNursesDataTable;
use App\DataTables\Nurses\UnderReviewNurseDataTable;
use App\DataTables\Nurses\ApprovedNursesDataTable;
use App\DataTables\Nurses\RejectedNursesDataTable;
use App\DataTables\Nurses\DeletedNurseDataTable;
use App\Http\Requests\Admin\StoreNurseRequest;
use App\Http\Requests\Admin\UpdateNurseRequest;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\CareType;
use App\Models\NurseEducation;
use App\Models\NurseWorkHistory;
use App\Models\LoginHistory;
use App\Models\NurseDocument;
use App\Models\NurseProfile;
use App\Models\NurseProfileVerification;
use App\Models\NurseRequestCache;
use App\Models\NurseReview;
use App\Models\User;
use App\Models\RequestBid;
use App\Notifications\WelcomeNurseNotification;
use App\Services\Admin\NurseService;
use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NurseController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService,
        private NurseService $nurseService
    ) {
    }
    // ── All ───────────────────────────────────────────────
    public function create()
    {
        $careTypes = CareType::where('status', 1)->get();
        return view('admin.nurses.create', compact('careTypes'));
    }

    public function store(StoreNurseRequest $request)
    {
        try {
            $user = $this->nurseService->createNurse($request->validated());

            return redirect()->route('admin.nurses.show', $user->id)
                ->with('success', 'Nurse created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create nurse: ' . $e->getMessage());
        }
    }

    public function index(AllNursesDataTable $dt)
    {
        return $dt->render('admin.nurses.index');
    }

    public function indexData(AllNursesDataTable $dt)
    {
        return $dt->ajax();
    }

    // ── Pending Approval ───────────────────────────────────
    public function pending(UnderReviewNurseDataTable $dt)
    {
        return $dt->render('admin.nurses.pending_approval');
    }

    public function pendingData(UnderReviewNurseDataTable $dt)
    {
        return $dt->ajax();
    }

    // ── Approved ──────────────────────────────────────────
    public function approved(ApprovedNursesDataTable $dt)
    {
        return $dt->render('admin.nurses.approved');
    }

    public function approvedData(ApprovedNursesDataTable $dt)
    {
        return $dt->ajax();
    }

    // ── Rejected ──────────────────────────────────────────
    public function rejected(RejectedNursesDataTable $dt)
    {
        return $dt->render('admin.nurses.rejected');
    }

    public function rejectedData(RejectedNursesDataTable $dt)
    {
        return $dt->ajax();
    }

    public function pendingCount()
    {
        $count = NurseProfile::where('status', NurseProfile::STATUS_UNDER_REVIEW)->count();
        return response()->json(['count' => $count]);
    }

    // ── Show Profile ──────────────────────────────────────
    public function show($id)
    {
        $user = User::with(['nurseProfile.verifications', 'nurseProfile.careTypes'])->findOrFail($id);

        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        $profile = $user->nurseProfile;

        // Route based on status
        if (in_array($profile->status, [NurseProfile::STATUS_APPROVED, NurseProfile::STATUS_SUSPENDED])) {
            return view('admin.nurses.show-approved', compact('user', 'profile'));
        } elseif ($profile->status === NurseProfile::STATUS_UNDER_REVIEW) {
            return view('admin.nurses.show-review', compact('user', 'profile'));
        } elseif ($profile->status === NurseProfile::STATUS_REJECTED) {
            return view('admin.nurses.show-review', compact('user', 'profile'));
        } else {
            // PENDING
            return view('admin.nurses.show-pending', compact('user', 'profile'));
        }
    }

    // ── Edit Profile ──────────────────────────────────────
    public function edit($id)
    {
        $user = User::with(['nurseProfile', 'nurseProfile.careTypes'])->findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        $careTypes = CareType::where('status', 1)->get();

        return view('admin.nurses.edit', compact('user', 'careTypes'));
    }

    // ── Update Profile ────────────────────────────────────
    public function update(UpdateNurseRequest $request, $id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        try {
            $this->nurseService->updateNurse($user, $request->validated());

            try {
                ActivityLogger::log(
                    Activity::ACTION_UPDATED,
                    'Admin updated nurse profile details.',
                    $user->nurseProfile
                );
            } catch (\Exception $e) {
                // Ignore logging errors to ensure the main transaction remains stable
            }

            return redirect()->route('admin.nurses.edit', $user->id)
                ->with('success', 'Nurse profile updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update nurse profile. Please try again.');
        }
    }

    // ── Delete Profile ────────────────────────────────────
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse(), 404, 'Nurse not found.');

        try {
            // Because User has SoftDeletes trait, this will perform a soft delete.
            $user->delete();
            return response()->json(['success' => true, 'message' => 'Nurse deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete nurse.'], 500);
        }
    }

    // ── Deleted Nurses (Trash) ────────────────────────────
    public function deleted(DeletedNurseDataTable $dt)
    {
        return $dt->render('admin.nurses.deleted');
    }

    public function deletedData(DeletedNurseDataTable $dt)
    {
        return $dt->ajax();
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        abort_unless($user->isNurse(), 404, 'Nurse not found.');

        try {
            $user->restore();
            return response()->json(['success' => true, 'message' => 'Nurse restored successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to restore nurse.'], 500);
        }
    }

    // ── Update Status ─────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        $request->validate([
            'status' => 'required|integer',
            'reason' => 'nullable|string'
        ]);

        $profile = $user->nurseProfile;

        switch ($request->status) {
            case NurseProfile::STATUS_APPROVED:
                $profile->markAsApproved(auth()->id());
                break;
            case NurseProfile::STATUS_REJECTED:
                $profile->markAsRejected($request->reason);
                break;
            case NurseProfile::STATUS_SUSPENDED:
                $profile->markAsSuspended($request->reason);
                break;
            case NurseProfile::STATUS_PENDING:
                $profile->update(['status' => NurseProfile::STATUS_PENDING]);
                break;
        }

        ActivityLogger::log(
            Activity::ACTION_UPDATED,
            'Admin changed nurse status to ' . NurseProfile::getStatusList()[$request->status],
            $profile,
            ['status' => $request->status, 'reason' => $request->reason]
        );

        return redirect()->back()->with('success', 'Nurse status updated successfully.');
    }

    // ── Edit Education ────────────────────────────────────
    public function storeEducation(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'degree_name' => 'required|string|max:255',
            'institution_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        $validated['nurse_id'] = $user->nurseProfile->id;
        NurseEducation::create($validated);
        
        return redirect()->back()->with('success', 'Education added successfully.');
    }

    public function destroyEducation($id, $educationId)
    {
        $education = NurseEducation::findOrFail($educationId);
        $education->delete();
        
        return redirect()->back()->with('success', 'Education deleted successfully.');
    }

    // ── Edit Experience ───────────────────────────────────
    public function storeExperience(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'hospital_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_currently_working' => 'nullable|boolean',
        ]);
        
        $validated['nurse_id'] = $user->nurseProfile->id;
        $validated['is_currently_working'] = $request->has('is_currently_working');
        
        NurseWorkHistory::create($validated);
        
        return redirect()->back()->with('success', 'Experience added successfully.');
    }

    public function destroyExperience($id, $experienceId)
    {
        $experience = NurseWorkHistory::findOrFail($experienceId);
        $experience->delete();
        
        return redirect()->back()->with('success', 'Experience deleted successfully.');
    }

    // ── Edit Documents ────────────────────────────────────
    public function storeDocument(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        
        $file = $request->file('document');
        $path = $file->store('nurse-documents/' . $user->nurseProfile->id, 'public');
        
        NurseDocument::create([
            'nurse_id' => $user->nurseProfile->id,
            'title' => $request->title,
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'status' => NurseDocument::STATUS_APPROVED,
        ]);
        
        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function destroyDocument($id, $documentId)
    {
        $document = NurseDocument::findOrFail($documentId);
        if (\Storage::disk('public')->exists($document->file_path)) {
            \Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    public function stats($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        $profileId = $user->nurseProfile->id;

        $totalReviews = NurseReview::where('nurse_id', $profileId)->count();
        $avgRating = NurseReview::where('nurse_id', $profileId)->avg('rating') ?? 0;

        $totalBookings = Booking::where('nurse_id', $profileId)->count();
        $completedBookings = Booking::where('nurse_id', $profileId)->where('status', Booking::STATUS_COMPLETED)->count();

        $trustScore = $user->nurseProfile->trust_score ?? 100;
        if ($totalBookings > 0) {
            $trustScore = round(($completedBookings / $totalBookings) * 100);
        }

        return response()->json([
            'avg_rating' => number_format($avgRating, 1),
            'total_reviews' => $totalReviews,
            'trust_score' => $trustScore,
            'jobs_done' => $completedBookings,
        ]);
    }

    public function reviews(Request $request, $id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        return view('admin.nurses.tabs.reviews', compact('user'));
    }

    public function reviewsData($id)
    {
        $user = User::findOrFail($id);

        $reviews = NurseReview::with(['user', 'booking'])
            ->where('nurse_id', $user->nurseProfile->id)
            ->latest();

        return datatables()->of($reviews)
            ->addColumn('user', function ($review) {
                return '<div class="d-flex align-items-center gap-3">
                            <div class="d-flex flex-column">
                                <a href="' . route('admin.patients.show', $review->user->id) . '" class="text-gray-900 text-hover-primary fw-bold fs-7">' . $review->user->name . '</a>
                                <span class="text-gray-500 fs-8">' . $review->user->email . '</span>
                            </div>
                        </div>';
            })
            ->editColumn('booking_id', function ($review) {
                return '<a href="' . route('admin.bookings.show', $review->booking_id) . '" class="text-primary fw-semibold fs-7 hover-underline">#' . $review->booking_id . '</a>';
            })
            ->editColumn('rating', function ($review) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $color = $i <= $review->rating ? 'text-warning' : 'text-gray-300';
                    $stars .= '<i class="ki-solid ki-star fs-6 ' . $color . '"></i>';
                }
                return '<div class="d-flex align-items-center gap-1">' . $stars . '<span class="ms-1 fw-bold text-gray-800 fs-7">' . $review->rating . '.0</span></div>';
            })
            ->editColumn('review', function ($review) {
                if ($review->review) {
                    return '<span class="text-gray-700 fs-7">' . htmlspecialchars($review->review) . '</span>';
                }
                return '<span class="text-gray-400 fs-7 fst-italic">No text review</span>';
            })
            ->editColumn('created_at', function ($review) {
                return '<span class="text-gray-600 fs-7">' . $review->created_at->format('d M Y') . '<br><span class="fs-8 text-gray-500">' . $review->created_at->format('h:i A') . '</span></span>';
            })
            ->rawColumns(['user', 'booking_id', 'rating', 'review', 'created_at'])
            ->make(true);
    }

    public function bids($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        return view('admin.nurses.tabs.bids', compact('user'));
    }

    public function bidsData($id)
    {
        $user = User::findOrFail($id);

        $bids = RequestBid::with('careRequest')->where('nurse_id', $user->id)->latest();

        return datatables()->of($bids)
            ->addColumn('request', function ($bid) {
                return '<a href="' . route('admin.requests.show', $bid->care_request_id) . '" class="text-primary fw-bold text-hover-primary mb-1 fs-6">#' . $bid->care_request_id . '</a>';
            })
            ->editColumn('amount', function ($bid) {
                return '<span class="fw-bold text-success fs-6">$' . number_format($bid->total_amount, 2) . '</span>';
            })
            ->editColumn('status', function ($bid) {
                $statusColor = $bid->status_color;
                $statusText = $bid->status_text;

                return '<span class="badge badge-light-' . $statusColor . '">' . $statusText . '</span>';
            })
            ->editColumn('created_at', function ($bid) {
                return '<span class="text-gray-600 fs-7">' . $bid->created_at->format('d M Y') . '<br><span class="fs-8 text-gray-500">' . $bid->created_at->format('h:i A') . '</span></span>';
            })
            ->rawColumns(['request', 'amount', 'status', 'created_at'])
            ->make(true);
    }

    public function careRequests($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        return view('admin.nurses.tabs.care-requests', compact('user'));
    }

    public function careRequestsData($id)
    {
        $user = User::findOrFail($id);
        $profileId = $user->nurseProfile->id;

        $requests = NurseRequestCache::with(['careRequest', 'careRequest.user'])
            ->where('nurse_id', $profileId)
            ->whereIn('status', [
                NurseRequestCache::STATUS_NOTIFIED,
                NurseRequestCache::STATUS_VIEWED
            ])
            ->where('expires_at', '>', now())
            ->latest();

        return datatables()->of($requests)
            ->addColumn('request', function ($cache) {
                return '<a href="' . route('admin.requests.show', $cache->care_request_id) . '" class="text-primary fw-bold text-hover-primary mb-1 fs-6">#' . $cache->care_request_id . '</a>';
            })
            ->editColumn('status', function ($cache) {
                $color = $cache->status_color;
                $text = $cache->status_text;
                return '<span class="badge badge-light-' . $color . '">' . $text . '</span>';
            })
            ->addColumn('patient', function ($cache) {
                $patient = $cache->careRequest->user ?? null;
                if ($patient) {
                    return '<div class="d-flex align-items-center gap-3">
                                <div class="d-flex flex-column">
                                    <a href="' . route('admin.patients.show', $patient->id) . '" class="text-gray-900 text-hover-primary fw-bold fs-7">' . htmlspecialchars($patient->name) . '</a>
                                </div>
                            </div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->editColumn('expires_at', function ($cache) {
                return '<span class="text-gray-600 fs-7">' . Carbon::parse($cache->expires_at)->format('d M Y') . '<br><span class="fs-8 text-gray-500">' . Carbon::parse($cache->expires_at)->format('h:i A') . '</span></span>';
            })
            ->editColumn('created_at', function ($cache) {
                return '<span class="text-gray-600 fs-7">' . $cache->created_at->format('d M Y') . '<br><span class="fs-8 text-gray-500">' . $cache->created_at->format('h:i A') . '</span></span>';
            })
            ->rawColumns(['request', 'patient', 'status', 'expires_at', 'created_at'])
            ->make(true);
    }

    public function showApplication($id)
    {
        $user = User::with('nurseProfile')->findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');
        $profile = $user->nurseProfile;

        return view('admin.nurses.show-review', compact('user', 'profile'));
    }

    // ── Review Processing ─────────────────────────────────
    public function reviewStep(Request $request, $id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        $request->validate([
            'step_id' => 'required|integer',
            'status' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        if ($request->step_id == NurseProfile::STEP_DOCUMENTS && $request->status == NurseProfileVerification::STATUS_APPROVED) {
            $unapprovedDocumentsCount = NurseDocument::where('nurse_id', $user->nurseProfile->id)
                ->where('status', '!=', NurseDocument::STATUS_APPROVED)
                ->count();

            if ($unapprovedDocumentsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must approve all individual documents before approving the entire documents section.'
                ]);
            }
        }

        $this->onboardingService->reviewStep($user, $request->step_id, $request->status, $request->reason);

        return response()->json(['success' => true, 'message' => 'Step verification updated.']);
    }

    public function reviewDocument(Request $request, $id, $documentId)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'status' => 'required|in:1,2'
        ]);

        $document = NurseDocument::where('nurse_id', $user->nurseProfile->id)->findOrFail($documentId);

        $document->update([
            'status' => $request->status,
            'reviewed_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Document status updated.']);
    }

    public function document($documentId)
    {
        $document = NurseDocument::findOrFail($documentId);
        
        if (!\Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document not found on server.');
        }

        return \Storage::disk('public')->response($document->file_path);
    }

    public function getReviewStepView(Request $request, $id, $step)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');
        $profile = $user->nurseProfile;

        if ($step === 'final') {
            return view('admin.nurses.review-steps.final', compact('user', 'profile'));
        }

        $stepId = (int) $step;
        $sectionData = $this->onboardingService->getStepData($user, $stepId);

        $verification = $profile->verifications->where('step_id', $stepId)->first();
        $status = $verification ? $verification->status : NurseProfileVerification::STATUS_PENDING;

        $viewMap = [
            NurseProfile::STEP_BASIC_PROFILE => 'basic_profile',
            NurseProfile::STEP_CARE_TYPES => 'care_types',
            NurseProfile::STEP_EDUCATION => 'education',
            NurseProfile::STEP_WORK_HISTORY => 'work_history',
            NurseProfile::STEP_DOCUMENTS => 'documents',
        ];

        $viewName = $viewMap[$stepId] ?? 'basic_profile';

        $isReadOnly = $request->query('readonly', 0) == 1;

        return view('admin.nurses.review-steps.' . $viewName, compact('user', 'profile', 'stepId', 'sectionData', 'status', 'verification', 'isReadOnly'));
    }

    public function finalizeReview(Request $request, $id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        $request->validate([
            'status' => 'required|integer',
            'reason' => 'nullable|string',
            'can_reapply' => 'nullable|boolean',
        ]);

        $this->onboardingService->finalizeReview($user, $request->status, $request->reason, $request->can_reapply);

        $actionType = $request->status == \App\Models\NurseProfile::STATUS_APPROVED
            ? \App\Models\Activity::ACTION_APPROVED
            : \App\Models\Activity::ACTION_REJECTED;

        \App\Helpers\ActivityLogger::log(
            $actionType,
            'Admin finalized application review.',
            $user->nurseProfile,
            [
                'status' => $request->status,
                'reason' => $request->reason
            ]
        );

        return response()->json(['success' => true, 'message' => 'Application review finalized.']);
    }

    public function bookings($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        return view('admin.nurses.tabs.bookings', compact('user'));
    }

    public function bookingsData($id)
    {
        $user = User::findOrFail($id);
        $profile = $user->nurseProfile;

        $bookings = Booking::with(['user'])
            ->where('nurse_id', $profile->id)
            ->latest();

        return datatables()->of($bookings)
            ->editColumn('reference_id', function ($booking) {
                return '<a href="' . route('admin.bookings.show', $booking->id) . '" class="text-primary fw-bold text-hover-primary mb-1 fs-6">#' . $booking->reference_id . '</a>';
            })
            ->editColumn('user', function ($booking) {
                if (!$booking->user)
                    return '<span class="text-muted">Unassigned</span>';
                $patientUser = $booking->user;
                $img = $patientUser->avatar_html;
                return '
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-30px symbol-circle">' . $img . '</div>
                    <a href="' . route('admin.patients.show', $patientUser->id) . '" class="text-gray-900 text-hover-primary fw-bold fs-7">' . $patientUser->name . '</a>
                </div>';
            })
            ->editColumn('status', function ($booking) {
                $color = $booking->status_color;
                return '<span class="badge badge-light-' . $color . ' border border-' . $color . '">' . $booking->status_text . '</span>';
            })
            ->editColumn('payment_status', function ($booking) {
                $color = $booking->payment_status_color;
                return '<span class="badge badge-light-' . $color . ' border border-' . $color . '">' . $booking->payment_status_text . '</span>';
            })
            ->editColumn('total_amount', function ($booking) {
                return '<span class="fw-bold text-success">₹' . number_format($booking->total_amount, 2) . '</span>';
            })
            ->editColumn('created_at', function ($booking) {
                return '<span class="text-gray-600 fs-7">' . $booking->created_at->format('d M Y') . '</span>';
            })
            ->rawColumns(['reference_id', 'user', 'status', 'payment_status', 'total_amount', 'created_at'])
            ->make(true);
    }

    public function loginHistory($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        return view('admin.nurses.tabs.login-history', compact('user'));
    }

    public function loginHistoryData($id)
    {
        $user = User::findOrFail($id);

        $logins = LoginHistory::where('user_id', $user->id)->latest();

        return datatables()->of($logins)
            ->editColumn('ip_address', function ($login) {
                return '<span class="fw-bold text-gray-800">' . $login->ip_address . '</span>';
            })
            ->editColumn('user_agent', function ($login) {
                $icon = 'ki-screen';
                if (stripos($login->user_agent, 'mobile') !== false || stripos($login->user_agent, 'android') !== false || stripos($login->user_agent, 'iphone') !== false) {
                    $icon = 'ki-phone';
                }
                return '<div class="d-flex align-items-center"><i class="ki-outline ' . $icon . ' fs-3 me-2 text-primary"></i><span class="text-truncate d-inline-block" style="max-width:250px;" title="' . htmlspecialchars($login->user_agent) . '">' . Str::limit($login->user_agent, 40) . '</span></div>';
            })
            ->editColumn('created_at', function ($login) {
                return '<span class="text-gray-600 fs-7">' . $login->created_at->format('d M Y, h:i A') . '</span>';
            })
            ->addColumn('action', function ($login) {
                return '<a href="' . route('admin.login-history.show', $login->id) . '" class="btn btn-sm btn-icon btn-light-info border border-info"><i class="ti ti-eye fs-4"></i></a>';
            })
            ->rawColumns(['ip_address', 'user_agent', 'created_at', 'action'])
            ->make(true);
    }

    public function contactForm($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        return view('admin.nurses._contact', compact('user'));
    }

    public function contact(Request $request, $id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->isNurse() && $user->nurseProfile, 404, 'Nurse profile not found.');

        $request->validate([
            'channel' => 'required|in:email,sms',
            'subject' => 'required_if:channel,email|nullable|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $content = $request->message;
            if ($request->channel === 'email' && $request->subject) {
                $content = "Subject: " . $request->subject . "\n\n" . $content;
            }

            \App\Models\CommunicationLog::create([
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'channel' => $request->channel,
                'type' => 'manual',
                'destination' => $request->channel === 'email' ? $user->email : $user->phone,
                'content' => $content,
                'status' => 'sent', // Currently just logging
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->channel) . ' logged and sent successfully to ' . $user->name,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process message: ' . $e->getMessage()
            ], 500);
        }
    }
}