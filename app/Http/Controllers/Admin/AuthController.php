<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\Activity;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin()
    {

        if (auth()->guard('web')->check() && auth()->user()->status == User::STATUS_ACTIVE) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            
            if ($user = User::where('email', $request->email)->first()) {
                $this->logLoginAttempt($user->id, $request, LoginHistory::STATUS_INACTIVE);
            }

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::user();

        // Role check
        if (!$user->isAdmin()) {
            Auth::logout();
            $this->logLoginAttempt($user->id, $request, LoginHistory::STATUS_INACTIVE);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'You do not have admin access.']);
        }

        // Status check
        if (!$user->isActive()) {
            Auth::logout();
            $this->logLoginAttempt($user->id, $request, LoginHistory::STATUS_INACTIVE);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Your account is inactive or blocked.']);
        }

        $user->update(['last_login_at' => now()]);

        $this->logLoginAttempt($user->id, $request, LoginHistory::STATUS_ACTIVE);

        ActivityLogger::log(
            Activity::ACTION_LOGIN,
            'Admin logged in via Web.',
            $user,
            ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]
        );

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    private function logLoginAttempt($userId, Request $request, $status)
    {
        LoginHistory::create([
            'user_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_in_at' => now(),
            'status' => $status,
        ]);
    }


    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogger::log(
                Activity::ACTION_LOGOUT,
                'Admin logged out via Web.',
                $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}