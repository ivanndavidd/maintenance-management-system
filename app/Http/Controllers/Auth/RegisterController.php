<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationCode;
use App\Mail\EmailVerificationCode as EmailVerificationCodeMail;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
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
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request - send verification code first.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('register')
                ->withErrors($validator)
                ->withInput();
        }

        // Store registration data and generate verification code
        $verification = EmailVerificationCode::generateCode(
            $request->email,
            [
                'name' => $request->name,
                'employee_id' => $request->employee_id,
                'email' => $request->email,
                'password' => $request->password, // Will be hashed when user is created
            ]
        );

        // Send verification email
        try {
            Mail::to($request->email)->send(
                new EmailVerificationCodeMail($verification->code, $request->name)
            );
        } catch (\Exception $e) {
            // Delete the verification code if email fails
            $verification->delete();

            return redirect()->route('register')
                ->with('error', 'Failed to send verification email. Please try again.')
                ->withInput();
        }

        // Redirect to verification page
        return redirect()->route('verify.code.form', ['email' => $request->email])
            ->with('success', 'A verification code has been sent to your email.');
    }

    /**
     * Show the verification code form.
     */
    public function showVerifyCodeForm(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('register')
                ->with('error', 'Invalid verification request.');
        }

        // Check if verification code exists
        $verification = EmailVerificationCode::where('email', $email)
            ->whereNull('verified_at')
            ->first();

        if (!$verification) {
            return redirect()->route('register')
                ->with('error', 'No pending verification found. Please register again.');
        }

        return view('auth.verify-code', compact('email'));
    }

    /**
     * Verify the code and complete registration.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $verification = EmailVerificationCode::verifyCode($request->email, $request->code);

        if (!$verification) {
            return back()
                ->with('error', 'Invalid or expired verification code. Please try again.')
                ->withInput();
        }

        // Mark as verified
        $verification->markAsVerified();

        // Create the user
        $data = $verification->registration_data;

        $user = User::create([
            'name' => $data['name'],
            'employee_id' => $data['employee_id'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(), // Email is verified
            'is_active' => false, // Still needs admin approval
        ]);

        // Auto-assign 'staff_maintenance' role for new registrations
        $user->assignRole('staff_maintenance');

        // Delete the verification code
        $verification->delete();

        // Redirect to pending page with user data
        return redirect()->route('auth.pending')
            ->with('user', $user)
            ->with('success', 'Email verified successfully! Your account is pending admin approval.');
    }

    /**
     * Resend verification code.
     */
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find existing verification
        $verification = EmailVerificationCode::where('email', $request->email)
            ->whereNull('verified_at')
            ->first();

        if (!$verification) {
            return redirect()->route('register')
                ->with('error', 'No pending verification found. Please register again.');
        }

        // Generate new code
        $verification->update([
            'code' => EmailVerificationCode::generateRandomCode(),
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send new verification email
        try {
            $registrationData = $verification->registration_data;
            Mail::to($request->email)->send(
                new EmailVerificationCodeMail($verification->code, $registrationData['name'])
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send verification email. Please try again.');
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }
}
