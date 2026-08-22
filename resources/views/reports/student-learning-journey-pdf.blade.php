<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 24px;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 14px;
            margin-top: 18px;
            margin-bottom: 8px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
        }

        .meta {
            margin-top: 6px;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .stats-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .stats-grid td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            width: 50%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 700;
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            vertical-align: top;
        }

        .muted {
            color: #6b7280;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <h1>Student Learning Journey Report</h1>
    <div class="meta">
        <strong>Student:</strong> {{ $user->name }}<br>
        <strong>Email:</strong> {{ $user->email }}<br>
        <strong>Generated:</strong> {{ now()->format('d M Y H:i') }}<br>
        <strong>Filters:</strong>
        Date From {{ $filters['date_from'] ?: 'All' }},
        Date To {{ $filters['date_to'] ?: 'All' }},
        Course {{ $filters['course_id'] === 'all' ? 'All Courses' : ('ID ' . $filters['course_id']) }}
    </div>

    <h2>Overview</h2>
    <table class="stats-grid">
        <tr>
            <td><strong>Courses Tracked</strong>: {{ $journeyStats['courses_tracked'] }}</td>
            <td><strong>Lessons Covered</strong>: {{ $journeyStats['lessons_covered'] }}</td>
        </tr>
        <tr>
            <td><strong>Challenge Completion</strong>: {{ $journeyStats['challenge_completed'] }}/{{ $journeyStats['challenge_attempts'] }}</td>
            <td><strong>Assessment Pass Rate</strong>: {{ $journeyStats['assessment_pass_rate'] }}%</td>
        </tr>
        <tr>
            <td><strong>Assignments Graded</strong>: {{ $journeyStats['assignment_graded'] }}/{{ $journeyStats['assignment_submissions'] }}</td>
            <td><strong>Attendance Rate</strong>: {{ $journeyStats['attendance_rate'] !== null ? number_format($journeyStats['attendance_rate'], 1) . '%' : 'N/A' }}</td>
        </tr>
    </table>

    <h2>Course Journey</h2>
    <table>
        <thead>
            <tr>
                <th>Course</th>
                <th>Progress</th>
                <th>Lessons</th>
                <th>Quizzes</th>
                <th>Avg Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courseJourney as $item)
                <tr>
                    <td>{{ $item->course?->title ?? 'N/A' }}</td>
                    <td>{{ number_format((float) ($item->progress_percentage ?? 0), 1) }}%</td>
                    <td>{{ $item->lessons_completed ?? 0 }}</td>
                    <td>{{ $item->quizzes_completed ?? 0 }}</td>
                    <td>{{ number_format((float) ($item->average_quiz_score ?? 0), 1) }}%</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No records</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2>Lessons Covered</h2>
    <table>
        <thead>
            <tr>
                <th>Lesson</th>
                <th>Course</th>
                <th>Type</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessonProgress as $item)
                <tr>
                    <td>{{ $item->lesson?->title ?? 'Lesson #' . $item->lesson_id }}</td>
                    <td>{{ $item->course?->title ?? 'N/A' }}</td>
                    <td>{{ strtoupper((string) $item->type) }}</td>
                    <td>{{ $item->score !== null ? number_format((float) $item->score, 1) . '%' : '—' }}</td>
                    <td>{{ optional($item->completed_at ?? $item->created_at)->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No records</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Daily Challenges</h2>
    <table>
        <thead>
            <tr>
                <th>Challenge</th>
                <th>Difficulty</th>
                <th>Points</th>
                <th>Status</th>
                <th>Attempted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($challengeAttempts as $item)
                <tr>
                    <td>{{ $item->challenge?->title ?? 'Challenge #' . $item->challenge_id }}</td>
                    <td>{{ ucfirst($item->challenge?->difficulty_level ?? 'N/A') }}</td>
                    <td>{{ (int) ($item->points_earned ?? 0) }}</td>
                    <td>{{ $item->is_completed ? 'Completed' : 'Attempted' }}</td>
                    <td>{{ optional($item->attempted_at ?? $item->created_at)->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No records</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2>Assessments & Results</h2>
    <table>
        <thead>
            <tr>
                <th>Assessment</th>
                <th>Type</th>
                <th>Score</th>
                <th>Result</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessmentAttempts as $item)
                <tr>
                    <td>{{ $item->assessment?->title ?? 'Assessment #' . $item->assessment_id }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', (string) $item->assessment?->assessment_type)) }}</td>
                    <td>{{ $item->score !== null ? number_format((float) $item->score, 1) . '%' : '—' }}</td>
                    <td>{{ $item->is_passed ? 'Passed' : 'Not Passed' }}</td>
                    <td>{{ optional($item->completed_at ?? $item->started_at)->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No records</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Assignment Submissions</h2>
    <table>
        <thead>
            <tr>
                <th>Assignment</th>
                <th>Status</th>
                <th>Points</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignmentSubmissions as $item)
                <tr>
                    <td>{{ $item->assignment?->title ?? 'Assignment #' . $item->assignment_id }}</td>
                    <td>{{ ucfirst((string) $item->status) }}</td>
                    <td>{{ $item->points_earned !== null ? number_format((float) $item->points_earned, 1) : '—' }}</td>
                    <td>{{ optional($item->submitted_at ?? $item->created_at)->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No records</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Attendance</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Course</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendanceRecords as $item)
                <tr>
                    <td>{{ optional($item->attendance_date)->format('d M Y') }}</td>
                    <td>{{ ucfirst((string) $item->status) }}</td>
                    <td>{{ $item->course?->title ?? 'General' }}</td>
                    <td>{{ $item->clockInCarbon()?->format('H:i') ?? '--:--' }} - {{ $item->clockOutCarbon()?->format('H:i') ?? '--:--' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No records</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Learning Timeline (Latest 40 events)</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Title</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($learningTimeline as $event)
                <tr>
                    <td>{{ optional($event['at'])->format('d M Y H:i') }}</td>
                    <td>{{ ucfirst((string) $event['type']) }}</td>
                    <td>{{ $event['title'] }}</td>
                    <td>{{ $event['detail'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No timeline events</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
