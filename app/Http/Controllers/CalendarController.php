<?php

namespace App\Http\Controllers;

use App\Services\CalendarEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Display the supervisor's calendar view.
     */
    public function index(Request $request, CalendarEventService $calendar)
    {
        return view('supervisor.calendar', [
            'events' => $calendar->forSupervisor(Auth::user()),
        ]);
    }

    /**
     * Get events for a specific date (kept for compatibility with AJAX callers).
     */
    public function getObservationsByDate(Request $request, CalendarEventService $calendar)
    {
        $date = $request->query('date');

        if (! $date) {
            return response()->json([]);
        }

        $events = collect($calendar->forSupervisor(Auth::user()))
            ->where('date', $date)
            ->values()
            ->all();

        return response()->json($events);
    }
}