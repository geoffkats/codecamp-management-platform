<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignmentSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run UserSeeder first.');
            return;
        }

        // Get assignments from assessments table or assignments table
        $assignmentsFromAssessments = \App\Models\Assessment::where('assessment_type', 'assignment')
            ->where('approval_status', 'approved')
            ->get();

        $assignmentsFromTable = Assignment::all();
        
        $allAssignments = collect();
        
        // Create assignments from assessments if assignments table is empty
        if ($assignmentsFromAssessments->isNotEmpty() && $assignmentsFromTable->isEmpty()) {
            foreach ($assignmentsFromAssessments as $assessment) {
                $assignment = Assignment::firstOrCreate([
                    'lesson_id' => $assessment->lesson_id,
                    'course_id' => $assessment->course_id,
                ], [
                    'title' => $assessment->title,
                    'description' => $assessment->description,
                    'instructions' => 'Complete this assignment according to the lesson requirements.',
                    'due_date' => now()->addDays(rand(7, 30)),
                    'max_points' => 100,
                    'status' => 'active',
                    'created_by' => \App\Models\User::whereHas('roles', function($q) {
                        $q->where('name', 'teacher');
                    })->inRandomOrder()->first()?->id ?? 1,
                ]);
                $allAssignments->push($assignment);
            }
        } else {
            $allAssignments = $assignmentsFromTable;
        }

        if ($allAssignments->isEmpty()) {
            $this->command->warn('No assignments found. Please run AssessmentSeeder first.');
            return;
        }

        $submissionCount = 0;

        foreach ($students as $student) {
            // Each student submits 2-4 random assignments
            $studentAssignments = $allAssignments->random(rand(2, min(4, $allAssignments->count())));

            foreach ($studentAssignments as $assignment) {

                $submittedAt = now()->subDays(rand(1, 30));
                $isGraded = rand(0, 100) > 40; // 60% graded
                $pointsEarned = $isGraded ? rand(60, 100) : null;

                AssignmentSubmission::create([
                    'user_id' => $student->id,
                    'assignment_id' => $assignment->id,
                    'submitted_at' => $submittedAt,
                    'content' => $this->generateSubmissionContent(),
                    'attachments' => $isGraded ? ['/uploads/submissions/sample-submission.pdf'] : null,
                    'points_earned' => $pointsEarned,
                    'feedback' => $isGraded ? $this->generateFeedback($pointsEarned) : null,
                    'status' => $isGraded ? 'graded' : ($submittedAt < now() ? 'submitted' : 'draft'),
                    'graded_at' => $isGraded ? $submittedAt->copy()->addDays(rand(1, 5)) : null,
                    'graded_by' => $isGraded ? \App\Models\User::whereHas('roles', function($q) {
                        $q->where('name', 'teacher');
                    })->inRandomOrder()->first()?->id : null,
                    'created_at' => $submittedAt,
                    'updated_at' => $isGraded ? $submittedAt->copy()->addDays(rand(1, 5)) : $submittedAt,
                ]);

                $submissionCount++;
            }
        }

        $this->command->info("Created {$submissionCount} assignment submissions.");
    }

    private function generateSubmissionContent(): string
    {
        $templates = [
            "I have completed the assignment according to the requirements. Please find my submission attached.",
            "This assignment helped me understand the key concepts better. I've included detailed explanations in my submission.",
            "I've followed the guidelines provided and completed all the required tasks. Looking forward to your feedback.",
        ];

        return $templates[array_rand($templates)];
    }

    private function generateFeedback(?int $pointsEarned): string
    {
        if ($pointsEarned === null) {
            return "Your submission needs improvement. Please review the lesson materials and resubmit if possible.";
        }
        
        if ($pointsEarned >= 90) {
            return "Excellent work! Your submission demonstrates a strong understanding of the concepts. Keep it up!";
        } elseif ($pointsEarned >= 80) {
            return "Good job! Your submission is well-structured and addresses the requirements effectively.";
        } elseif ($pointsEarned >= 70) {
            return "Your submission meets the requirements. Consider reviewing the feedback and improving for next time.";
        } else {
            return "Your submission needs improvement. Please review the lesson materials and resubmit if possible.";
        }
    }
}

