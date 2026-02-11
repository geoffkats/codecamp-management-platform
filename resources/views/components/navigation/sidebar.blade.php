@props(['user'])

{{-- All data is now computed in the component class with caching --}}

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
        @if($showIctStudentSection)
            {{-- ICT/ICDL STUDENT SECTION --}}
            <flux:navlist.group :heading="__('ICDL')">
                <flux:navlist.item 
                    icon="home" 
                    :href="route('dashboard')" 
                    :current="request()->routeIs('dashboard')" 
                    wire:navigate
                >
                    {{ __('Dashboard') }}
                </flux:navlist.item>

                <flux:navlist.item 
                    icon="book-open" 
                    :href="route('enrollments.index')" 
                    :current="request()->routeIs('enrollments.*')" 
                    wire:navigate
                >
                    {{ __('My Modules') }}
                </flux:navlist.item>

                <flux:navlist.item 
                    icon="chart-bar" 
                    :href="route('progress.student')" 
                    :current="request()->routeIs('progress.student')" 
                    wire:navigate
                >
                    {{ __('Internal Tests & Progress') }}
                </flux:navlist.item>

                <flux:navlist.item 
                    icon="document-check" 
                    :href="route('certificates.index')" 
                    :current="request()->routeIs('certificates.*')" 
                    wire:navigate
                >
                    {{ __('Certificates') }}
                </flux:navlist.item>

                <flux:navlist.item 
                    icon="bell" 
                    :href="route('notifications.index')" 
                    :current="request()->routeIs('notifications.*')" 
                    wire:navigate
                >
                    {{ __('Notifications') }}
                </flux:navlist.item>

                <flux:navlist.item 
                    icon="cog-6-tooth" 
                    :href="route('profile.edit')" 
                    :current="request()->routeIs('profile.edit')" 
                    wire:navigate
                >
                    {{ __('Account Settings') }}
                </flux:navlist.item>
            </flux:navlist.group>
        @else
            {{-- CODECAMP STUDENT SECTION --}}
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
    @endif

    @if($showIctTeacherSection)
        {{-- ICT TEACHER SECTION --}}
        <flux:navlist.group :heading="__('ICT')">
            <flux:navlist.item 
                icon="users" 
                :href="route('students.index')" 
                :current="request()->routeIs('students.*')" 
                wire:navigate
            >
                {{ __('Students (My School)') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="book-open" 
                :href="route('courses.index')" 
                :current="request()->routeIs('courses.*')" 
                wire:navigate
            >
                {{ __('ICT Modules') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="clipboard-document-list" 
                :href="route('test-marks.index')" 
                :current="request()->routeIs('test-marks.*')" 
                wire:navigate
            >
                {{ __('Enter Test Marks') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="document-check" 
                :href="route('icdl-exam-marks.index')" 
                :current="request()->routeIs('icdl-exam-marks.*')" 
                wire:navigate
            >
                {{ __('ICDL Exam Marks') }}
            </flux:navlist.item>
        </flux:navlist.group>
    @elseif($showCodecampTeacherSection)
        {{-- CODECAMP TEACHER SECTION --}}
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

            <flux:navlist.item 
                icon="document-text" 
                :href="route('daily-reports.submit')" 
                :current="request()->routeIs('daily-reports.*')" 
                wire:navigate
            >
                {{ __('Daily Reports') }}
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
        {{-- ADMIN SECTION - SCHOOL ADMINISTRATION --}}
        <flux:navlist.group :heading="__('School Administration')">
            <flux:navlist.item 
                icon="users" 
                :href="route('admin.users.index')" 
                :current="request()->routeIs('admin.users.*')" 
                wire:navigate
            >
                {{ __('User Management') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="building-library" 
                :href="route('admin.schools')" 
                :current="request()->routeIs('admin.schools')" 
                wire:navigate
            >
                {{ __('Schools & Teachers') }}
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
                icon="document-text" 
                :href="route('admin.daily-reports.index')" 
                :current="request()->routeIs('admin.daily-reports.*')" 
                wire:navigate
            >
                {{ __('Daily Reports') }}
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

        {{-- ICDL EXAM MANAGEMENT --}}
        <flux:navlist.group :heading="__('ICDL Exam Management')">
            <flux:navlist.item 
                icon="clipboard-document-check" 
                :href="route('admin.icdl-workflow')" 
                :current="request()->routeIs('admin.icdl-workflow')" 
                wire:navigate
            >
                {{ __('ICDL Workflow') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="document-check" 
                :href="route('admin.icdl-exam-marks')" 
                :current="request()->routeIs('admin.icdl-exam-marks')" 
                wire:navigate
            >
                {{ __('ICDL Exam Marks') }}
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- ACADEMIC MANAGEMENT --}}
        <flux:navlist.group :heading="__('Academic Management')">
            <flux:navlist.item 
                icon="shield-check" 
                :href="route('content-approvals.index')" 
                :current="request()->routeIs('content-approvals.*')" 
                wire:navigate
            >
                {{ __('Content Approval') }}
                @if($pendingApprovalCount > 0)
                    <flux:badge size="sm" variant="danger">{{ $pendingApprovalCount }}</flux:badge>
                @endif
            </flux:navlist.item>

            <flux:navlist.item 
                icon="chat-bubble-bottom-center-text" 
                :href="route('admin.feedback')" 
                :current="request()->routeIs('admin.feedback')" 
                wire:navigate
            >
                {{ __('Teacher Feedback') }}
                @if($pendingFeedbackCount > 0)
                    <flux:badge size="sm" variant="danger">{{ $pendingFeedbackCount }}</flux:badge>
                @endif
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- GAMIFICATION & ENGAGEMENT --}}
        <flux:navlist.group :heading="__('Gamification & Engagement')">
            <flux:navlist.item 
                icon="star" 
                :href="route('admin.xp-manager')" 
                :current="request()->routeIs('admin.xp-manager')" 
                wire:navigate
            >
                {{ __('XP Manager') }}
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
                :href="route('badges.index', ['manage' => true])" 
                :current="request()->routeIs('badges.*') && request()->get('manage')" 
                wire:navigate
            >
                {{ __('Badge Management') }}
            </flux:navlist.item>
        </flux:navlist.group>

        {{-- ANALYTICS & REPORTING --}}
        <flux:navlist.group :heading="__('Analytics & Reporting')">
            <flux:navlist.item 
                icon="chart-bar-square" 
                :href="route('analytics.dashboard')" 
                :current="request()->routeIs('analytics.*')" 
                wire:navigate
            >
                {{ __('System Analytics') }}
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
                icon="star" 
                :href="route('admin.xp-manager')" 
                :current="request()->routeIs('admin.xp-manager')" 
                wire:navigate
            >
                {{ __('XP Manager') }}
            </flux:navlist.item>

            <flux:navlist.item 
                icon="shield-check" 
                :href="route('content-approvals.index')" 
                :current="request()->routeIs('content-approvals.*')" 
                wire:navigate
            >
                {{ __('Content Approval') }}
                @if($pendingApprovalCount > 0)
                    <flux:badge size="sm" variant="danger">{{ $pendingApprovalCount }}</flux:badge>
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
            @if($unreadNotificationsCount > 0)
                <flux:badge size="sm" variant="primary">{{ $unreadNotificationsCount }}</flux:badge>
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
