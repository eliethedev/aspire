<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifySchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = $this->resolveSchool($request);

        if (!$school) {
            return response()->json(['message' => 'School not found'], 404);
        }

        if (!$school->isActive()) {
            return response()->json(['message' => 'School is inactive'], 403);
        }

        // Bind school to container for use throughout the request
        app()->instance('current_school', $school);
        
        // Set school connection if needed for database per-school
        // $this->setSchoolConnection($school);

        return $next($request);
    }

    protected function resolveSchool(Request $request): ?School
    {
        // Try to resolve school by subdomain first
        if ($subdomain = $this->getSubdomain($request)) {
            return School::where('subdomain', $subdomain)->first();
        }

        // Try to resolve school by domain
        if ($domain = $request->getHost()) {
            return School::where('domain', $domain)->first();
        }

        // Try to resolve school by header (for API requests)
        if ($schoolId = $request->header('X-School-ID')) {
            return School::find($schoolId);
        }

        // Try to resolve school from authenticated user
        if ($user = $request->user()) {
            return $user->school;
        }

        return null;
    }

    protected function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            return $parts[0];
        }

        return null;
    }

    protected function setSchoolConnection(School $school): void
    {
        // Uncomment and modify if using separate databases per school
        // config(['database.default' => 'school']);
        // config(['database.connections.school.database' => 'school_' . $school->id]);
    }
}
