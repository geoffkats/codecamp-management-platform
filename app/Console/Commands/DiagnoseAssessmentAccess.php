<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Console\Command;

class DiagnoseAssessmentAccess extends Command
{
    protected $signature = 'assessment:diagnose {assessment_id} {user_id?}';
    
    protected $description = 'Diagnose why a user cannot access an assessment';

    public function handle()
    {
        $assessmentId = $this->argument('assessment_id');
        $userId = $this->argument('user_id');

        $this->info("Diagnosing Assessment Access");
        $this->line(str_repeat('=', 60));

        // Check assessment
        $assessment = Assessment::withTrashed()->with(['course', 'lesson'])->find($assessmentId);
        
        if (!$assessment) {
            $this->error("❌ Assessment ID {$assessmentId} not found!");
            
            $recent = Assessment::withTrashed()
                ->latest()
                ->take(5)
                ->get(['id', 'title', 'deleted_at']);
                
            $this->line("\nRecent assessments:");
            foreach ($recent as $a) {
                $deleted = $a->deleted_at ? ' (DELETED)' : '';
                $this->line("  - ID {$a->id}: {$a->title}{$deleted}");
            }
            
            return 1;
        }

        if ($assessment->deleted_at) {
            $this->warn("⚠️  Assessment is SOFT DELETED (deleted at: {$assessment->deleted_at})");
        }

        $this->info("Assessment: {$assessment->title}");
        $this->line("Type: {$assessment->assessment_type}");
        $this->line("Course: {$assessment->course->title} (ID: {$assessment->course_id})");
        $this->line("Approval Status: {$assessment->approval_status}");
        $this->line("Is Locked: " . ($assessment->is_locked ? 'Yes' : 'No'));
        
        if ($assessment->lesson) {
            $this->line("Lesson: {$assessment->lesson->title} (ID: {$assessment->lesson_id})");
        }

        // If user ID provided, check their access
        if ($userId) {
            $this->line("\n" . str_repeat('-', 60));
            
            $user = User::with('roles')->find($userId);
            
            if (!$user) {
                $this->error("❌ User ID {$userId} not found!");
                return 1;
            }

            $this->info("User: {$user->name} (ID: {$userId})");
            $this->line("Email: {$user->email}");
            $this->line("Roles: " . $user->roles->pluck('name')->join(', '));

            $this->line("\n" . str_repeat('-', 60));
            $this->info("Access Checks:");

            // Check 1: Enrollment
            $enrolled = $assessment->course->enrollments()->where('user_id', $userId)->first();
            if (!$enrolled && !$user->hasRole('admin')) {
                $this->error("  ❌ DENIED: User is not enrolled in the course");
                $this->line("     → User needs to enroll in: {$assessment->course->title}");
            } else {
                $this->info("  ✓ PASSED: User is enrolled or is admin");
                if ($enrolled) {
                    $this->line("     → Enrollment ID: {$enrolled->id}");
                }
            }

            // Check 2: Locked status
            if ($assessment->is_locked && !$user->hasAnyRole(['admin', 'teacher'])) {
                $this->error("  ❌ DENIED: Assessment is locked");
                $this->line("     → Only admins and teachers can access locked assessments");
            } else {
                $this->info("  ✓ PASSED: Assessment is unlocked or user has permission");
            }

            // Check 3: Approval status
            if ($assessment->approval_status !== 'approved' && !$user->hasAnyRole(['admin', 'teacher'])) {
                $this->error("  ❌ DENIED: Assessment is not approved (status: {$assessment->approval_status})");
                $this->line("     → Assessment needs to be approved by an instructor");
            } else {
                $this->info("  ✓ PASSED: Assessment is approved or user has permission");
            }
        }

        return 0;
    }
}
