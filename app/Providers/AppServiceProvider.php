<?php

namespace App\Providers;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Policies\AssessmentPolicy;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        $this->registerPolicies();

        // Register permission gates
        Gate::define('manage_users', function (User $user) {
            return $user->hasPermission('manage_users');
        });

        Gate::define('view_analytics', function (User $user) {
            return $user->hasPermission('view_analytics') || $user->isAdmin() || $user->isTeacher();
        });

        Gate::define('manage_badges', function (User $user) {
            return $user->hasPermission('manage_badges') || $user->isAdmin();
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
            return $user->hasPermission('grade_submissions') || $user->isTeacher() || $user->isAdmin();
        });

        Gate::define('enroll_courses', function (User $user) {
            return $user->hasPermission('enroll_courses') || $user->isStudent();
        });
    }
}
