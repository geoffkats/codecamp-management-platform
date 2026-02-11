<?php

namespace App\Http\Middleware;

use App\Models\DailyReport;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDailyReport
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Allow admins/supervisors to bypass enforcement
        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('supervisor'))) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $allowedRoutes = [
            'daily-reports.submit',
            'logout',
        ];

        if (in_array($routeName, $allowedRoutes, true)) {
            return $next($request);
        }

        $cutoff = config('reports.cutoff_time', '17:00');
        $now = Carbon::now();
        $todayCutoff = Carbon::parse($now->toDateString() . ' ' . $cutoff);

        if ($now->lt($todayCutoff)) {
            return $next($request);
        }

        $hasReport = DailyReport::whereDate('report_date', $now->toDateString())
            ->where('instructor_id', $user->id)
            ->exists();

        if ($hasReport) {
            return $next($request);
        }

        return redirect()->route('daily-reports.submit')
            ->with('error', 'Daily report required before continuing.');
    }
}
