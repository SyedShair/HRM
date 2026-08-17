<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * SHOW LOGIN FORM
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * LOGIN
     */
    public function login(Request $request)
    {

        /*
        =========================================
        EMAIL + PASSWORD LOGIN
        =========================================
        */

        if ($request->filled('email') && $request->filled('password')) {

            $credentials = [
                'email'    => $request->email,
                'password' => $request->password,
                'status'   => 1
            ];

            if (Auth::attempt($credentials)) {

                $request->session()->regenerate();

                return $this->redirectUser(Auth::user());
            }

            return back()->withErrors([
                'login' => 'Invalid email or password'
            ]);
        }

        /*
        =========================================
        NATIONAL ID + DOB LOGIN
        =========================================
        */

        if ($request->filled('nationalid') && $request->filled('birthday')) {

            // FIND PERSON

            $person = DB::table('tbl_people')
                ->where('nationalid', $request->nationalid)
                ->whereDate('birthday', $request->birthday)
                ->first();

            if (!$person) {

                return back()->withErrors([
                    'login' => 'Invalid National ID or Date of Birth'
                ]);
            }

            // FIND USER ACCOUNT

            $user = DB::table('users')
                ->where('reference', $person->id)
                ->where('status', 1)
                ->first();

            if (!$user) {

                return back()->withErrors([
                    'login' => 'No active user account found'
                ]);
            }

            // LOGIN

            Auth::loginUsingId($user->id);

            return $this->redirectUser(Auth::user());
        }

        /*
        =========================================
        NO LOGIN DATA
        =========================================
        */

        return back()->withErrors([
            'login' => 'Please enter login details'
        ]);
    }

    /**
     * REDIRECT USER
     */
    private function redirectUser($user)
    {

        // UPDATE LAST SEEN

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'last_seen' => now()
            ]);

        switch ($user->acc_type) {

            case 1:
                return redirect()->intended('personal/dashboard');

            case 2:
                return redirect()->intended('dashboard');

            default:

                Auth::logout();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'access' => 'Unauthorized account type.'
                    ]);
        }
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {

            DB::table('users')
                ->where('id', Auth::id())
                ->update([
                    'last_seen' => now()
                ]);
        }

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Logged out successfully');
    }

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
} 



