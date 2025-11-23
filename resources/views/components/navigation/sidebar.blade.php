@props(['user'])

@php
    // Determine user's primary role for sidebar organization
    // Priority: Admin > Supervisor > Teacher > Student
    $isAdmin = $user->isAdmin();
    $isSupervisor = $user->isSupervisor();
    $isTeacher = $user->isTeacher();
    $isStudent = $user->isStudent();
    
    // Show role-specific sections only if user has that role and is not a higher role
    $showStudentSection = $isStudent && !$isAdmin && !$isTeacher;
    $showTeacherSection = $isTeacher && !$isAdmin;
    $showSupervisorSection = $isSupervisor && !$isAdmin;
@endphp

<flux:navlist variant="outline">
    <!-- Dashboard - All Roles -->
    <flux:navlist.group :heading="__('Main')">
        <flux:navlist.item 
            icon="home" 
            :href="route('dashboard')" 
            :current="request()->routeIs('dashboard')" 
            wire:navigate
        >
            {{ __('Dashboard') }}
        </flux:navlist.item>
    </flux:navlist.group>

    @if($showStudentSection && $user->studentProfile)
        {{-- STUDENT SECTION (Only for pure students with student profile, not admins or teachers) --}}
        <flux:navlist.group :heading="__('Learning')">
            <flux:navlist.item 
                icon="book-open" 
                :href="route('courses.index')" 
                :current="request()->routeIs('courses.*')" 
                wire:navigate
            >
                {{ __('Courses') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="calendar-days" 
                :href="route('enrollments.index')" 
                :current="request()->routeIs('enrollments.*')" 
                wire:navigate
            >
                {{ __('My Enrollments') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="envelope" 
                :href="route('invitations.index')" 
                :current="request()->routeIs('invitations.*')" 
                wire:navigate
            >
                {{ __('Invitations') }}
                @php
                    $pendingInvitationsCount = \App\Models\CourseInvitation::where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->where(function ($q) {
                            $q->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                        })
                        ->count();
                @endphp
                @if($pendingInvitationsCount > 0)
                    <flux:badge size="sm" variant="primary">{{ $pendingInvitationsCount }}</flux:badge>
                @endif
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar" 
                :href="route('progress.student')" 
                :current="request()->routeIs('progress.*')" 
                wire:navigate
            >
                {{ __('My Progress') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="trophy" 
                :href="route('leaderboards.index')" 
                :current="request()->routeIs('leaderboards.*')" 
                wire:navigate
            >
                {{ __('Leaderboard') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="sparkles" 
                :href="route('daily-challenges.index')" 
                :current="request()->routeIs('daily-challenges.*')" 
                wire:navigate
            >
                {{ __('Daily Challenges') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="trophy" 
                :href="route('badges.index')" 
                :current="request()->routeIs('badges.*')" 
                wire:navigate
            >
                {{ __('Badges') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="academic-cap" 
                :href="route('certificates.index')" 
                :current="request()->routeIs('certificates.*')" 
                wire:navigate
            >
                {{ __('Certificates') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chat-bubble-left-right" 
                :href="route('feedback.teacher')" 
                :current="request()->routeIs('feedback.teacher')" 
                wire:navigate
            >
                {{ __('Teacher Feedback') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clock" 
                :href="route('attendance.check-in')" 
                :current="request()->routeIs('attendance.check-in')" 
                wire:navigate
            >
                {{ __('Check In/Out') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group :heading="__('Activities')">
            <flux:navlist.item 
                icon="clipboard-document-check" 
                :href="route('assignments.index')" 
                :current="request()->routeIs('assignments.*')" 
                wire:navigate
            >
                {{ __('Assignments') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chat-bubble-left-right" 
                :href="route('discussions.index')" 
                :current="request()->routeIs('discussions.*')" 
                wire:navigate
            >
                {{ __('Discussions') }}
            </flux:navlist.item>
        </flux:navlist.group>
    @endif

    @if($showTeacherSection)
        {{-- TEACHER SECTION (Only for teachers who are not admins) --}}
        <flux:navlist.group :heading="__('Teaching')">
            <flux:navlist.item 
                icon="academic-cap" 
                :href="route('courses.index')" 
                :current="request()->routeIs('courses.*') && !request()->routeIs('courses.create') && !request()->routeIs('courses.edit')" 
                wire:navigate
            >
                {{ __('My Courses') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="squares-2x2" 
                :href="route('curriculum.builder')" 
                :current="request()->routeIs('curriculum.*')" 
                wire:navigate
            >
                {{ __('Curriculum Builder') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="users" 
                :href="route('students.index')" 
                :current="request()->routeIs('students.*')" 
                wire:navigate
            >
                {{ __('Students') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar" 
                :href="route('attendance.dashboard')" 
                :current="request()->routeIs('attendance.dashboard')" 
                wire:navigate
            >
                {{ __('Attendance Dashboard') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="qr-code" 
                :href="route('attendance.code')" 
                :current="request()->routeIs('attendance.code')" 
                wire:navigate
            >
                {{ __('Daily Code') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clipboard-document-list" 
                :href="route('submissions.index')" 
                :current="request()->routeIs('submissions.*')" 
                wire:navigate
            >
                {{ __('Submissions') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="star" 
                :href="route('grades.index')" 
                :current="request()->routeIs('grades.*')" 
                wire:navigate
            >
                {{ __('Grades') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar-square" 
                :href="route('analytics.dashboard')" 
                :current="request()->routeIs('analytics.*')" 
                wire:navigate
            >
                {{ __('Analytics') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group :heading="__('Learning')">
            <flux:navlist.item 
                icon="book-open" 
                :href="route('courses.index')" 
                :current="request()->routeIs('courses.index') && !request()->routeIs('courses.create') && !request()->routeIs('courses.edit')" 
                wire:navigate
            >
                {{ __('Browse Courses') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clipboard-document-check" 
                :href="route('assignments.index')" 
                :current="request()->routeIs('assignments.*')" 
                wire:navigate
            >
                {{ __('Assignments') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chat-bubble-left-right" 
                :href="route('discussions.index')" 
                :current="request()->routeIs('discussions.*')" 
                wire:navigate
            >
                {{ __('Discussions') }}
            </flux:navlist.item>
        </flux:navlist.group>
    @endif

    @if($isAdmin)
        {{-- ADMIN SECTION --}}
        <flux:navlist.group :heading="__('Administration')">
            <flux:navlist.item 
                icon="users" 
                :href="route('admin.users.index')" 
                :current="request()->routeIs('admin.users.*')" 
                wire:navigate
            >
                {{ __('User Management') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="user-group" 
                :href="route('admin.enrollments')" 
                :current="request()->routeIs('admin.enrollments')" 
                wire:navigate
            >
                {{ __('Enrollment Management') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="shield-check" 
                :href="route('content-approvals.index')" 
                :current="request()->routeIs('content-approvals.*')" 
                wire:navigate
            >
                {{ __('Content Approval') }}
                @php
                    $pendingCount = \App\Models\Course::where('approval_status', 'pending')->count() + 
                                   \App\Models\Lesson::where('approval_status', 'pending')->count() + 
                                   \App\Models\Assessment::where('approval_status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <flux:badge size="sm" variant="danger">{{ $pendingCount }}</flux:badge>
                @endif
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar-square" 
                :href="route('analytics.dashboard')" 
                :current="request()->routeIs('analytics.*')" 
                wire:navigate
            >
                {{ __('System Analytics') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="sparkles" 
                :href="route('badges.index', ['manage' => true])" 
                :current="request()->routeIs('badges.*') && request()->get('manage')" 
                wire:navigate
            >
                {{ __('Badge Management') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chat-bubble-bottom-center-text" 
                :href="route('admin.feedback')" 
                :current="request()->routeIs('admin.feedback')" 
                wire:navigate
            >
                {{ __('Teacher Feedback') }}
                @php
                    $pendingFeedback = \App\Models\TeacherFeedback::where('status', 'pending')->count();
                @endphp
                @if($pendingFeedback > 0)
                    <flux:badge size="sm" variant="danger">{{ $pendingFeedback }}</flux:badge>
                @endif
            </flux:navlist.item>

            <flux:navlist.item 
                icon="cog-6-tooth" 
                :href="route('admin.settings')" 
                :current="request()->routeIs('admin.settings')" 
                wire:navigate
            >
                {{ __('System Settings') }}
            </flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group :heading="__('Content Management')">
            <flux:navlist.item 
                icon="academic-cap" 
                :href="route('courses.index')" 
                :current="request()->routeIs('courses.*')" 
                wire:navigate
            >
                {{ __('Courses') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="squares-2x2" 
                :href="route('curriculum.builder')" 
                :current="request()->routeIs('curriculum.*')" 
                wire:navigate
            >
                {{ __('Curriculum Builder') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="users" 
                :href="route('students.index')" 
                :current="request()->routeIs('students.*')" 
                wire:navigate
            >
                {{ __('Students') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar" 
                :href="route('attendance.dashboard')" 
                :current="request()->routeIs('attendance.dashboard')" 
                wire:navigate
            >
                {{ __('Attendance Dashboard') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clipboard-document-check" 
                :href="route('attendance.student')" 
                :current="request()->routeIs('attendance.student')" 
                wire:navigate
            >
                {{ __('Manual Attendance') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="qr-code" 
                :href="route('attendance.code')" 
                :current="request()->routeIs('attendance.code')" 
                wire:navigate
            >
                {{ __('Daily Code') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clock" 
                :href="route('attendance.check-in')" 
                :current="request()->routeIs('attendance.check-in')" 
                wire:navigate
            >
                {{ __('Check In/Out') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chat-bubble-left-right" 
                :href="route('discussions.index')" 
                :current="request()->routeIs('discussions.*')" 
                wire:navigate
            >
                {{ __('Discussions') }}
            </flux:navlist.item>
        </flux:navlist.group>
    @endif

    @if($user->hasRole('operations_manager'))
        {{-- OPERATIONS MANAGER SECTION --}}
        <flux:navlist.group :heading="__('Operations')">
            <flux:navlist.item 
                icon="users" 
                :href="route('students.index')" 
                :current="request()->routeIs('students.*')" 
                wire:navigate
            >
                {{ __('Students') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar" 
                :href="route('attendance.dashboard')" 
                :current="request()->routeIs('attendance.dashboard')" 
                wire:navigate
            >
                {{ __('Attendance Dashboard') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clipboard-document-check" 
                :href="route('attendance.student')" 
                :current="request()->routeIs('attendance.student')" 
                wire:navigate
            >
                {{ __('Manual Attendance') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="qr-code" 
                :href="route('attendance.code')" 
                :current="request()->routeIs('attendance.code')" 
                wire:navigate
            >
                {{ __('Daily Code') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clock" 
                :href="route('attendance.check-in')" 
                :current="request()->routeIs('attendance.check-in')" 
                wire:navigate
            >
                {{ __('Check In/Out') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="user-circle" 
                :href="route('attendance.instructor')" 
                :current="request()->routeIs('attendance.instructor')" 
                wire:navigate
            >
                {{ __('Instructor Attendance') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar" 
                :href="route('analytics.dashboard')" 
                :current="request()->routeIs('analytics.*')" 
                wire:navigate
            >
                {{ __('Reports') }}
            </flux:navlist.item>
        </flux:navlist.group>
    @endif

    @if($showSupervisorSection)
        {{-- SUPERVISOR SECTION (Only if not admin) --}}
        <flux:navlist.group :heading="__('Administration')">
            <flux:navlist.item 
                icon="user-group" 
                :href="route('admin.enrollments')" 
                :current="request()->routeIs('admin.enrollments')" 
                wire:navigate
            >
                {{ __('Enrollment Management') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="shield-check" 
                :href="route('content-approvals.index')" 
                :current="request()->routeIs('content-approvals.*')" 
                wire:navigate
            >
                {{ __('Content Approval') }}
                @php
                    $pendingCount = \App\Models\Course::where('approval_status', 'pending')->count() + 
                                   \App\Models\Lesson::where('approval_status', 'pending')->count() + 
                                   \App\Models\Assessment::where('approval_status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <flux:badge size="sm" variant="danger">{{ $pendingCount }}</flux:badge>
                @endif
            </flux:navlist.item>

            <flux:navlist.item 
                icon="squares-2x2" 
                :href="route('curriculum.builder')" 
                :current="request()->routeIs('curriculum.*')" 
                wire:navigate
            >
                {{ __('Curriculum Builder') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar-square" 
                :href="route('analytics.dashboard')" 
                :current="request()->routeIs('analytics.*')" 
                wire:navigate
            >
                {{ __('System Analytics') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chart-bar" 
                :href="route('attendance.dashboard')" 
                :current="request()->routeIs('attendance.dashboard')" 
                wire:navigate
            >
                {{ __('Attendance Dashboard') }}
            </flux:navlist.item>
        </flux:navlist.group>
    @endif

    <!-- Common - All Roles -->
    <flux:navlist.group :heading="__('Account')">
        <flux:navlist.item 
            icon="bell" 
            :href="route('notifications.index')" 
            :current="request()->routeIs('notifications.*')" 
            wire:navigate
        >
            {{ __('Notifications') }}
            @php
                $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            @endphp
            @if($unreadCount > 0)
                <flux:badge size="sm" variant="primary">{{ $unreadCount }}</flux:badge>
            @endif
        </flux:navlist.item>

        <flux:navlist.item 
            icon="cog-6-tooth" 
            :href="route('profile.edit')" 
            :current="request()->routeIs('settings.*')" 
            wire:navigate
        >
            {{ __('Settings') }}
        </flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
