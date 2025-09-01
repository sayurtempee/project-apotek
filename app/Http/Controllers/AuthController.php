<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ResetPasswordNotification;
use App\Services\MailerService;

class AuthController extends Controller
{
    // ================= LOGIN ==================
    public function showLogin()
    {
        $title = 'Login';
        $project = 'Apotek Mii';
        return view('auth.login', compact('title', 'project'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Cek apakah akun sedang aktif di perangkat lain
            if ($user->status) {
                return redirect()->route('login')
                    ->with('error', 'Akun ini sedang aktif di perangkat lain. Silakan logout dulu.');
            }

            $request->session()->regenerate();

            $user->status = true; // set aktif
            $user->save();

            return redirect()
                ->route('dashboard')
                ->with([
                    'login_success' => true,
                    'login_name' => $user->name,
                    'login_role' => $user->role
                ]);
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    // ================= LOGOUT ==================
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->status = false; // set nonaktif
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ================= FORGOT PASSWORD ==================
    public function showForgotPasswordForm()
    {
        $title = 'Lupa Password';
        $project = 'Apotek Mii';
        return view('auth.forgot_password', compact('title', 'project'));
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(64);

        $user = User::where('email', $request->email)->first();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        $resetLink = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));
        $sent = MailerService::sendResetEmail($user->email, $resetLink);

        if ($sent === true) {
            return back()->with('status', 'Link reset password sudah dikirim ke email Anda!');
        } else {
            return back()->withErrors(['email' => "Email tidak dapat dikirim. Error: {$sent}"]);
        }
    }

    // ================= RESET PASSWORD ==================
    public function showResetPasswordForm(Request $request)
    {
        $token = $request->query('token');
        $title = 'Reset Password';
        $project = 'Apotek Mii';
        return view('auth.reset_password', ['token' => $token, 'title' => $title, 'project' => $project, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
            'token' => 'required'
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah digunakan.']);
        }

        // Update password
        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Hapus token setelah digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password berhasil direset, silakan login.');
    }
}
