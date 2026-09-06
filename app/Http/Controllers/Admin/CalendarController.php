<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CalendarEventService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request, CalendarEventService $calendar)
    {
        return view('admin.calendar', [
            'events' => $calendar->forAdmin(),
        ]);
    }
}