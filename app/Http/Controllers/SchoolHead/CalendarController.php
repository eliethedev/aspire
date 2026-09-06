<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Services\CalendarEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request, CalendarEventService $calendar)
    {
        return view('school-head.calendar', [
            'events' => $calendar->forSchoolHead(Auth::user()),
        ]);
    }
}