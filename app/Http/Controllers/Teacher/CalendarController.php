<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\CalendarEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request, CalendarEventService $calendar)
    {
        return view('teacher.calendar', [
            'events' => $calendar->forTeacher(Auth::user()),
        ]);
    }
}