<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }
    public function edit(Request $request): View
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }


    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ];

        // Thêm rules cho thông tin phụ huynh nếu là học sinh
        if ($user->isStudent()) {
            $rules = array_merge($rules, [
                'guardian_name' => 'nullable|string|max:255',
                'guardian_email' => 'nullable|email|unique:users,guardian_email,'.$user->id,
                'guardian_phone' => 'nullable|string|max:20',
            ]);
        }

        $validated = $request->validate($rules);

        // Dữ liệu cơ bản
        $updateData = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        // Thêm thông tin phụ huynh nếu là học sinh
        if ($user->isStudent()) {
            $updateData['guardian_name'] = $validated['guardian_name'] ?? null;
            $updateData['guardian_email'] = $validated['guardian_email'] ?? null;
            $updateData['guardian_phone'] = $validated['guardian_phone'] ?? null;
        }

        $user->update($updateData);

        // Xử lý avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars');
            $user->update(['avatar' => $path]);
        }

        // Đổi mật khẩu
        if ($request->filled('current_password')) {
            if (Hash::check($validated['current_password'], $user->password)) {
                $user->update([
                    'password' => Hash::make($validated['new_password'])
                ]);
            } else {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
            }
        }

        return redirect()->route('profile.show')
            ->with('success', 'Cập nhật hồ sơ thành công!');
    }}
