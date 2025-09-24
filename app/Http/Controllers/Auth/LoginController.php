<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Default redirect after login (will be overridden in redirectTo()).
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
        $this->middleware('throttle:3,1')->only('login');
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);

        // Retrieve user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        if (!$user->status) {
            throw ValidationException::withMessages([
                'email' => [trans('Your account access is disabled')],
            ]);
        }

        return $this->guard()->attempt(
            $credentials,
            $request->filled('remember')
        );
    }

    /**
     * Redirect users after login based on role.
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user->hasRole('member')) {
            return '/'; // frontend dashboard/profile
        }

        // default for admin, employee, moderator
        return '/dashboard'; // backend
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->hasRole('member')) {
            session()->flash('login_success', 'Hai ' . $user->name . ', selamat datang kembali!');
            return redirect()->route('home');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? response()->json([], 204)
            : redirect()->route('login');
    }
}