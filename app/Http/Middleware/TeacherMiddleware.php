<?php

namespace App\Http\Middleware;

use App\Models\TeacherAssignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->user() || !$request->user()->isTeacher()){
            return redirect()->route('home.index')->with('error', 'Bạn không có quyền vào trang này');
        }
        $isHomeroomTeacher = TeacherAssignment::where('teacher_id', $request->user()->id)
            ->where('is_homeroom', true)
            ->exists();
        $request->session()->put('is_homeroom_teacher', $isHomeroomTeacher);

        return $next($request);
    }
}
