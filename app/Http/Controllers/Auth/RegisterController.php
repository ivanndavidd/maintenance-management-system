<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     */
    protected $redirectTo = '/auth/pending';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users'], // ✅ ADDED
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'employee_id' => $data['employee_id'], // ✅ ADDED
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => false, // Inactive by default - admin must activate
        ]);

        // Auto-assign 'user' role
        $user->assignRole('user');

        return $user;
    }

    /**
     * The user has been registered.
     * Override to redirect to pending page instead of auto-login
     */
    protected function registered(Request $request, $user)
    {
        // Logout the user immediately (Laravel auto-logs in after register)
        $this->guard()->logout();

        // Redirect to pending page with user data
        return redirect()->route('auth.pending')->with('user', $user);
    }
}
