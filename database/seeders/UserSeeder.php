<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $studentRole = Role::where('name', 'student')->first();
        $teacherRole = Role::where('name', 'teacher')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $supervisorRole = Role::where('name', 'supervisor')->first();

        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->roles()->sync([$adminRole->id]);

        // Create Supervisor User
        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@example.com'],
            [
                'name' => 'Supervisor User',
                'email' => 'supervisor@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $supervisor->roles()->sync([$supervisorRole->id]);

        // Create Teacher Users
        $teacher1 = User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'John Teacher',
                'email' => 'teacher@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'bio' => 'Experienced educator with 10+ years in online teaching',
            ]
        );
        $teacher1->roles()->sync([$teacherRole->id]);

        $teacher2 = User::updateOrCreate(
            ['email' => 'teacher2@example.com'],
            [
                'name' => 'Jane Instructor',
                'email' => 'teacher2@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'bio' => 'Passionate about making learning engaging and accessible',
            ]
        );
        $teacher2->roles()->sync([$teacherRole->id]);

        // Create Student Users
        $students = [
            [
                'name' => 'Alice Student',
                'email' => 'student@example.com',
            ],
            [
                'name' => 'Bob Learner',
                'email' => 'student2@example.com',
            ],
            [
                'name' => 'Charlie Student',
                'email' => 'student3@example.com',
            ],
        ];

        foreach ($students as $studentData) {
            $student = User::updateOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'email' => $studentData['email'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $student->roles()->sync([$studentRole->id]);

            // Initialize user points
            UserPoint::updateOrCreate(
                ['user_id' => $student->id],
                [
                    'total_points' => rand(100, 1000),
                    'level' => rand(1, 10),
                    'points_to_next_level' => 100,
                ]
            );
        }

        // Initialize points for admin and teachers too
        UserPoint::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]
        );

        UserPoint::updateOrCreate(
            ['user_id' => $teacher1->id],
            [
                'total_points' => rand(500, 2000),
                'level' => rand(5, 15),
                'points_to_next_level' => 100,
            ]
        );
    }
}

