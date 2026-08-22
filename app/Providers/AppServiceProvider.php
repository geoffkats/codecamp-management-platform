<?php

namespace App\Providers;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CodeClub;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\StudentProfile;
use App\Models\User;
use App\Policies\AssessmentPolicy;
use App\Policies\CodeClubPolicy;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use App\Policies\StudentProfilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        Lesson::class => LessonPolicy::class,
        Assessment::class => AssessmentPolicy::class,
        Assignment::class => AssessmentPolicy::class, // Reuse for now
        User::class => UserPolicy::class,
        StudentProfile::class => StudentProfilePolicy::class,
        CodeClub::class => CodeClubPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length to prevent MySQL key length issues
        Schema::defaultStringLength(191);

        Relation::morphMap([
            'user' => User::class,
        ]);

        Route::bind('submission', function (string $value) {
            return AssignmentSubmission::find($value)
                ?? AssessmentAttempt::find($value)
                ?? abort(404);
        });
        
        $this->registerPolicies();

        // Register permission gates
        Gate::define('manage_users', function (User $user) {
            return $user->hasPermission('manage_users') || $user->isAdmin() || $user->isOperationsManager() || $user->isTeacher();
        });

        Gate::define('manage_students', function (User $user) {
            if ($user->hasPermission('manage_users') || $user->isAdmin() || $user->isOperationsManager() || $user->isTeacher()) {
                return true;
            }

            return config('features.code_club', false) && $user->isClubFacilitator();
        });

        Gate::define('access_code_clubs', function (User $user) {
            if ($user->isAdmin() || $user->isSupervisor()) {
                return true;
            }

            return config('features.code_club', false) && $user->hasCodeClubAccess();
        });

        Gate::define('access_club_attendance', function (User $user) {
            return config('features.code_club', false) && $user->hasCodeClubAccess();
        });

        Gate::define('view_analytics', function (User $user) {
            return $user->hasPermission('view_analytics') || $user->isAdmin() || $user->isTeacher();
        });

        Gate::define('manage_badges', function (User $user) {
            return $user->hasPermission('manage_badges') || $user->isAdmin();
        });

        Gate::define('manage_challenges', function (User $user) {
            return $user->isAdmin()
                || $user->isSupervisor()
                || $user->isTeacher()
                || $user->hasRole('codecamp_trainer');
        });

        Gate::define('create_courses', function (User $user) {
            return $user->hasPermission('create_courses') || $user->isTeacher() || $user->isAdmin();
        });

        Gate::define('edit_courses', function (User $user) {
            return $user->hasPermission('edit_courses') || $user->isTeacher() || $user->isAdmin();
        });

        Gate::define('review_content', function (User $user) {
            return $user->hasPermission('review_content') || $user->isSupervisor() || $user->isAdmin();
        });

        Gate::define('grade_submissions', function (User $user) {
            return $user->hasPermission('grade_submissions')
                || $user->isTeacher()
                || $user->isAdmin()
                || (config('features.code_club', false) && $user->hasCodeClubAccess());
        });

        Gate::define('enroll_courses', function (User $user) {
            return $user->hasPermission('enroll_courses') 
                || $user->isStudent() 
                || $user->isAdmin() 
                || $user->isTeacher() 
                || $user->isSupervisor();
        });

        Gate::define('view_teacher_code', function (User $user) {
            return $user->isTeacher()
                || $user->isAdmin()
                || $user->isSupervisor()
                || $user->isOperationsManager()
                || (config('features.code_club', false) && $user->hasCodeClubAccess());
        });

        Gate::define('generate_certificates', function (User $user) {
            return $user->isAdmin()
                || $user->isTeacher()
                || $user->isSupervisor()
                || $user->isOperationsManager();
        });
    }
}
