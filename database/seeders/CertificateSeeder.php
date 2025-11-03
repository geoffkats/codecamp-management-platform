<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
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

        // Get completed enrollments
        $completedEnrollments = CourseEnrollment::whereNotNull('completed_at')
            ->where('progress_percentage', '>=', 100)
            ->get();

        if ($completedEnrollments->isEmpty()) {
            $this->command->warn('No completed enrollments found. Please run CourseEnrollmentSeeder first.');
            return;
        }

        $certificateCount = 0;

        foreach ($completedEnrollments->take(5) as $enrollment) {
            // Skip if certificate already exists
            if (Certificate::where('user_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->exists()) {
                continue;
            }

            $course = $enrollment->course;
            
            Certificate::create([
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'certificate_number' => 'CERT-' . strtoupper(uniqid()),
                'title' => 'Certificate of Completion - ' . ($course->title ?? 'Course'),
                'description' => 'This certifies that the student has successfully completed the course.',
                'issued_at' => $enrollment->completed_at ?? now(),
                'expires_at' => null, // Certificates don't expire
                'is_verified' => true,
                'completion_data' => [
                    'progress_percentage' => $enrollment->progress_percentage,
                    'completion_date' => $enrollment->completed_at?->format('Y-m-d'),
                    'lessons_completed' => $enrollment->lessons_completed ?? 0,
                ],
                'file_path' => null, // Would be generated later
            ]);

            $certificateCount++;
        }

        $this->command->info("Created {$certificateCount} certificates for completed courses.");
    }
}

