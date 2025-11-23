<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Visual Components Test Page
Route::get('/test-visual-components', function () {
    return view('test-visual-components');
})->name('test-visual-components');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Image upload API for TipTap editor
    Route::post('/api/upload-image', [App\Http\Controllers\Api\ImageUploadController::class, 'upload'])
        ->name('api.upload-image');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Courses Routes
    Route::prefix('courses')->name('courses.')->middleware('student.profile')->group(function () {
        Route::get('/', \App\Livewire\Courses\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Courses\Create::class)->name('create');
        Route::get('/{course}/learn', \App\Livewire\Courses\Learn::class)->name('learn');
        Route::get('/{course}', \App\Livewire\Courses\Show::class)->name('show');
        Route::get('/{course}/edit', \App\Livewire\Courses\Edit::class)->name('edit');
        Route::get('/{course}/enrollments', \App\Livewire\Courses\ManageEnrollments::class)->name('enrollments');
    });

    // Modules Routes
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::get('/', \App\Livewire\Modules\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Modules\Create::class)->name('create');
        Route::get('/{module}', \App\Livewire\Modules\Show::class)->name('show');
        Route::get('/{module}/edit', \App\Livewire\Modules\Edit::class)->name('edit');
    });

    // Lessons Routes
    Route::prefix('lessons')->name('lessons.')->group(function () {
        Route::get('/', \App\Livewire\Lessons\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Lessons\Create::class)->name('create');
        Route::get('/{lesson}/view', \App\Livewire\Lessons\View::class)->name('view');
        Route::get('/{lesson}', \App\Livewire\Lessons\Show::class)->name('show');
        Route::get('/{lesson}/edit', \App\Livewire\Lessons\Edit::class)->name('edit');
    });

    // Assessments Routes
    Route::prefix('assessments')->name('assessments.')->group(function () {
        Route::get('/', \App\Livewire\Assessments\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Assessments\Create::class)->name('create');
        Route::get('/{assessment}', \App\Livewire\Assessments\Show::class)->name('show');
        Route::get('/{assessment}/edit', \App\Livewire\Assessments\Edit::class)->name('edit');
        Route::get('/{assessment}/take', \App\Livewire\Assessments\Take::class)->name('take');
    });

    // Quizzes Routes - DISABLED (Using Assessments instead)
    // Route::prefix('quizzes')->name('quizzes.')->group(function () {
    //     Route::get('/', \App\Livewire\Quizzes\Index::class)->name('index');
    //     Route::get('/create', \App\Livewire\Quizzes\Create::class)->name('create');
    //     Route::get('/{assessment}', \App\Livewire\Quizzes\Show::class)->name('show');
    //     Route::get('/{assessment}/edit', \App\Livewire\Quizzes\Edit::class)->name('edit');
    //     Route::get('/{assessment}/take', \App\Livewire\Quizzes\Take::class)->name('take');
    // });

    // Questions Routes
    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('/', \App\Livewire\Questions\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Questions\Create::class)->name('create');
        Route::get('/{question}/edit', \App\Livewire\Questions\Edit::class)->name('edit');
    });

    // Assignments Routes
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', \App\Livewire\Assignments\Index::class)->name('index');
        Route::middleware(['can:create_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Assignments\Create::class)->name('create');
        });
        Route::get('/{assignment}', \App\Livewire\Assignments\Show::class)->name('show');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/{assignment}/edit', \App\Livewire\Assignments\Edit::class)->name('edit');
        });
        Route::get('/{assignment}/submit', \App\Livewire\Assignments\Submit::class)->name('submit');
    });

    // Badges Routes
    Route::prefix('badges')->name('badges.')->group(function () {
        Route::get('/', \App\Livewire\Badges\Index::class)->name('index');
        Route::middleware(['can:manage_badges'])->group(function () {
            Route::get('/create', \App\Livewire\Badges\Create::class)->name('create');
            Route::get('/{badge}/edit', \App\Livewire\Badges\Edit::class)->name('edit');
        });
        Route::get('/{badge}', \App\Livewire\Badges\Show::class)->name('show');
    });

    // Daily Challenges Routes
    Route::prefix('daily-challenges')->name('daily-challenges.')->group(function () {
        Route::get('/', \App\Livewire\DailyChallenges\Index::class)->name('index');
        Route::middleware(['can:manage_badges'])->group(function () {
            Route::get('/create', \App\Livewire\DailyChallenges\Create::class)->name('create');
            Route::get('/{dailyChallenge}/edit', \App\Livewire\DailyChallenges\Edit::class)->name('edit');
        });
        Route::get('/{dailyChallenge}', \App\Livewire\DailyChallenges\Show::class)->name('show');
    });

    // Leaderboards Routes
    Route::prefix('leaderboards')->name('leaderboards.')->group(function () {
        Route::get('/', \App\Livewire\Leaderboards\Index::class)->name('index');
        Route::get('/{leaderboard}', \App\Livewire\Leaderboards\Show::class)->name('show');
    });

    // Certificates Routes
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', \App\Livewire\Certificates\Index::class)->name('index');
        Route::get('/{certificate}', \App\Livewire\Certificates\Show::class)->name('show');
        Route::middleware(['can:enroll_courses'])->group(function () {
            Route::get('/generate/{course}', \App\Livewire\Certificates\Generate::class)->name('generate');
        });
    });

    // Discussions Routes
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/', \App\Livewire\Discussions\Index::class)->name('index');
        Route::middleware(['can:enroll_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Discussions\Create::class)->name('create');
            Route::get('/{discussion}/edit', \App\Livewire\Discussions\Edit::class)->name('edit');
        });
        Route::get('/{discussion}', \App\Livewire\Discussions\Show::class)->name('show');
    });

    // Enrollments Routes
    Route::prefix('enrollments')->name('enrollments.')->group(function () {
        Route::get('/', \App\Livewire\Enrollments\Index::class)->name('index');
        Route::get('/enroll/{course}', \App\Livewire\Enrollments\Enroll::class)->name('enroll');
    });

    // Invitations Routes - Students can view their invitations
    Route::prefix('invitations')->name('invitations.')->group(function () {
        Route::get('/', \App\Livewire\Invitations\Index::class)->name('index');
    });

    // Content Approvals Routes - Supervisor/Admin only
    Route::middleware(['can:review_content'])->prefix('content-approvals')->name('content-approvals.')->group(function () {
        Route::get('/', \App\Livewire\ContentApprovals\Index::class)->name('index');
        Route::get('/{approval}/review', \App\Livewire\ContentApprovals\Review::class)->name('review');
    });

    // Analytics Routes - Restricted by role
    Route::middleware(['can:view_analytics'])->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', \App\Livewire\Analytics\Dashboard::class)->name('dashboard');
    });

    // Curriculum Builder Routes - Teacher/Admin only
    Route::middleware(['can:edit_courses'])->prefix('curriculum')->name('curriculum.')->group(function () {
        Route::get('/builder/{course?}', \App\Livewire\Curriculum\NewBuilder::class)->name('builder');
    });

    // Progress Routes
    Route::prefix('progress')->name('progress.')->group(function () {
        Route::get('/', \App\Livewire\Progress\Tracking::class)->name('tracking');
        Route::get('/student', \App\Livewire\Progress\StudentProgress::class)->name('student');
    });

    // Notifications Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', \App\Livewire\Notifications\Index::class)->name('index');
    });

    // Gamification Routes
    Route::prefix('gamification')->name('gamification.')->group(function () {
        Route::get('/points', \App\Livewire\Gamification\Points::class)->name('points');
    });

    // Grades Routes - Teachers/Admins can view all, Students only their own
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', \App\Livewire\Grades\Index::class)->name('index');
        Route::middleware(['can:grade_submissions'])->group(function () {
            Route::get('/{submission}/grade', \App\Livewire\Grades\Grade::class)->name('grade');
        });
    });

    // Submissions Routes
    Route::prefix('submissions')->name('submissions.')->group(function () {
        Route::get('/', \App\Livewire\Submissions\Index::class)->name('index');
        Route::get('/{submissionId}/{type?}', \App\Livewire\Submissions\Show::class)->name('show');
    });

    // Attempts Routes
    Route::prefix('attempts')->name('attempts.')->group(function () {
        Route::get('/', \App\Livewire\Attempts\Index::class)->name('index');
        Route::get('/{attempt}', \App\Livewire\Attempts\Show::class)->name('show');
    });

    // Admin Routes - User Management
    Route::middleware(['can:manage_users'])->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', \App\Livewire\Users\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Users\Create::class)->name('create');
            Route::get('/{user}', \App\Livewire\Users\Show::class)->name('show');
            Route::get('/{user}/edit', \App\Livewire\Users\Edit::class)->name('edit');
        });
    });

    // Admin Routes - Enrollment Management (Admin/Supervisor)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/enrollments', \App\Livewire\Admin\EnrollmentManagement::class)->name('enrollments');
        Route::get('/settings', \App\Livewire\Admin\SystemSettings::class)->name('settings');
        Route::get('/feedback', \App\Livewire\Admin\ManageTeacherFeedback::class)->name('feedback');
    });

    // Student Feedback Routes
    Route::middleware(['student.profile'])->prefix('feedback')->name('feedback.')->group(function () {
        Route::get('/teacher', \App\Livewire\Feedback\SubmitTeacherFeedback::class)->name('teacher');
    });

    // Student Management Routes - Teachers/Admin/Operations
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', \App\Livewire\Students\ManageStudents::class)->name('index');
        Route::get('/create', \App\Livewire\Students\StudentForm::class)->name('create');
        Route::get('/{student}', \App\Livewire\Students\StudentProfile::class)->name('show');
        Route::get('/{student}/edit', \App\Livewire\Students\StudentForm::class)->name('edit');
        Route::get('/{student}/teacher-update', \App\Livewire\Students\TeacherStudentUpdate::class)->name('teacher-update');
    });

    // Attendance Routes - Operations Manager/Admin
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/students', \App\Livewire\Attendance\StudentAttendance::class)->name('student');
        Route::get('/instructors', \App\Livewire\Attendance\InstructorAttendance::class)->name('instructor');
        Route::get('/records', \App\Livewire\Attendance\AttendanceRecords::class)->name('records');
        
        // New daily code system routes
        Route::get('/code', \App\Livewire\Attendance\TeacherCodeDisplay::class)->name('code');
        Route::get('/check-in', \App\Livewire\Attendance\StudentCheckIn::class)->name('check-in');
        Route::get('/dashboard', \App\Livewire\Attendance\AttendanceDashboard::class)->name('dashboard');
    });
});
