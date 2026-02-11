<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'student_type' => 'codecamp',
            'password' => $input['password'],
        ]);

        // Automatically assign student role
        $studentRole = \App\Models\Role::where('name', 'student')->first();
        if ($studentRole) {
            $user->roles()->attach($studentRole->id);
        }

        // Create basic student profile
        \App\Models\StudentProfile::create([
            'user_id' => $user->id,
            'student_id' => $this->generateStudentId(),
            'full_name' => $input['name'],
            'email' => $input['email'],
            'date_of_birth' => null, // Can be filled later
            'gender' => null, // Can be filled later
            'phone_number' => null, // Can be filled later
            'address' => null, // Can be filled later
        ]);

        // Create user points record for gamification
        \App\Models\UserPoint::create([
            'user_id' => $user->id,
            'total_points' => 0,
            'level' => 1,
            'points_to_next_level' => 100,
        ]);

        return $user;
    }

    /**
     * Generate unique student ID
     */
    private function generateStudentId(): string
    {
        $year = date('Y');
        $lastStudent = \App\Models\StudentProfile::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastStudent && preg_match('/STU-' . $year . '-(\d+)/', $lastStudent->student_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return 'STU-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
