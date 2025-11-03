<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\User;
use App\Models\Badge;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            BadgeSeeder::class,
            CourseSeeder::class,
            LessonSeeder::class,
            AssessmentSeeder::class,
            QuizSeeder::class,
            DailyChallengeSeeder::class,
            CourseEnrollmentSeeder::class,
            UserBadgeSeeder::class,
            QuizAttemptSeeder::class,
            AssignmentSubmissionSeeder::class,
            CertificateSeeder::class,
            NotificationSeeder::class,
            ContentApprovalSeeder::class,
            DiscussionSeeder::class,
            ProgressSeeder::class,
            LeaderboardSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Roles: 4');
        
        $userCount = User::count();
        $adminCount = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count();
        $teacherCount = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->count();
        $studentCount = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        $this->command->info("   - Users: {$userCount} ({$adminCount} admin, {$teacherCount} teachers, {$studentCount} students)");
        
        $this->command->info('   - Badges: ' . Badge::count());
        
        $courseCount = Course::count();
        $lessonCount = Lesson::count();
        $assessmentCount = Assessment::count();
        $questionCount = Question::count();
        
        $this->command->info("   - Courses: {$courseCount}");
        $this->command->info("   - Lessons: {$lessonCount}");
        $this->command->info("   - Assessments: {$assessmentCount}");
        $this->command->info("   - Questions: {$questionCount}");
        
        // Show course list
        $courses = Course::select('title', 'category', 'difficulty_level')->get();
        if ($courses->count() > 0) {
            $this->command->info('');
            $this->command->info('📚 Courses Created:');
            foreach ($courses as $course) {
                $this->command->info("   • {$course->title} ({$course->category} - {$course->difficulty_level})");
            }
        }
        
        $this->command->info('');
        $this->command->info('   - Quiz Attempts: Multiple attempts per student');
        $this->command->info('   - Assignment Submissions: Student submissions');
        $this->command->info('   - Certificates: For completed courses');
        $this->command->info('   - Notifications: For all users');
        $this->command->info('   - Daily Challenges: Created for next 7 days');
        $this->command->info('');
        $this->command->info('🔐 Login Credentials:');
        $this->command->info('   - Admin: admin@example.com / password');
        $this->command->info('   - Teacher: teacher@example.com / password');
        $this->command->info('   - Student: student@example.com / password');
    }
}
