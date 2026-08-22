<?php

namespace App\Livewire\Dashboard;

use App\Models\AssessmentAttempt;
use App\Models\Badge;
use App\Models\Certificate;
use App\Models\ClubSessionReport;
use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Models\ContentApproval;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DailyChallenge;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AdminDashboard extends Component
{
    public $stats = [];
    public $pendingApprovals = [];
    public $recentUsers = [];
    public $recentCourses = [];
    public $topPerformers = [];
    public $systemHealth = [];
    public $chartData = [];
    public $sampleCertificate = null;
    public $ictSchoolPerformance = [];
    public $recentIctAssessmentResults = [];
    public $codeClubStats = [];
    public $codeClubHighlights = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadPendingApprovals();
        $this->loadRecentUsers();
        $this->loadRecentCourses();
        $this->loadTopPerformers();
        $this->loadSampleCertificate();
        $this->checkSystemHealth();
        $this->loadChartData();
        $this->loadIctSchoolPerformance();

        if (config('features.code_club', false)) {
            $this->loadCodeClubStats();
        }
    }

    public function loadStats()
    {
        $this->stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_courses' => Course::count(),
            'published_courses' => Course::where('is_published', true)->count(),
            'total_lessons' => Lesson::count(),
            'total_enrollments' => CourseEnrollment::count(),
            'pending_approvals' => ContentApproval::where('status', 'pending')->count(),
            'total_badges' => Badge::count(),
            'active_challenges' => DailyChallenge::where('is_active', true)
                ->where('date', '>=', now()->toDateString())
                ->count(),
            'total_notifications' => Notification::count(),
            'unread_notifications' => Notification::where('is_read', false)->count(),
        ];

        // Calculate completion rate
        $completed = CourseEnrollment::whereNotNull('completed_at')->count();
        $this->stats['completion_rate'] = $this->stats['total_enrollments'] > 0
            ? round(($completed / $this->stats['total_enrollments']) * 100, 2)
            : 0;
        
        // Calculate percentage changes for dashboard cards
        $this->stats['users_change'] = $this->calculatePercentageChange(
            User::where('created_at', '>=', now()->subDays(30))->count(),
            User::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count()
        );
        
        $this->stats['courses_change'] = $this->calculatePercentageChange(
            Course::where('created_at', '>=', now()->subDays(30))->count(),
            Course::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count()
        );
        
        $this->stats['enrollments_change'] = $this->calculatePercentageChange(
            CourseEnrollment::where('created_at', '>=', now()->subDays(30))->count(),
            CourseEnrollment::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count()
        );
    }
    
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        $change = (($current - $previous) / $previous) * 100;
        return round($change, 1);
    }

    public function loadPendingApprovals()
    {
        $this->pendingApprovals = ContentApproval::with(['submitter', 'approvable'])
            ->where('status', 'pending')
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get()
            ->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'type' => class_basename($approval->approvable_type),
                    'title' => $this->getApprovableTitle($approval),
                    'submitted_by' => $approval->submitter?->name ?? 'Unknown',
                    'submitted_at' => $approval->submitted_at?->diffForHumans(),
                    'priority' => $approval->priority,
                    'approvable_id' => $approval->approvable_id,
                    'approvable_type' => $approval->approvable_type,
                ];
            })
            ->toArray();
    }

    protected function getApprovableTitle($approval)
    {
        if (!$approval->approvable) {
            return 'Deleted Item';
        }

        return match (class_basename($approval->approvable_type)) {
            'Course' => $approval->approvable->title,
            'Lesson' => $approval->approvable->title,
            'CourseModule' => $approval->approvable->title,
            default => 'Unknown',
        };
    }

    public function loadRecentUsers()
    {
        $this->recentUsers = User::with('roles')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'login_id' => $user->loginIdentifier(),
                    'student_type' => $user->student_type,
                    'is_active' => $user->is_active,
                    'roles' => $user->roles->pluck('display_name')->join(', '),
                    'created_at' => $user->created_at->diffForHumans(),
                    'last_login' => $user->last_login_at?->diffForHumans() ?? 'Never',
                ];
            })
            ->toArray();
    }

    public function loadCodeClubStats(): void
    {
        $this->codeClubStats = [
            'active_clubs' => CodeClub::where('status', 'active')->count(),
            'total_members' => CodeClubMembership::where('status', 'active')->count(),
            'students' => StudentProfile::where('program_type', 'codeclub')->where('is_active', true)->count(),
            'pending_reports' => ClubSessionReport::where('status', 'submitted')->count(),
            'follow_up_reports' => ClubSessionReport::where('follow_up_required', true)
                ->where('status', 'submitted')
                ->count(),
            'reports_this_week' => ClubSessionReport::where('session_date', '>=', now()->startOfWeek()->toDateString())->count(),
            'new_students_month' => StudentProfile::where('program_type', 'codeclub')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];

        $this->codeClubHighlights = CodeClub::query()
            ->with(['school:id,name'])
            ->withCount(['activeMemberships'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($club) => [
                'id' => $club->id,
                'name' => $club->name,
                'school' => $club->school?->name ?? '—',
                'members' => $club->active_memberships_count,
                'schedule' => $club->schedule_label,
            ])
            ->toArray();
    }

    public function loadRecentCourses()
    {
        $this->recentCourses = Course::with('instructor')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'instructor' => $course->instructor->name ?? 'Unknown',
                    'status' => $course->approval_status,
                    'is_published' => $course->is_published,
                    'enrollments' => $course->enrollments()->count(),
                    'created_at' => $course->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    public function loadTopPerformers()
    {
        $this->topPerformers = UserPoint::with('user')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get()
            ->map(function ($point) {
                return [
                    'user_id' => $point->user_id,
                    'name' => $point->user->name ?? 'Unknown',
                    'points' => number_format($point->total_points),
                    'level' => $point->level,
                    'badges_count' => $point->user ? $point->user->badges()->count() : 0,
                ];
            })
            ->toArray();
    }

    public function loadSampleCertificate()
    {
        $certificate = Certificate::with(['user', 'course'])
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at')
            ->first();

        $this->sampleCertificate = $certificate ? [
            'id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'title' => $certificate->title,
            'issued_at' => $certificate->issued_at?->format('M j, Y'),
            'user_name' => $certificate->user?->name,
            'course_title' => $certificate->course?->title,
        ] : null;
    }

    public function loadIctSchoolPerformance(): void
    {
        $this->ictSchoolPerformance = DB::table('assessment_attempts')
            ->leftJoin('schools', 'assessment_attempts.school_id', '=', 'schools.id')
            ->selectRaw('assessment_attempts.school_id, schools.name as school_name, COUNT(*) as total_attempts, SUM(assessment_attempts.is_passed = 1) as passed_attempts')
            ->where('assessment_attempts.student_type', 'ict')
            ->where('assessment_attempts.status', 'completed')
            ->groupBy('assessment_attempts.school_id', 'schools.name')
            ->orderByDesc('total_attempts')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total_attempts;
                $passed = (int) $row->passed_attempts;

                return [
                    'school_id' => $row->school_id,
                    'school_name' => $row->school_name ?? 'Unassigned',
                    'total_attempts' => $total,
                    'passed_attempts' => $passed,
                    'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
                ];
            })
            ->toArray();

        $this->recentIctAssessmentResults = AssessmentAttempt::query()
            ->where('student_type', 'ict')
            ->where('status', 'completed')
            ->with(['assessment.lesson', 'assessment.course', 'user', 'assessment.questions'])
            ->orderByDesc('completed_at')
            ->limit(8)
            ->get();
    }

    public function checkSystemHealth()
    {
        $this->systemHealth = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'recent_errors' => $this->checkRecentErrors(),
            'active_sessions' => $this->getActiveSessions(),
        ];
    }

    protected function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'message' => 'Database connection is active',
                'icon' => 'check-circle',
                'color' => 'green',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'icon' => 'x-circle',
                'color' => 'red',
            ];
        }
    }

    protected function checkStorageHealth()
    {
        $disk = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());
        $percentFree = ($disk / $total) * 100;

        return [
            'status' => $percentFree > 20 ? 'healthy' : 'warning',
            'message' => round($percentFree, 2) . '% free space',
            'icon' => $percentFree > 20 ? 'check-circle' : 'alert-circle',
            'color' => $percentFree > 20 ? 'green' : 'yellow',
            'percent' => round($percentFree, 2),
        ];
    }

    protected function checkRecentErrors()
    {
        // Check Laravel log file for recent errors (last 24 hours)
        $recentErrors = 0;
        $logPath = storage_path('logs/laravel.log');
        
        if (file_exists($logPath)) {
            // Read last part of log file (last 100KB to avoid memory issues)
            $fileSize = filesize($logPath);
            
            // Skip if file is empty
            if ($fileSize > 0) {
                $chunkSize = min(100 * 1024, $fileSize); // 100KB
                $handle = fopen($logPath, 'r');
                
                if ($handle && $chunkSize > 0) {
                    // Read from end of file
                    fseek($handle, -$chunkSize, SEEK_END);
                    $content = fread($handle, $chunkSize);
                    fclose($handle);
                    
                    // Count ERROR level log entries from last 24 hours
                    $lines = explode("\n", $content);
                    $yesterday = now()->subDay()->format('Y-m-d');
                    
                    foreach ($lines as $line) {
                        if (preg_match('/\[(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                            if ($matches[1] >= $yesterday && 
                                (stripos($line, '.ERROR') !== false || 
                                 stripos($line, 'CRITICAL') !== false ||
                                 stripos($line, 'Exception') !== false)) {
                                $recentErrors++;
                            }
                        }
                    }
                }
            }
        }
        
        // Also check for failed queue jobs in last 24 hours
        try {
            $failedJobs = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();
            $recentErrors += $failedJobs;
        } catch (\Exception $e) {
            // Table might not exist, ignore
        }
        
        return [
            'status' => $recentErrors < 10 ? 'healthy' : 'warning',
            'message' => $recentErrors . ' recent errors',
            'icon' => $recentErrors < 10 ? 'check-circle' : 'alert-circle',
            'color' => $recentErrors < 10 ? 'green' : 'yellow',
        ];
    }

    protected function getActiveSessions()
    {
        try {
            $sessions = DB::table('sessions')
                ->where('last_activity', '>', now()->subHour()->timestamp)
                ->count();

            return [
                'count' => $sessions,
                'status' => $sessions > 0 ? 'active' : 'inactive',
            ];
        } catch (\Exception $e) {
            return [
                'count' => 0,
                'status' => 'unknown',
            ];
        }
    }

    public function loadChartData()
    {
        // Enrollment trends (last 7 days)
        $enrollmentTrends = CourseEnrollment::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // User growth (last 6 months)
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $userGrowth = User::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } else {
            $userGrowth = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        // Course completion rates
        $completionData = DB::select("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed
            FROM course_enrollments
        ");

        // Enrollment by course
        $enrollmentsByCourse = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit(5)
            ->get();

        $this->chartData = [
            'enrollment_trends' => [
                'labels' => $enrollmentTrends->pluck('date')->toArray(),
                'data' => $enrollmentTrends->pluck('count')->toArray(),
            ],
            'user_growth' => [
                'labels' => $userGrowth->pluck('month')->toArray(),
                'data' => $userGrowth->pluck('count')->toArray(),
            ],
            'completion_rate' => [
                'completed' => $completionData[0]->completed ?? 0,
                'total' => $completionData[0]->total ?? 0,
            ],
            'top_courses' => [
                'labels' => $enrollmentsByCourse->pluck('title')->toArray(),
                'enrollments' => $enrollmentsByCourse->pluck('enrollments_count')->toArray(),
            ],
        ];
    }

    public function refresh()
    {
        $this->loadStats();
        $this->loadPendingApprovals();
        $this->loadRecentUsers();
        $this->loadRecentCourses();
        $this->loadTopPerformers();
        $this->loadSampleCertificate();
        $this->checkSystemHealth();
        $this->loadChartData();
        $this->loadIctSchoolPerformance();

        if (config('features.code_club', false)) {
            $this->loadCodeClubStats();
        }
        
        $this->dispatch('refreshed');
    }

    public function render()
    {
        // Get additional metrics for dashboard
        $this->loadAdditionalMetrics();
        
        return view('livewire.dashboard.admin-dashboard', [
            'quickStats' => $this->getQuickStats(),
            'performanceMetrics' => $this->getPerformanceMetrics(),
            'recentActivity' => $this->getRecentActivity(),
        ])->with('chartData', $this->chartData);
    }

    private function loadAdditionalMetrics()
    {
        // Ensure all required data is loaded
        if (empty($this->stats)) {
            $this->loadStats();
        }
        if (empty($this->chartData)) {
            $this->loadChartData();
        }
    }

    private function getQuickStats(): array
    {
        return [
            'today_enrollments' => CourseEnrollment::whereDate('created_at', today())->count(),
            'today_completions' => CourseEnrollment::whereDate('completed_at', today())->count(),
            'today_new_users' => User::whereDate('created_at', today())->count(),
            'week_enrollments' => CourseEnrollment::where('created_at', '>=', now()->startOfWeek())->count(),
            'month_enrollments' => CourseEnrollment::where('created_at', '>=', now()->startOfMonth())->count(),
            'avg_completion_time' => $this->getAverageCompletionTime(),
        ];
    }

    private function getPerformanceMetrics(): array
    {
        $enrollmentsQuery = CourseEnrollment::query();
        $total = $enrollmentsQuery->count();
        $completed = $enrollmentsQuery->whereNotNull('completed_at')->count();
        $engaged = CourseEnrollment::where('progress_percentage', '>', 0)->count();
        
        return [
            'engagement_rate' => $total > 0 ? round(($engaged / $total) * 100, 2) : 0,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'retention_rate' => $this->getRetentionRate(),
            'avg_session_duration' => $this->getAvgSessionDuration(),
            'active_learners_7d' => $this->getActiveLearners(7),
            'active_learners_30d' => $this->getActiveLearners(30),
        ];
    }

    private function getRecentActivity()
    {
        return [
            'course_creations' => Course::where('created_at', '>=', now()->subDays(7))->count(),
            'new_badges_earned' => DB::table('user_badges')->where('created_at', '>=', now()->subDays(7))->count(),
            'challenges_completed' => DB::table('daily_challenge_attempts')->where('is_completed', true)->where('completed_at', '>=', now()->subDays(7))->count(),
            'discussions_created' => \App\Models\Discussion::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    private function getAverageCompletionTime()
    {
        $completions = CourseEnrollment::whereNotNull('completed_at')
            ->whereNotNull('enrolled_at')
            ->get()
            ->map(function ($enrollment) {
                return $enrollment->enrolled_at->diffInDays($enrollment->completed_at);
            });

        return $completions->count() > 0 ? round($completions->avg(), 1) : 0;
    }

    private function getRetentionRate()
    {
        $startDate = now()->subDays(30);
        $users30dAgo = User::where('created_at', '<=', $startDate)->count();
        $activeUsers = User::whereHas('enrollments', function($q) use ($startDate) {
            $q->where('updated_at', '>=', $startDate);
        })->count();
        
        return $users30dAgo > 0 ? round(($activeUsers / $users30dAgo) * 100, 2) : 0;
    }

    private function getAvgSessionDuration()
    {
        try {
            // Get sessions that started and ended today
            $sessions = DB::table('sessions')
                ->where('last_activity', '>=', now()->subDay()->timestamp)
                ->get();
            
            if ($sessions->isEmpty()) {
                return 0;
            }
            
            // Calculate average session duration
            // Use last_activity - created_at as session duration estimate
            // For better accuracy, we'd need to track session start/end properly
            $totalDuration = 0;
            $count = 0;
            
            foreach ($sessions as $session) {
                $sessionData = unserialize(base64_decode($session->payload));
                if (isset($sessionData['_token_created_at'])) {
                    $createdAt = $sessionData['_token_created_at'];
                    $lastActivity = $session->last_activity;
                    $duration = $lastActivity - $createdAt;
                    if ($duration > 0 && $duration < 86400) { // Less than 24 hours (in seconds)
                        $totalDuration += $duration;
                        $count++;
                    }
                }
            }
            
            // If we can't calculate from session data, estimate from activity patterns
            if ($count === 0) {
                // Estimate based on average time between enrollments and last activity
                $driver = DB::connection()->getDriverName();
                if ($driver === 'sqlite') {
                    $enrollments = CourseEnrollment::where('created_at', '>=', now()->subDay())
                        ->get()
                        ->map(function ($enrollment) {
                            return $enrollment->created_at->diffInSeconds($enrollment->updated_at);
                        });
                } else {
                    $enrollments = CourseEnrollment::where('created_at', '>=', now()->subDay())
                        ->selectRaw('AVG(UNIX_TIMESTAMP(updated_at) - UNIX_TIMESTAMP(created_at)) as avg_duration')
                        ->first();
                    
                    if ($enrollments && $enrollments->avg_duration) {
                        return round($enrollments->avg_duration / 60, 1); // Convert to minutes
                    }
                    
                    return 0;
                }
                
                if ($enrollments->count() > 0) {
                    $avgSeconds = $enrollments->avg();
                    return round($avgSeconds / 60, 1); // Convert to minutes
                }
                
                return 0;
            }
            
            return round(($totalDuration / $count) / 60, 1); // Convert to minutes
        } catch (\Exception $e) {
            // Fallback calculation based on user activity
            $activeUsers = CourseEnrollment::where('updated_at', '>=', now()->subDay())
                ->distinct('user_id')
                ->count();
            
            if ($activeUsers > 0) {
                // Rough estimate: average 30 minutes per active session
                return 30;
            }
            
            return 0;
        }
    }

    private function getActiveLearners($days)
    {
        return CourseEnrollment::where('updated_at', '>=', now()->subDays($days))
            ->distinct('user_id')
            ->count('user_id');
    }
}
