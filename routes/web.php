<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BidController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\NurseController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\System\ErrroLogsController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SupportController;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');

});

Route::get('/', function () {
    return auth()->guard('web')->check() ? redirect('/admin') : redirect('/login');
});

Route::prefix('admin')->name('admin.')->middleware(['auth:web', 'admin'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Nurses
    Route::prefix('nurses')->name('nurses.')->group(function () {

        Route::get('/', [NurseController::class, 'index'])->name('index');
        Route::get('/data', [NurseController::class, 'indexData'])->name('data');
        
        Route::get('/create', [NurseController::class, 'create'])->name('create');
        Route::post('/store', [NurseController::class, 'store'])->name('store');

        Route::get('/pending_approval', [NurseController::class, 'pending'])->name('pending_approval');
        Route::get('/pending_approval/data', [NurseController::class, 'pendingData'])->name('pending_approval.data');

        Route::get('/approved', [NurseController::class, 'approved'])->name('approved');
        Route::get('/approved/data', [NurseController::class, 'approvedData'])->name('approved.data');

        Route::get('/rejected', [NurseController::class, 'rejected'])->name('rejected');
        Route::get('/rejected/data', [NurseController::class, 'rejectedData'])->name('rejected.data');
        Route::get('/pending-count', [NurseController::class, 'pendingCount'])->name('pending-count');
        Route::get('/deleted', [NurseController::class, 'deleted'])->name('deleted');
        Route::get('/deleted/data', [NurseController::class, 'deletedData'])->name('deleted.data');
        Route::get('/{user}/edit', [NurseController::class, 'edit'])->name('edit');
        Route::post('/{user}/update', [NurseController::class, 'update'])->name('update');
        Route::get('/{user}', [NurseController::class, 'show'])->name('show');
        Route::delete('/{user}', [NurseController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/restore', [NurseController::class, 'restore'])->name('restore');
        Route::get('/{user}/stats', [NurseController::class, 'stats'])->name('stats');
        Route::get('/{user}/application', [NurseController::class, 'showApplication'])->name('show-application');
        Route::get('/{user}/reviews', [NurseController::class, 'reviews'])->name('reviews');
        Route::get('/{user}/reviews/data', [NurseController::class, 'reviewsData'])->name('reviews.data');
        Route::get('/{user}/bids', [NurseController::class, 'bids'])->name('bids');
        Route::get('/{user}/bids/data', [NurseController::class, 'bidsData'])->name('bids.data');
        Route::get('/{user}/bookings', [NurseController::class, 'bookings'])->name('bookings');
        Route::get('/{user}/bookings/data', [NurseController::class, 'bookingsData'])->name('bookings.data');
        Route::get('/{user}/contact-form', [NurseController::class, 'contactForm'])->name('contact-form');
        Route::post('/{user}/contact', [NurseController::class, 'contact'])->name('contact');
        Route::get('/{user}/login-history', [NurseController::class, 'loginHistory'])->name('login-history');
        Route::get('/{user}/login-history/data', [NurseController::class, 'loginHistoryData'])->name('login-history.data');
        Route::get('/{user}/care-requests', [NurseController::class, 'careRequests'])->name('care-requests');
        Route::get('/{user}/care-requests/data', [NurseController::class, 'careRequestsData'])->name('care-requests.data');
        Route::get('/{user}/review-step-view/{step}', [NurseController::class, 'getReviewStepView'])->name('review-step-view');
        Route::post('/{user}/review-step', [NurseController::class, 'reviewStep'])->name('review-step');
        Route::post('/{user}/document-review/{document}', [NurseController::class, 'reviewDocument'])->name('document-review');
        Route::post('/{user}/finalize-review', [NurseController::class, 'finalizeReview'])->name('finalize-review');
        
        // Status Control
        Route::post('/{user}/status', [NurseController::class, 'updateStatus'])->name('status.update');

        // Edit profile sections
        Route::post('/{user}/education', [NurseController::class, 'storeEducation'])->name('education.store');
        Route::delete('/{user}/education/{id}', [NurseController::class, 'destroyEducation'])->name('education.destroy');
        Route::post('/{user}/experience', [NurseController::class, 'storeExperience'])->name('experience.store');
        Route::delete('/{user}/experience/{id}', [NurseController::class, 'destroyExperience'])->name('experience.destroy');
        Route::post('/{user}/documents', [NurseController::class, 'storeDocument'])->name('documents.store');
        Route::delete('/{user}/documents/{id}', [NurseController::class, 'destroyDocument'])->name('documents.destroy');
        
        // Secure document download route
        Route::get('/documents/{document}', [NurseController::class, 'document'])->name('document');
    });

    // PEOPLE — Patients
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('data', [PatientController::class, 'data'])->name('data');
        Route::get('create', [PatientController::class, 'create'])->name('create');
        Route::post('store', [PatientController::class, 'store'])->name('store');

        Route::get('blocked', [PatientController::class, 'blocked'])->name('blocked');
        Route::get('blocked/data', [PatientController::class, 'blockedData'])->name('blocked.data');
        Route::get('deleted', [PatientController::class, 'deleted'])->name('deleted');
        Route::get('deleted/data', [PatientController::class, 'deletedData'])->name('deleted.data');
        Route::post('{patient}/unblock', [PatientController::class, 'unblock'])->name('unblock');

        Route::get('{patient}', [PatientController::class, 'show'])->name('show');
        Route::get('{patient}/stats', [PatientController::class, 'stats'])->name('stats');
        Route::get('{patient}/requests', [PatientController::class, 'requests'])->name('requests');
        Route::get('{patient}/requests/data', [PatientController::class, 'requestsData'])->name('requests.data');
        Route::get('{patient}/bookings', [PatientController::class, 'bookings'])->name('bookings');
        Route::get('{patient}/bookings/data', [PatientController::class, 'bookingsData'])->name('bookings.data');
        Route::get('{patient}/login-history', [PatientController::class, 'loginHistory'])->name('login-history');
        Route::get('{patient}/login-history/data', [PatientController::class, 'loginHistoryData'])->name('login-history.data');
        Route::get('{patient}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::post('{patient}', [PatientController::class, 'update'])->name('update');
        Route::delete('{patient}', [PatientController::class, 'destroy'])->name('destroy');
        Route::post('{patient}/status', [PatientController::class, 'updateStatus'])->name('status');
        Route::post('{patient}/restore', [PatientController::class, 'restore'])->name('restore');
    });

    // PEOPLE — Login History
    Route::prefix('login-history')->name('login-history.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LoginHistoryController::class, 'index'])->name('index');
        Route::get('data', [LoginHistoryController::class, 'data'])->name('data');
        Route::post('empty', [\App\Http\Controllers\Admin\LoginHistoryController::class, 'empty'])->name('empty');
        Route::get('{id}', [\App\Http\Controllers\Admin\LoginHistoryController::class, 'show'])->name('show');
    });

    // Care Request
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RequestController::class, 'index'])->name('index');
        Route::get('/data', [RequestController::class, 'data'])->name('data');
        Route::get('today', [RequestController::class, 'todayIndex'])->name('today');
        Route::get('{request}/bids-data', [RequestController::class, 'bidsData'])->name('bids-data');
        Route::get('{request}/notified-nurses-data', [RequestController::class, 'notifiedNursesData'])->name('notified-nurses-data');
        Route::get('{request}', [\App\Http\Controllers\Admin\RequestController::class, 'show'])->name('show');
        Route::delete('{request}', [RequestController::class, 'destroy'])->name('destroy');
    });

    // Care
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('index');
        Route::get('/data', [BookingController::class, 'data'])->name('data');
        Route::get('active', [\App\Http\Controllers\Admin\BookingController::class, 'active'])->name('active');
        Route::get('active/data', [BookingController::class, 'activeData'])->name('active-data');
        Route::get('cancelled', [\App\Http\Controllers\Admin\BookingController::class, 'cancelled'])->name('cancelled');
        Route::get('cancelled/data', [BookingController::class, 'cancelledData'])->name('cancelled-data');
        
        Route::get('{booking}/sessions-data', [BookingController::class, 'sessionsData'])->name('sessions-data');
        Route::get('{booking}/payment-logs-data', [BookingController::class, 'paymentLogsData'])->name('payment-logs-data');
        Route::get('{booking}/reviews-data', [BookingController::class, 'reviewsData'])->name('reviews-data');
        Route::get('{booking}/bids-data', [BookingController::class, 'bidsData'])->name('bids-data');
        
        Route::get('{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('show');
    });

    // OPERATIONS — Bids
    Route::prefix('bids')->name('bids.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BidController::class, 'index'])->name('index');
        Route::get('/data', [BidController::class, 'data'])->name('data');
        Route::get('today', [\App\Http\Controllers\Admin\BidController::class, 'today'])->name('today');
        Route::get('today/data', [BidController::class, 'todayData'])->name('today.data');
        Route::get('active', [\App\Http\Controllers\Admin\BidController::class, 'active'])->name('active');
        Route::get('active/data', [BidController::class, 'activeData'])->name('active.data');

        Route::get('{bid}', [\App\Http\Controllers\Admin\BidController::class, 'show'])->name('show');
    });

    // OPERATIONS — Services
    Route::prefix('services')->name('services.')->group(function () {
        Route::resource('care-types', \App\Http\Controllers\Admin\CareTypeController::class);
    });

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('transactions', [\App\Http\Controllers\Admin\PaymentController::class, 'transactions'])->name('transactions');
        Route::get('transactions/data', [\App\Http\Controllers\Admin\PaymentController::class, 'transactionsData'])->name('transactions-data');
        
        Route::get('payouts', [\App\Http\Controllers\Admin\PaymentController::class, 'payouts'])->name('payouts');
        Route::get('payouts/data', [\App\Http\Controllers\Admin\PaymentController::class, 'payoutsData'])->name('payouts-data');
        
        Route::get('refunds', [\App\Http\Controllers\Admin\PaymentController::class, 'refunds'])->name('refunds');
        Route::get('refunds/data', [\App\Http\Controllers\Admin\PaymentController::class, 'refundsData'])->name('refunds-data');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('revenue', function () {
            echo "Revenue Reports";
        })->name('revenue');
        Route::get('nurse-activity', function () {
            echo "Nurse Activity";
        })->name('nurse-activity');
        Route::get('requests', function () {
            echo "Request Reports";
        })->name('requests');
    });

    // Comments
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');



    // System
    Route::prefix('system')->name('system.')->group(function () {

        Route::get('error-logs', [ErrroLogsController::class, 'index'])->name('error-logs');
        Route::get('error-logs/data', [ErrroLogsController::class, 'data'])->name('errors.data');
        Route::post('error-logs/empty', [ErrroLogsController::class, 'empty'])->name('errors.empty');
        Route::get('error-logs/pending-count', [ErrroLogsController::class, 'pendingCount'])->name('errors.pending-count');
        Route::post('error-logs/{id}/status', [ErrroLogsController::class, 'status'])->name('errors.status');
        Route::get('error-logs/{id}', [ErrroLogsController::class, 'show'])->name('errors.show');
        
        Route::prefix('communication-logs')->name('communication-logs.')->group(function() {
            Route::get('/', [\App\Http\Controllers\Admin\System\CommunicationLogController::class, 'index'])->name('index');
            Route::get('data', [\App\Http\Controllers\Admin\System\CommunicationLogController::class, 'data'])->name('data');
            Route::post('empty', [\App\Http\Controllers\Admin\System\CommunicationLogController::class, 'empty'])->name('empty');
        });


        Route::get('failed-jobs', function () {
            echo "Failed Jobs";
        })->name('failed-jobs');
        Route::get('queue', function () {
            echo "Queue Monitor";
        })->name('queue');
        Route::get('backups', function () {
            echo "Backups";
        })->name('backups');
    });

    // support ticket
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/pending-count', [\App\Http\Controllers\Admin\SupportController::class, 'pendingCount'])->name('pending-count');

        // Categories
        Route::get('/categories', [\App\Http\Controllers\Admin\SupportCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [\App\Http\Controllers\Admin\SupportCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [\App\Http\Controllers\Admin\SupportCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\SupportCategoryController::class, 'destroy'])->name('categories.destroy');

        // Faqs
        Route::get('/faqs/data', [\App\Http\Controllers\Admin\Support\FaqController::class, 'data'])->name('faqs.data');
        Route::resource('/faqs', \App\Http\Controllers\Admin\Support\FaqController::class)->except(['show']);

        Route::get('/', [\App\Http\Controllers\Admin\SupportController::class, 'index'])->name('index');
        Route::get('/data', [\App\Http\Controllers\Admin\SupportController::class, 'data'])->name('data');
        Route::get('/{id}', [\App\Http\Controllers\Admin\SupportController::class, 'show'])->name('show');
        Route::post('/{id}/reply', [\App\Http\Controllers\Admin\SupportController::class, 'reply'])->name('reply');
        Route::post('/{id}/status', [\App\Http\Controllers\Admin\SupportController::class, 'updateStatus'])->name('update-status');
    });

    // SYSTEM — Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('general', [\App\Http\Controllers\Admin\SettingController::class, 'general'])->name('general');
        Route::post('general', [\App\Http\Controllers\Admin\SettingController::class, 'updateGeneral'])->name('general.update');
        
        Route::get('app', function () {
            echo "App Config";
        })->name('app');
        Route::get('roles', function () {
            echo "Roles & Permissions";
        })->name('roles');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');

});