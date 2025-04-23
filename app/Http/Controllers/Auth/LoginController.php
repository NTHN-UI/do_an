<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        Auth::logout();
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try{
            $request->validate([
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $credentials = [
                $field => $request->login,
                'password' => $request->password,
                'is_active' => true
            ];

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();

                $user = Auth::user();

                if(Auth::check()){
                    return redirect()->intended(route('home.index'));
                }
            }

            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }
        catch(Exception $ex){
            Log::error("Loi tai dang nhap: " . $ex->getMessage());
            return back()->withInput();
        }

    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Bạn đã đăng xuất thành công!');
    }

}
