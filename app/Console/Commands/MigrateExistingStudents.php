<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Console\Command;

class MigrateExistingStudents extends Command
{
    protected $signature = 'students:migrate-existing';
    protected $description = 'Create student profiles for existing student users';

    public function handle()
    {
        $this->info('Migrating existing students...');

        // Get all users with student role who don't have a profile
        $students = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->whereDoesntHave('studentProfile')->get();

        if ($students->isEmpty()) {
            $this->info('No students to migrate. All students already have profiles!');
            return 0;
        }

        $this->info("Found {$students->count()} students without profiles.");
        
        $bar = $this->output->createProgressBar($students->count());
        $bar->start();

        foreach ($students as $student) {
            StudentProfile::create([
                'user_id' => $student->id,
                'student_id' => StudentProfile::generateStudentId(),
                'full_name' => $student->name,
                'parent_guardian_name' => 'To Be Updated',
                'parent_guardian_contact' => 'To Be Updated',
                // Other fields will be null/default until teacher updates them
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ Successfully migrated {$students->count()} students!");
        $this->warn('Note: Teachers should update parent contact info and other details.');

        return 0;
    }
}
