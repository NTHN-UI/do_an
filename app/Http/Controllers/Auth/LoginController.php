<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
//    public function showLoginForm()
//    {
//        Auth::logout();
//        return view('auth.login');
//    }
    public function showLoginForm()
    {
        // Kiểm tra nếu đăng nhập do hết hạn session
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

        // Thêm remember token với thời gian lưu dài hơn
//        $remember = $request->filled('remember');
//        if ($remember) {
//            $rememberTokenExpire = 43200; // 30 ngày (tính bằng phút)
//            Auth::setRememberDuration($rememberTokenExpire);
//        }

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => 'Thông tin đăng nhập không chính xác',
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


}
