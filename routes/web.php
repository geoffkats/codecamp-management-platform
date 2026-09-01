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

Route::get('dashboard', function () {
    $user = auth()->user();

    if ($user?->isStudent()) {
        if ($user->isIctStudent()) {
            return redirect()->route('icdl.dashboard');
        }
        if ($user->isCodeClubStudent() && config('features.code_club', false)) {
            return redirect()->route('codeclub.dashboard');
        }

        return redirect()->route('codecamp.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('icdl/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('icdl.dashboard');

Route::view('codecamp/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('codecamp.dashboard');

Route::view('codeclub/dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'code_club.enabled'])
    ->name('codeclub.dashboard');

Route::post('program-context/switch', [\App\Http\Controllers\ProgramContextController::class, 'switch'])
    ->middleware(['auth'])
    ->name('program-context.switch');

Route::prefix('register')->name('registration.')->group(function () {
    Route::get('/codecamp', [\App\Http\Controllers\RegistrationController::class, 'showCodecamp'])->name('codecamp');
    Route::post('/codecamp', [\App\Http\Controllers\RegistrationController::class, 'storeCodecamp'])->name('codecamp.store');
    Route::get('/school', [\App\Http\Controllers\RegistrationController::class, 'showSchool'])->name('school');
    Route::post('/school', [\App\Http\Controllers\RegistrationController::class, 'storeSchool'])->name('school.store');
    Route::get('/icdl', [\App\Http\Controllers\RegistrationController::class, 'showIcdl'])->name('icdl');
    Route::post('/icdl', [\App\Http\Controllers\RegistrationController::class, 'storeIcdl'])->name('icdl.store');
    Route::middleware('code_club.enabled')->group(function () {
        Route::get('/codeclub', [\App\Http\Controllers\RegistrationController::class, 'showCodeclub'])->name('codeclub');
        Route::post('/codeclub', [\App\Http\Controllers\RegistrationController::class, 'storeCodeclub'])->name('codeclub.store');
    });
    Route::get('/thank-you', [\App\Http\Controllers\RegistrationController::class, 'thankYou'])->name('thank-you');
});

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
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', \App\Livewire\Courses\Index::class)->name('index');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Courses\Create::class)->name('create');
            Route::get('/{course}/edit', \App\Livewire\Courses\Edit::class)->name('edit');
            Route::get('/{course}/enrollments', \App\Livewire\Courses\ManageEnrollments::class)->name('enrollments');
        });
        Route::get('/{course}/preview', \App\Livewire\Courses\Preview::class)->name('preview');
        Route::middleware(['student.profile'])->group(function () {
            Route::get('/{course}/learn', \App\Livewire\Courses\Learn::class)->name('learn');
            Route::get('/{course}', \App\Livewire\Courses\Show::class)->name('show');
        });
    });

    // Modules Routes
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::get('/', \App\Livewire\Modules\Index::class)->name('index');
        Route::get('/{module}', \App\Livewire\Modules\Show::class)->name('show');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Modules\Create::class)->name('create');
            Route::get('/{module}/edit', \App\Livewire\Modules\Edit::class)->name('edit');
        });
    });

    // Daily Reports - submission for instructors
    Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
        Route::get('/submit', \App\Livewire\DailyReports\Submit::class)->name('submit');
    });

    Route::prefix('club-session-reports')->middleware('code_club.enabled')->name('club-session-reports.')->group(function () {
        Route::get('/submit', \App\Livewire\ClubSessionReports\Submit::class)->name('submit');
    });

    Route::get('/lesson-locks', \App\Livewire\Lessons\LessonLocks::class)->name('lessons.locks');

    // Lessons Routes
    Route::prefix('lessons')->name('lessons.')->group(function () {
        Route::get('/', \App\Livewire\Lessons\Index::class)->name('index');
        Route::redirect('/locks', '/lesson-locks');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Lessons\Create::class)->name('create');
        });
        Route::get('/{lesson}/view', \App\Livewire\Lessons\View::class)->whereNumber('lesson')->name('view');
        Route::post('/{lesson}/complete', [\App\Http\Controllers\Lessons\CompletionController::class, 'store'])->whereNumber('lesson')->name('complete');
        Route::get('/{lesson}', \App\Livewire\Lessons\View::class)->whereNumber('lesson')->name('show');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/{lesson}/edit', \App\Livewire\Lessons\Edit::class)->whereNumber('lesson')->name('edit');
        });
    });

    // Assessments Routes
    Route::prefix('assessments')->name('assessments.')->group(function () {
        Route::get('/', \App\Livewire\Assessments\Index::class)->name('index');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Assessments\Create::class)->name('create');
            Route::get('/{assessment}/edit', \App\Livewire\Assessments\Edit::class)->name('edit');
        });
        Route::get('/{assessment}', \App\Livewire\Assessments\Show::class)->name('show');
        Route::get('/{assessment}/take', \App\Livewire\Assessments\Take::class)->name('take');
        Route::get('/{assessment}/results/{attempt}', \App\Livewire\Assessments\Results::class)->name('results');
    });


    Route::get('/students/{student}/print-credentials', function ($student) {
        $studentProfile = \App\Models\StudentProfile::with(['user', 'school'])
            ->findOrFail($student);

        abort_unless(auth()->user()?->can('view', $studentProfile), 403);

        return view('students.print-credentials', [
            'student' => $studentProfile,
        ]);
    })->name('students.print-credentials');
    // Quizzes routes (alias for quiz-type assessments)
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', \App\Livewire\Quizzes\Index::class)->name('index');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Quizzes\Create::class)->name('create');
            Route::get('/{assessment}/edit', \App\Livewire\Quizzes\Edit::class)->name('edit');
        });
        Route::get('/{assessment}', \App\Livewire\Quizzes\Show::class)->name('show');
        Route::get('/{assessment}/take', \App\Livewire\Quizzes\Take::class)->name('take');
    });

    // Questions Routes
    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('/', \App\Livewire\Questions\Index::class)->name('index');
        Route::middleware(['can:edit_courses'])->group(function () {
            Route::get('/create', \App\Livewire\Questions\Create::class)->name('create');
            Route::get('/{question}/edit', \App\Livewire\Questions\Edit::class)->name('edit');
        });
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
        Route::middleware(['can:manage_challenges'])->group(function () {
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
        // Students: list certificates issued to their account
        Route::get('/', \App\Livewire\Certificates\Index::class)->name('index');

        // Staff only: generate / issue / test (must be registered before /{certificate})
        Route::middleware(['can:generate_certificates'])->group(function () {
            Route::get('/generator', \App\Livewire\Certificates\Generate::class)->name('generator');
            Route::get('/generate/{course}', \App\Livewire\Certificates\Generate::class)->name('generate');
            Route::get('/sample-csv', [\App\Http\Controllers\CertificateController::class, 'sampleCsv'])->name('sample-csv');
            Route::get('/template-preview', [\App\Http\Controllers\CertificateController::class, 'preview'])->name('template-preview');
            Route::get('/test', \App\Livewire\Certificates\Test::class)->name('test');
        });

        // View / download issued certificates (owner or staff)
        Route::get('/{certificate}', \App\Livewire\Certificates\Show::class)->name('show');
        Route::get('/{certificate}/view', [\App\Http\Controllers\Certificates\CertificateDownloadController::class, 'view'])->name('view');
        Route::get('/{certificate}/download', [\App\Http\Controllers\Certificates\CertificateDownloadController::class, 'download'])->name('download');
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

    // ICT Internal Test Marks
    Route::get('/test-marks', \App\Livewire\TestMarks\Index::class)->name('test-marks.index');
    Route::get('/icdl-exam-marks', \App\Livewire\IcdlExamMarks\Index::class)->name('icdl-exam-marks.index');

    // Submissions Routes
    Route::prefix('submissions')->name('submissions.')->group(function () {
        Route::get('/', \App\Livewire\Submissions\Index::class)->name('index');
        Route::get('/file', \App\Http\Controllers\Submissions\FileDownloadController::class)->name('file');
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

    Route::middleware(['can:award_course_xp'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/xp-manager', \App\Livewire\Admin\XpManager::class)->name('xp-manager');
    });

    // Admin Routes - Enrollment Management (Admin/Supervisor only)
    Route::middleware(['can:manage_users'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/enrollments', \App\Livewire\Admin\EnrollmentManagement::class)->name('enrollments');
        Route::get('/schools', \App\Livewire\Admin\SchoolManagement::class)->name('schools');
        Route::get('/schools/{school}', \App\Livewire\Admin\SchoolShow::class)->name('schools.show');
        Route::get('/schools/{school}/students', \App\Livewire\Admin\SchoolStudents::class)->name('schools.students');
        Route::get('/icdl-workflow', \App\Livewire\Admin\IcdlWorkflow::class)->name('icdl-workflow');
            Route::get('/icdl-exam-marks', \App\Livewire\Admin\IcdlExamMarks::class)->name('icdl-exam-marks');
        Route::get('/settings', \App\Livewire\Admin\SystemSettings::class)->name('settings');
        Route::get('/feedback', \App\Livewire\Admin\ManageTeacherFeedback::class)->name('feedback');

        Route::prefix('camps')->name('camps.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Camps\Index::class)->name('index');
            Route::get('/{camp}', \App\Livewire\Admin\Camps\Show::class)->name('show');
        });

        Route::prefix('club-session-reports')->middleware('code_club.enabled')->name('club-session-reports.')->group(function () {
            Route::get('/', \App\Livewire\Admin\ClubSessionReports\Index::class)->name('index');
        });
        
        // Daily Reports Admin
        Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
            Route::get('/', \App\Livewire\Admin\DailyReports\Index::class)->name('index');
            Route::get('/{report}', \App\Livewire\Admin\DailyReports\Show::class)->name('show');
        });
        
        // Audit Log Routes
        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('logs');
            Route::get('/deleted-items', [\App\Http\Controllers\Admin\AuditLogController::class, 'deletedItems'])->name('deleted-items');
            Route::get('/{modelType}/{modelId}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('show');
            Route::post('/restore', [\App\Http\Controllers\Admin\AuditLogController::class, 'restore'])->name('restore');
            Route::post('/revert', [\App\Http\Controllers\Admin\AuditLogController::class, 'revert'])->name('revert');
            Route::post('/force-delete', [\App\Http\Controllers\Admin\AuditLogController::class, 'forceDelete'])->name('force-delete');
            Route::get('/export', [\App\Http\Controllers\Admin\AuditLogController::class, 'export'])->name('export');
        });
    });

    // Code Club admin — facilitators + admin/supervisor (not full manage_users)
    Route::middleware(['code_club.enabled', 'can:access_code_clubs'])->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('code-clubs')->name('code-clubs.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Clubs\Index::class)->name('index');
            Route::get('/{club}', \App\Livewire\Admin\Clubs\Show::class)->name('show');
            Route::get('/{club}/reports/bulk-download', [\App\Http\Controllers\Reports\CodeClubTermReportController::class, 'bulkDownload'])->name('reports.bulk-download');
            Route::get('/{club}/reports/school-summary', [\App\Http\Controllers\Reports\CodeClubTermReportController::class, 'schoolSummary'])->name('reports.school-summary');
            Route::get('/{club}/reports/{student}/download', [\App\Http\Controllers\Reports\CodeClubTermReportController::class, 'download'])->name('reports.download');
            Route::get('/{club}/reports/{student}/preview', [\App\Http\Controllers\Reports\CodeClubTermReportController::class, 'preview'])->name('reports.preview');
            Route::get('/{club}/reports/{student}/html', [\App\Http\Controllers\Reports\CodeClubTermReportController::class, 'html'])->name('reports.html');
        });
    });

    // Student Feedback Routes
    Route::middleware(['student.profile'])->prefix('feedback')->name('feedback.')->group(function () {
        Route::get('/teacher', \App\Livewire\Feedback\SubmitTeacherFeedback::class)->name('teacher');
        Route::post('/teacher/submit', [\App\Http\Controllers\Feedback\LessonFeedbackController::class, 'submit'])->name('teacher.submit');
    });

    // Student Management Routes - Teachers/Admin/Operations (requires authorization)
    Route::middleware(['can:manage_students'])->prefix('students')->name('students.')->group(function () {
        Route::get('/', \App\Livewire\Students\ManageStudents::class)->name('index');
        Route::get('/create', \App\Livewire\Students\StudentProgramSelect::class)->name('create');
        Route::get('/create/ict', \App\Livewire\Students\IctStudentForm::class)->name('create-ict');
        Route::get('/create/codecamp', \App\Livewire\Students\StudentForm::class)->name('create-codecamp');
        Route::middleware('code_club.enabled')->group(function () {
            Route::get('/create/codeclub', \App\Livewire\Students\CodeClubStudentForm::class)->name('create-codeclub');
            Route::get('/{student}/edit-codeclub', \App\Livewire\Students\CodeClubStudentForm::class)->name('edit-codeclub');
        });
        Route::get('/print-credentials/bulk', function () {
            $ids = collect(explode(',', (string) request('ids')))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($ids->isEmpty()) {
                abort(404);
            }

            $user = auth()->user();

            $query = \App\Models\StudentProfile::query()
                ->with(['user', 'school'])
                ->whereIn('id', $ids);

            if ($user->isIctTeacher()) {
                $schoolId = $user->ictSchoolId();
                if (!$schoolId) {
                    abort(403);
                }
                $query->where('program_type', 'ict')
                    ->where('school_id', $schoolId);
            } elseif ($user->isCodecampTrainer()) {
                $query->where('program_type', 'codecamp');
            } elseif ($user->hasCodeClubAccess()) {
                \App\Support\ProgramScope::applyStudentProfileScope($query, $user);
            }

            $students = $query->orderBy('full_name')->get();

            if ($students->isEmpty()) {
                abort(404);
            }

            return view('students.print-credentials-bulk', [
                'students' => $students,
            ]);
        })->name('bulk-print-credentials');
        Route::get('/{student}', \App\Livewire\Students\StudentProfile::class)->name('show');
        Route::get('/{student}/edit-ict', \App\Livewire\Students\IctStudentEdit::class)->name('edit-ict');
        Route::get('/{student}/edit', \App\Livewire\Students\StudentForm::class)->name('edit');
        Route::get('/{student}/teacher-update', \App\Livewire\Students\TeacherStudentUpdate::class)->name('teacher-update');
    });

    // Student Check-in/Checkout (accessible to all authenticated users)
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/check-in', \App\Livewire\Attendance\StudentCheckIn::class)->name('check-in');
    });

    // Teacher Code Display (accessible to teachers, operations managers, and admins)
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/code', \App\Livewire\Attendance\TeacherCodeDisplay::class)->name('code')
            ->middleware('can:view_teacher_code');
    });

    // Attendance Routes - Code Club facilitators
    Route::middleware(['code_club.enabled', 'can:access_club_attendance'])->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/club', \App\Livewire\Attendance\ClubAttendance::class)->name('club');
    });

    // Attendance Routes - CodeCamp / operations (requires manage_users)
    Route::middleware(['can:manage_users'])->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/students', \App\Livewire\Attendance\StudentAttendance::class)->name('student');
        Route::get('/instructors', \App\Livewire\Attendance\InstructorAttendance::class)->name('instructor');
        Route::get('/records', \App\Livewire\Attendance\AttendanceRecords::class)->name('records');
        Route::get('/dashboard', \App\Livewire\Attendance\AttendanceDashboard::class)->name('dashboard');
    });
});
