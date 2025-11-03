<?php

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can access assessment take page when enrolled', function () {
    // Create users
    $student = User::factory()->create();
    $teacher = User::factory()->create();
    
    // Create course
    $course = Course::create([
        'title' => 'Test Course',
        'description' => 'Test Description',
        'instructor_id' => $teacher->id,
        'approval_status' => 'approved',
        'is_published' => true,
    ]);
    
    // Create lesson
    $lesson = Lesson::create([
        'course_id' => $course->id,
        'module_id' => null,
        'title' => 'Test Lesson',
        'slug' => 'test-lesson',
        'content' => 'Test content',
        'order' => 1,
    ]);
    
    // Enroll student
    CourseEnrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);
    
    // Create assessment
    $assessment = Assessment::create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'title' => 'Test Assessment',
        'assessment_type' => 'quiz',
        'approval_status' => 'approved',
        'is_locked' => false,
        'max_attempts' => 1,
        'passing_score' => 70,
        'xp_reward' => 50,
    ]);
    
    // Create a question
    $question = Question::create([
        'assessment_id' => $assessment->id,
        'question_text' => 'What is 2+2?',
        'question_type' => 'multiple_choice',
        'points' => 10,
        'order' => 1,
    ]);
    
    // Create option
    \App\Models\QuestionOption::create([
        'question_id' => $question->id,
        'option_text' => '4',
        'is_correct' => true,
        'order' => 1,
    ]);
    
    // Act as student
    $this->actingAs($student);
    
    // Try to access take page
    $response = $this->get(route('assessments.take', $assessment));
    
    // Should succeed - the assessment has questions, user is enrolled, assessment is approved
    $response->assertStatus(200);
});

test('user cannot access assessment take page when not enrolled', function () {
    // Create users
    $student = User::factory()->create();
    $teacher = User::factory()->create();
    
    // Create course
    $course = Course::create([
        'title' => 'Test Course',
        'description' => 'Test Description',
        'instructor_id' => $teacher->id,
        'approval_status' => 'approved',
        'is_published' => true,
    ]);
    
    // Do NOT enroll student
    
    // Create lesson
    $lesson = Lesson::create([
        'course_id' => $course->id,
        'module_id' => null,
        'title' => 'Test Lesson',
        'slug' => 'test-lesson-2',
        'content' => 'Test content',
        'order' => 1,
    ]);
    
    // Create assessment
    $assessment = Assessment::create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'title' => 'Test Assessment',
        'assessment_type' => 'quiz',
        'approval_status' => 'approved',
        'is_locked' => false,
        'max_attempts' => 1,
        'passing_score' => 70,
        'xp_reward' => 50,
    ]);
    
    // Act as student
    $this->actingAs($student);
    
    // Try to access take page
    $response = $this->get(route('assessments.take', $assessment));
    
    // Should redirect
    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('assignment type assessment does not require questions', function () {
    // Create users
    $student = User::factory()->create();
    $teacher = User::factory()->create();
    
    // Create course
    $course = Course::create([
        'title' => 'Test Course',
        'description' => 'Test Description',
        'instructor_id' => $teacher->id,
        'approval_status' => 'approved',
        'is_published' => true,
    ]);
    
    // Create lesson
    $lesson = Lesson::create([
        'course_id' => $course->id,
        'module_id' => null,
        'title' => 'Test Lesson',
        'slug' => 'test-lesson-3',
        'content' => 'Test content',
        'order' => 1,
    ]);
    
    // Enroll student
    CourseEnrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);
    
    // Create assignment-type assessment WITHOUT questions
    $assessment = Assessment::create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'title' => 'Test Assignment',
        'assessment_type' => 'assignment',
        'approval_status' => 'approved',
        'is_locked' => false,
        'max_attempts' => 1,
        'passing_score' => 70,
        'xp_reward' => 50,
    ]);
    
    // Act as student
    $this->actingAs($student);
    
    // Try to access take page
    $response = $this->get(route('assessments.take', $assessment));
    
    // Should succeed (assignment types don't need questions)
    $response->assertStatus(200);
});

test('non-assignment assessment requires questions', function () {
    // Create users
    $student = User::factory()->create();
    $teacher = User::factory()->create();
    
    // Create course
    $course = Course::create([
        'title' => 'Test Course',
        'description' => 'Test Description',
        'instructor_id' => $teacher->id,
        'approval_status' => 'approved',
        'is_published' => true,
    ]);
    
    // Create lesson
    $lesson = Lesson::create([
        'course_id' => $course->id,
        'module_id' => null,
        'title' => 'Test Lesson',
        'slug' => 'test-lesson-4',
        'content' => 'Test content',
        'order' => 1,
    ]);
    
    // Enroll student
    CourseEnrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);
    
    // Create quiz-type assessment WITHOUT questions
    $assessment = Assessment::create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'title' => 'Test Quiz',
        'assessment_type' => 'quiz',
        'approval_status' => 'approved',
        'is_locked' => false,
        'max_attempts' => 1,
        'passing_score' => 70,
        'xp_reward' => 50,
    ]);
    
    // Act as student
    $this->actingAs($student);
    
    // Try to access take page
    $response = $this->get(route('assessments.take', $assessment));
    
    // Should redirect with error
    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertStringContainsString('no questions', session('error'));
});

test('redirects use correct assessment ID', function () {
    // Create users
    $student = User::factory()->create();
    $teacher = User::factory()->create();
    
    // Create course
    $course = Course::create([
        'title' => 'Test Course',
        'description' => 'Test Description',
        'instructor_id' => $teacher->id,
        'approval_status' => 'approved',
        'is_published' => true,
    ]);
    
    // Create lesson
    $lesson = Lesson::create([
        'course_id' => $course->id,
        'module_id' => null,
        'title' => 'Test Lesson',
        'slug' => 'test-lesson-5',
        'content' => 'Test content',
        'order' => 1,
    ]);
    
    // Enroll student
    CourseEnrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
    ]);
    
    // Create locked assessment
    $assessment = Assessment::create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'title' => 'Locked Assessment',
        'assessment_type' => 'quiz',
        'approval_status' => 'approved',
        'is_locked' => true, // Locked
        'max_attempts' => 1,
        'passing_score' => 70,
        'xp_reward' => 50,
    ]);
    
    // Act as student
    $this->actingAs($student);
    
    // Try to access take page
    $response = $this->get(route('assessments.take', $assessment));
    
    // Should redirect to the SAME assessment's show page (not a different one)
    $response->assertRedirect(route('assessments.show', $assessment));
    $response->assertSessionHas('error');
});

