<?php

/**
 * Primary navigation — keep sidebars short; secondary tools live under "More".
 * Routes must exist; items with missing routes are skipped in the view.
 */

return [
    'admin' => [
        'primary' => [
            ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'match' => 'admin.users.*'],
            ['label' => 'Students', 'route' => 'students.index', 'icon' => 'user-group', 'match' => 'students.*'],
            ['label' => 'Schools', 'route' => 'admin.schools', 'icon' => 'building-library', 'match' => 'admin.schools*'],
            ['label' => 'Enrollments', 'route' => 'admin.enrollments', 'icon' => 'clipboard-document-list', 'match' => 'admin.enrollments'],
            ['label' => 'Code Camps', 'route' => 'admin.camps.index', 'icon' => 'flag', 'match' => 'admin.camps.*'],
            ['label' => 'Code Clubs', 'route' => 'admin.code-clubs.index', 'icon' => 'building-library', 'match' => 'admin.code-clubs.*', 'feature' => 'code_club', 'roles' => ['admin', 'supervisor']],
        ],
        'programs' => [
            ['label' => 'Courses', 'route' => 'courses.index', 'icon' => 'academic-cap', 'match' => 'courses.*'],
            ['label' => 'Curriculum', 'route' => 'curriculum.builder', 'icon' => 'squares-2x2', 'match' => 'curriculum.*'],
            ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'clipboard-document-list', 'match' => 'submissions.*', 'badge' => 'pending_submissions'],
            ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
            ['label' => 'Content Approval', 'route' => 'content-approvals.index', 'icon' => 'shield-check', 'match' => 'content-approvals.*', 'badge' => 'pending_approvals'],
            ['label' => 'Daily Reports', 'route' => 'admin.daily-reports.index', 'icon' => 'document-text', 'match' => 'admin.daily-reports.*'],
            ['label' => 'Club Session Reports', 'route' => 'admin.club-session-reports.index', 'icon' => 'document-text', 'match' => 'admin.club-session-reports.*', 'feature' => 'code_club', 'roles' => ['admin', 'supervisor']],
            ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'chart-bar', 'match' => 'attendance.dashboard'],
            ['label' => 'Daily Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
            ['label' => 'Certificate Generator', 'route' => 'certificates.generator', 'icon' => 'academic-cap', 'match' => 'certificates.generator'],
            ['label' => 'Student Progress', 'route' => 'admin.student-progress.index', 'icon' => 'presentation-chart-line', 'match' => 'admin.student-progress.*'],
            ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
            ['label' => 'XP Manager', 'route' => 'admin.xp-manager', 'icon' => 'bolt', 'match' => 'admin.xp-manager'],
        ],
        'more' => [
            ['label' => 'ICDL Workflow', 'route' => 'admin.icdl-workflow', 'icon' => 'clipboard-document-check', 'match' => 'admin.icdl-workflow'],
            ['label' => 'ICDL Exam Marks', 'route' => 'admin.icdl-exam-marks', 'icon' => 'document-check', 'match' => 'admin.icdl-exam-marks'],
            ['label' => 'Teacher Feedback', 'route' => 'admin.feedback', 'icon' => 'chat-bubble-bottom-center-text', 'match' => 'admin.feedback', 'badge' => 'pending_feedback'],
            ['label' => 'Registration Requests', 'route' => 'admin.registration-requests', 'icon' => 'clipboard-document-check', 'match' => 'admin.registration-requests'],
            ['label' => 'Audit Logs', 'route' => 'admin.audit.logs', 'icon' => 'shield-check', 'match' => 'admin.audit.*'],
            ['label' => 'Settings', 'route' => 'admin.settings', 'icon' => 'cog-6-tooth', 'match' => 'admin.settings'],
        ],
    ],

    'codecamp_teacher' => [
        ['label' => 'My Courses', 'route' => 'courses.index', 'icon' => 'academic-cap', 'match' => 'courses.*'],
        ['label' => 'Students', 'route' => 'students.index', 'icon' => 'users', 'match' => 'students.*'],
        ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'clipboard-document-list', 'match' => 'submissions.*', 'badge' => 'pending_submissions'],
        ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
        ['label' => 'Code Camps', 'route' => 'admin.camps.index', 'icon' => 'flag', 'match' => 'admin.camps.*'],
        ['label' => 'Daily Reports', 'route' => 'daily-reports.submit', 'icon' => 'document-text', 'match' => 'daily-reports.*'],
        ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'chart-bar', 'match' => 'attendance.dashboard'],
        ['label' => 'Daily Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
        ['label' => 'Certificate Generator', 'route' => 'certificates.generator', 'icon' => 'academic-cap', 'match' => 'certificates.generator'],
        ['label' => 'Curriculum', 'route' => 'curriculum.builder', 'icon' => 'squares-2x2', 'match' => 'curriculum.*'],
        ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
    ],

    'codeclub_facilitator' => [
        ['label' => 'My Clubs', 'route' => 'admin.code-clubs.index', 'icon' => 'building-library', 'match' => 'admin.code-clubs.*', 'feature' => 'code_club'],
        ['label' => 'Students', 'route' => 'students.index', 'icon' => 'users', 'match' => 'students.*'],
        ['label' => 'Club Attendance', 'route' => 'attendance.club', 'icon' => 'chart-bar', 'match' => 'attendance.club', 'feature' => 'code_club'],
        ['label' => 'Daily Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code', 'feature' => 'code_club'],
        ['label' => 'Session Reports', 'route' => 'club-session-reports.submit', 'icon' => 'document-text', 'match' => 'club-session-reports.*', 'feature' => 'code_club'],
        ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'clipboard-document-list', 'match' => 'submissions.*', 'badge' => 'pending_submissions'],
        ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
        ['label' => 'Assignments', 'route' => 'assignments.index', 'icon' => 'clipboard-document-check', 'match' => 'assignments.*'],
        ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
    ],

    'ict_teacher' => [
        ['label' => 'Students', 'route' => 'students.index', 'icon' => 'users', 'match' => 'students.*'],
        ['label' => 'ICT Modules', 'route' => 'courses.index', 'icon' => 'book-open', 'match' => 'courses.*'],
        ['label' => 'Test Marks', 'route' => 'test-marks.index', 'icon' => 'clipboard-document-list', 'match' => 'test-marks.*'],
        ['label' => 'ICDL Exam Marks', 'route' => 'icdl-exam-marks.index', 'icon' => 'document-check', 'match' => 'icdl-exam-marks.*'],
    ],

    'supervisor' => [
        ['label' => 'Enrollments', 'route' => 'admin.enrollments', 'icon' => 'user-group', 'match' => 'admin.enrollments'],
        ['label' => 'Code Camps', 'route' => 'admin.camps.index', 'icon' => 'flag', 'match' => 'admin.camps.*'],
        ['label' => 'Content Approval', 'route' => 'content-approvals.index', 'icon' => 'shield-check', 'match' => 'content-approvals.*', 'badge' => 'pending_approvals'],
        ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'clipboard-document-list', 'match' => 'submissions.*', 'badge' => 'pending_submissions'],
        ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
        ['label' => 'Daily Reports', 'route' => 'admin.daily-reports.index', 'icon' => 'document-text', 'match' => 'admin.daily-reports.*'],
        ['label' => 'Student Progress', 'route' => 'admin.student-progress.index', 'icon' => 'presentation-chart-line', 'match' => 'admin.student-progress.*'],
        ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'chart-bar', 'match' => 'attendance.dashboard'],
        ['label' => 'Daily Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
        ['label' => 'Certificate Generator', 'route' => 'certificates.generator', 'icon' => 'academic-cap', 'match' => 'certificates.generator'],
        ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
    ],

    'operations_manager' => [
        ['label' => 'Students', 'route' => 'students.index', 'icon' => 'users', 'match' => 'students.*'],
        ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'chart-bar', 'match' => 'attendance.*'],
        ['label' => 'Daily Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
        ['label' => 'Certificate Generator', 'route' => 'certificates.generator', 'icon' => 'academic-cap', 'match' => 'certificates.generator'],
        ['label' => 'Reports', 'route' => 'analytics.dashboard', 'icon' => 'chart-bar-square', 'match' => 'analytics.*'],
    ],

    'codecamp_student' => [
        ['label' => 'My Courses', 'route' => 'enrollments.index', 'icon' => 'book-open', 'match' => 'enrollments.*'],
        ['label' => 'Assignments', 'route' => 'assignments.index', 'icon' => 'clipboard-document-check', 'match' => 'assignments.*'],
        ['label' => 'Attendance', 'route' => 'attendance.check-in', 'icon' => 'clock', 'match' => 'attendance.check-in'],
        ['label' => 'Certificates', 'route' => 'certificates.index', 'icon' => 'academic-cap', 'match' => 'certificates.*'],
        ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
    ],

    'codeclub_student' => [
        ['label' => 'My Courses', 'route' => 'enrollments.index', 'icon' => 'book-open', 'match' => 'enrollments.*', 'feature' => 'code_club'],
        ['label' => 'Assignments', 'route' => 'assignments.index', 'icon' => 'clipboard-document-check', 'match' => 'assignments.*', 'feature' => 'code_club'],
        ['label' => 'Quizzes', 'route' => 'assessments.index', 'icon' => 'chart-bar', 'match' => 'assessments.*', 'feature' => 'code_club'],
        ['label' => 'Attendance', 'route' => 'attendance.check-in', 'icon' => 'clock', 'match' => 'attendance.check-in', 'feature' => 'code_club'],
        ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*', 'feature' => 'code_club'],
    ],

    'ict_student' => [
        ['label' => 'My Modules', 'route' => 'enrollments.index', 'icon' => 'book-open', 'match' => 'enrollments.*'],
        ['label' => 'Internal Tests', 'route' => 'assessments.index', 'icon' => 'chart-bar', 'match' => 'assessments.*'],
        ['label' => 'Certificates', 'route' => 'certificates.index', 'icon' => 'document-check', 'match' => 'certificates.*'],
    ],
];
