<?php

/**
 * Sidebar navigation, grouped into sections per role.
 * Each section: label, icon, items. Items with missing routes are skipped.
 */

$assessmentMatch = ['assessments.manage', 'assessments.create', 'assessments.edit', 'assessments.show', 'assessments.results'];
$certificates = ['label' => 'Certificates', 'route' => 'certificates.generator', 'icon' => 'academic-cap', 'match' => ['certificates.generator', 'certificates.generate'], 'keywords' => 'certificate generator issue'];

return [
    // Section keys (slugged labels) that start collapsed until the user opens them.
    'collapsed' => ['reports', 'icdl', 'system'],

    'admin' => [
        ['label' => 'People', 'icon' => 'users', 'items' => [
            ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'match' => 'admin.users.*', 'keywords' => 'accounts staff roles'],
            ['label' => 'Students', 'route' => 'students.index', 'icon' => 'user-group', 'match' => 'students.*', 'keywords' => 'learners children'],
            ['label' => 'Schools', 'route' => 'admin.schools', 'icon' => 'building-library', 'match' => 'admin.schools*'],
            ['label' => 'Enrollments', 'route' => 'admin.enrollments', 'icon' => 'clipboard-document-list', 'match' => 'admin.enrollments'],
            ['label' => 'Registration Requests', 'route' => 'admin.registration-requests', 'icon' => 'inbox-arrow-down', 'match' => 'admin.registration-requests', 'keywords' => 'signups applications'],
        ]],
        ['label' => 'Programs', 'icon' => 'flag', 'items' => [
            ['label' => 'Courses', 'route' => 'courses.index', 'icon' => 'book-open', 'match' => 'courses.*'],
            ['label' => 'Code Camps', 'route' => 'admin.camps.index', 'icon' => 'flag', 'match' => 'admin.camps.*'],
            ['label' => 'Code Clubs', 'route' => 'admin.code-clubs.index', 'icon' => 'building-storefront', 'match' => 'admin.code-clubs.*', 'feature' => 'code_club', 'roles' => ['admin', 'supervisor']],
        ]],
        ['label' => 'Teaching', 'icon' => 'academic-cap', 'items' => [
            ['label' => 'Curriculum', 'route' => 'curriculum.builder', 'icon' => 'squares-2x2', 'match' => 'curriculum.*', 'keywords' => 'builder modules lessons'],
            ['label' => 'Assessments', 'route' => 'assessments.manage', 'icon' => 'clipboard-document-check', 'match' => $assessmentMatch, 'keywords' => 'quiz test exam'],
            ['label' => 'Question Bank', 'route' => 'questions.index', 'icon' => 'archive-box', 'match' => 'questions.*', 'keywords' => 'questions'],
            ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'inbox-stack', 'match' => 'submissions.*', 'badge' => 'pending_submissions', 'keywords' => 'grading marking'],
            ['label' => 'Content Approval', 'route' => 'content-approvals.index', 'icon' => 'shield-check', 'match' => 'content-approvals.*', 'badge' => 'pending_approvals', 'keywords' => 'review approve'],
            ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
        ]],
        ['label' => 'Progress', 'icon' => 'trophy', 'items' => [
            ['label' => 'Student Progress', 'route' => 'admin.student-progress.index', 'icon' => 'presentation-chart-line', 'match' => 'admin.student-progress.*'],
            ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'calendar-days', 'match' => 'attendance.dashboard'],
            ['label' => 'Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
            $certificates,
            ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
            ['label' => 'XP Manager', 'route' => 'admin.xp-manager', 'icon' => 'bolt', 'match' => 'admin.xp-manager', 'keywords' => 'points award'],
        ]],
        ['label' => 'Reports', 'icon' => 'document-chart-bar', 'items' => [
            ['label' => 'Camp Reports', 'route' => 'admin.camp-reports.index', 'icon' => 'document-chart-bar', 'match' => ['admin.camp-reports.*', 'admin.camps.report'], 'keywords' => 'end of camp summary pdf attendance'],
            ['label' => 'Daily Reports', 'route' => 'admin.daily-reports.index', 'icon' => 'document-text', 'match' => 'admin.daily-reports.*'],
            ['label' => 'Club Session Reports', 'route' => 'admin.club-session-reports.index', 'icon' => 'clipboard-document', 'match' => 'admin.club-session-reports.*', 'feature' => 'code_club', 'roles' => ['admin', 'supervisor']],
            ['label' => 'Revised Content', 'route' => 'camp-revisions.index', 'icon' => 'document-arrow-up', 'match' => 'camp-revisions.*', 'badge' => 'pending_revisions', 'keywords' => 'end of camp trainer improvements'],
            ['label' => 'Teacher Feedback', 'route' => 'admin.feedback', 'icon' => 'chat-bubble-bottom-center-text', 'match' => 'admin.feedback', 'badge' => 'pending_feedback'],
        ]],
        ['label' => 'ICDL', 'icon' => 'computer-desktop', 'items' => [
            ['label' => 'ICDL Workflow', 'route' => 'admin.icdl-workflow', 'icon' => 'arrow-path-rounded-square', 'match' => 'admin.icdl-workflow'],
            ['label' => 'ICDL Exam Marks', 'route' => 'admin.icdl-exam-marks', 'icon' => 'document-check', 'match' => 'admin.icdl-exam-marks'],
        ]],
        ['label' => 'System', 'icon' => 'cog-6-tooth', 'items' => [
            ['label' => 'Audit Logs', 'route' => 'admin.audit.logs', 'icon' => 'finger-print', 'match' => 'admin.audit.*', 'keywords' => 'history activity'],
            ['label' => 'System Settings', 'route' => 'admin.settings', 'icon' => 'adjustments-horizontal', 'match' => 'admin.settings', 'keywords' => 'logo configuration'],
        ]],
    ],

    'codecamp_teacher' => [
        ['label' => 'Teaching', 'icon' => 'academic-cap', 'items' => [
            ['label' => 'My Courses', 'route' => 'courses.index', 'icon' => 'book-open', 'match' => 'courses.*'],
            ['label' => 'Curriculum', 'route' => 'curriculum.builder', 'icon' => 'squares-2x2', 'match' => 'curriculum.*', 'keywords' => 'builder modules lessons'],
            ['label' => 'Assessments', 'route' => 'assessments.manage', 'icon' => 'clipboard-document-check', 'match' => $assessmentMatch, 'keywords' => 'quiz test exam'],
            ['label' => 'Question Bank', 'route' => 'questions.index', 'icon' => 'archive-box', 'match' => 'questions.*'],
            ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'inbox-stack', 'match' => 'submissions.*', 'badge' => 'pending_submissions', 'keywords' => 'grading marking'],
            ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
        ]],
        ['label' => 'My Students', 'icon' => 'user-group', 'items' => [
            ['label' => 'Students', 'route' => 'students.index', 'icon' => 'user-group', 'match' => 'students.*'],
            ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'calendar-days', 'match' => 'attendance.dashboard'],
            ['label' => 'Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
            ['label' => 'Award XP', 'route' => 'admin.xp-manager', 'icon' => 'bolt', 'match' => 'admin.xp-manager', 'keywords' => 'points'],
            $certificates,
            ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
        ]],
        ['label' => 'Camps & Reports', 'icon' => 'flag', 'items' => [
            ['label' => 'Code Camps', 'route' => 'admin.camps.index', 'icon' => 'flag', 'match' => 'admin.camps.*'],
            ['label' => 'Daily Reports', 'route' => 'daily-reports.submit', 'icon' => 'document-text', 'match' => 'daily-reports.*'],
            ['label' => 'Revised Content', 'route' => 'camp-revisions.index', 'icon' => 'document-arrow-up', 'match' => 'camp-revisions.*', 'keywords' => 'end of camp improvements slides'],
        ]],
    ],

    'codeclub_facilitator' => [
        ['label' => 'My Club', 'icon' => 'building-storefront', 'items' => [
            ['label' => 'My Clubs', 'route' => 'admin.code-clubs.index', 'icon' => 'building-storefront', 'match' => 'admin.code-clubs.*', 'feature' => 'code_club'],
            ['label' => 'Students', 'route' => 'students.index', 'icon' => 'user-group', 'match' => 'students.*'],
            ['label' => 'Club Attendance', 'route' => 'attendance.club', 'icon' => 'calendar-days', 'match' => 'attendance.club', 'feature' => 'code_club'],
            ['label' => 'Daily Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code', 'feature' => 'code_club'],
            ['label' => 'Submit Session Report', 'route' => 'club-session-reports.submit', 'icon' => 'document-plus', 'match' => 'club-session-reports.*', 'feature' => 'code_club'],
            ['label' => 'My Session Reports', 'route' => 'admin.club-session-reports.index', 'icon' => 'document-text', 'match' => 'admin.club-session-reports.*', 'feature' => 'code_club', 'keywords' => 'comments feedback'],
        ]],
        ['label' => 'Teaching', 'icon' => 'academic-cap', 'items' => [
            ['label' => 'Assignments', 'route' => 'assignments.index', 'icon' => 'clipboard-document-check', 'match' => 'assignments.*'],
            ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'inbox-stack', 'match' => 'submissions.*', 'badge' => 'pending_submissions'],
            ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
            ['label' => 'Award XP', 'route' => 'admin.xp-manager', 'icon' => 'bolt', 'match' => 'admin.xp-manager'],
            ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
        ]],
    ],

    'ict_teacher' => [
        ['label' => 'ICT Teaching', 'icon' => 'computer-desktop', 'items' => [
            ['label' => 'Students', 'route' => 'students.index', 'icon' => 'user-group', 'match' => 'students.*'],
            ['label' => 'ICT Modules', 'route' => 'courses.index', 'icon' => 'book-open', 'match' => 'courses.*'],
            ['label' => 'Test Marks', 'route' => 'test-marks.index', 'icon' => 'clipboard-document-list', 'match' => 'test-marks.*'],
            ['label' => 'ICDL Exam Marks', 'route' => 'icdl-exam-marks.index', 'icon' => 'document-check', 'match' => 'icdl-exam-marks.*'],
        ]],
    ],

    'supervisor' => [
        ['label' => 'Oversight', 'icon' => 'eye', 'items' => [
            ['label' => 'Students', 'route' => 'students.index', 'icon' => 'user-group', 'match' => 'students.*', 'keywords' => 'learners children'],
            ['label' => 'Enrollments', 'route' => 'admin.enrollments', 'icon' => 'clipboard-document-list', 'match' => 'admin.enrollments'],
            ['label' => 'Code Camps', 'route' => 'admin.camps.index', 'icon' => 'flag', 'match' => 'admin.camps.*'],
            ['label' => 'Content Approval', 'route' => 'content-approvals.index', 'icon' => 'shield-check', 'match' => 'content-approvals.*', 'badge' => 'pending_approvals'],
            ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'inbox-stack', 'match' => 'submissions.*', 'badge' => 'pending_submissions'],
            ['label' => 'Lesson Locks', 'route' => 'lessons.locks', 'url' => '/lesson-locks', 'icon' => 'lock-closed', 'match' => 'lessons.locks'],
        ]],
        ['label' => 'Progress', 'icon' => 'trophy', 'items' => [
            ['label' => 'Student Progress', 'route' => 'admin.student-progress.index', 'icon' => 'presentation-chart-line', 'match' => 'admin.student-progress.*'],
            ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'calendar-days', 'match' => 'attendance.dashboard'],
            ['label' => 'Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
            ['label' => 'Award XP', 'route' => 'admin.xp-manager', 'icon' => 'bolt', 'match' => 'admin.xp-manager'],
            $certificates,
            ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
        ]],
        ['label' => 'Reports', 'icon' => 'document-chart-bar', 'items' => [
            ['label' => 'Camp Reports', 'route' => 'admin.camp-reports.index', 'icon' => 'document-chart-bar', 'match' => ['admin.camp-reports.*', 'admin.camps.report'], 'keywords' => 'end of camp summary pdf attendance'],
            ['label' => 'Daily Reports', 'route' => 'admin.daily-reports.index', 'icon' => 'document-text', 'match' => 'admin.daily-reports.*'],
            ['label' => 'Club Session Reports', 'route' => 'admin.club-session-reports.index', 'icon' => 'clipboard-document', 'match' => 'admin.club-session-reports.*', 'feature' => 'code_club'],
            ['label' => 'Revised Content', 'route' => 'camp-revisions.index', 'icon' => 'document-arrow-up', 'match' => 'camp-revisions.*', 'badge' => 'pending_revisions', 'keywords' => 'end of camp trainer improvements'],
        ]],
    ],

    'operations_manager' => [
        ['label' => 'Operations', 'icon' => 'briefcase', 'items' => [
            ['label' => 'Students', 'route' => 'students.index', 'icon' => 'user-group', 'match' => 'students.*'],
            ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'calendar-days', 'match' => 'attendance.*'],
            ['label' => 'Attendance Code', 'route' => 'attendance.code', 'icon' => 'key', 'match' => 'attendance.code'],
            $certificates,
            ['label' => 'Reports', 'route' => 'analytics.dashboard', 'icon' => 'chart-bar-square', 'match' => 'analytics.*'],
        ]],
    ],

    'codecamp_student' => [
        ['label' => 'My Learning', 'icon' => 'book-open', 'items' => [
            ['label' => 'My Courses', 'route' => 'enrollments.index', 'icon' => 'book-open', 'match' => 'enrollments.*'],
            ['label' => 'Assignments', 'route' => 'assignments.index', 'icon' => 'clipboard-document-check', 'match' => 'assignments.*'],
            ['label' => 'Attendance', 'route' => 'attendance.check-in', 'icon' => 'clock', 'match' => 'attendance.check-in', 'keywords' => 'check in'],
            ['label' => 'Certificates', 'route' => 'certificates.index', 'icon' => 'academic-cap', 'match' => 'certificates.*'],
            ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*'],
        ]],
    ],

    'codeclub_student' => [
        ['label' => 'Code Club', 'icon' => 'building-storefront', 'items' => [
            ['label' => 'My Courses', 'route' => 'enrollments.index', 'icon' => 'book-open', 'match' => 'enrollments.*', 'feature' => 'code_club'],
            ['label' => 'Assignments', 'route' => 'assignments.index', 'icon' => 'clipboard-document-check', 'match' => 'assignments.*', 'feature' => 'code_club'],
            ['label' => 'Quizzes', 'route' => 'assessments.index', 'icon' => 'puzzle-piece', 'match' => 'assessments.*', 'feature' => 'code_club'],
            ['label' => 'Attendance', 'route' => 'attendance.check-in', 'icon' => 'clock', 'match' => 'attendance.check-in', 'feature' => 'code_club'],
            ['label' => 'Leaderboard', 'route' => 'leaderboards.index', 'icon' => 'trophy', 'match' => 'leaderboards.*', 'feature' => 'code_club'],
        ]],
    ],

    'ict_student' => [
        ['label' => 'Learning', 'icon' => 'book-open', 'items' => [
            ['label' => 'My Modules', 'route' => 'enrollments.index', 'icon' => 'book-open', 'match' => 'enrollments.*'],
            ['label' => 'Internal Tests', 'route' => 'assessments.index', 'icon' => 'clipboard-document-check', 'match' => 'assessments.*'],
            ['label' => 'Certificates', 'route' => 'certificates.index', 'icon' => 'document-check', 'match' => 'certificates.*'],
        ]],
    ],
];
