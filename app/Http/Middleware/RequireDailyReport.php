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

        // Skip daily report enforcement for club-only facilitators or dual-access users in Code Club context
        if ($user->hasCodeClubAccess() && ! $user->isCodecampTrainer()) {
            return $next($request);
        }

        if ($user->hasDualProgramAccess() && $user->activeProgramContext() === 'codeclub') {
            return $next($request);
        }

        // Only enforce daily reports for instructors - skip students and ICT teachers
        if ($user->hasRole('instructor') || $user->isCodecampTrainer()) {
            $routeName = $request->route()?->getName();
            $allowedRoutes = [
                'daily-reports.submit',
                'club-session-reports.submit',
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

            // Optional: never block the app. Instructors get a dashboard
            // reminder and a 16:00 notification instead.
            return $next($request);
        }

        // Students and other roles bypass the daily report requirement
        return $next($request);
    }
}
