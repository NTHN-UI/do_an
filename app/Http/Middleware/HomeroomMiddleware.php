<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HomeroomMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->isSchoolAdmin()) {
            return $next($request);
        }
        try {
            $studentId = $request->route('student')->id ?? $request->route('id');

            if (!$studentId)
                throw new \Exception('Student ID not found in route');

            if ($user->isHomeroomTeacherOf($studentId)) {
                return $next($request);
            }

        } catch (\Exception $e) {
            Log::error('Middleware check failed: ' . $e->getMessage());
        }

        return redirect()->route('home.index')
            ->with('error', 'Bạn không có quyền truy cập!');
    }
}
