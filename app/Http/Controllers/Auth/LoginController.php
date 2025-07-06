<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (session('session_expired')) {
            Auth::logout();
            return view('auth.login')->with('status', 'Phiên làm việc đã hết hạn, vui lòng đăng nhập lại');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string|min:8',
        ], [
            'login.required' => 'Vui lòng nhập email hoặc số điện thoại',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field => $request->login,
            'password' => $request->password,
            'is_active' => true
        ];

        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Tài khoản đã bị khóa',
            ]);
        }

        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Tài khoản của bạn đã bị khóa',
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('home.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Bạn đã đăng xuất thành công!');
    }

    /**
     * Get the guard to be used during authentication.
     * (Có thể bỏ qua nếu sử dụng guard mặc định)
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the needed authorization credentials from the request.
     * (Có thể bỏ qua nếu xử lý trực tiếp trong phương thức login)
     */
    protected function credentials(Request $request)
    {
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        return [
            $field => $request->login,
            'password' => $request->password,
            'is_active' => true
        ];
    }
}
