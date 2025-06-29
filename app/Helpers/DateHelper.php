<?php

namespace App\Helpers;

use App\Models\TeacherAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DateHelper
{
    public static function getCurrentAcademicYear(){
        $currentYear = Carbon::now()->format('Y');
        $previousYear = intval($currentYear) - 1;
        return "{$previousYear}-{$currentYear}";
    }
}
