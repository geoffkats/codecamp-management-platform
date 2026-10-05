<?php

use App\Livewire\Certificates\Generate;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\CertificateDataService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

// student_profiles is MyISAM, so its rows survive the test transaction; keep student IDs unique.
function certStudent(): User
{
    $studentId = 'CAU-'.strtoupper(Str::random(8));
    $user = userWithRole('student');
    StudentProfile::forceCreate([
        'user_id' => $user->id,
        'student_id' => $studentId,
        'full_name' => 'Amina '.$studentId,
        'parent_guardian_name' => 'Parent',
        'parent_guardian_contact' => '0700000000',
    ]);

    return $user->fresh();
}

function completeCourse(User $user, Course $course): void
{
    CourseEnrollment::forceCreate([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'progress_percentage' => 100,
        'completed_at' => now(),
        'enrolled_at' => now()->subMonth(),
    ]);
}

function rows(string ...$names): array
{
    return array_map(fn ($n) => ['name' => $n, 'version' => '1.0', 'date' => '2026-10-01'], $names);
}

it('adds a second course to the same certificate instead of creating another', function () {
    $student = certStudent();
    [$scratch, $python] = Course::factory()->count(2)->create();
    $service = app(CertificateDataService::class);

    $first = $service->createOrUpdateCertificate($student, $scratch, rows('Scratch Basics', 'Scratch Games'));
    $second = $service->createOrUpdateCertificate($student, $python, rows('Python Intro'));

    expect(Certificate::where('user_id', $student->id)->count())->toBe(1)
        ->and($second->id)->toBe($first->id)
        ->and($second->certificate_number)->toBe($student->studentProfile->student_id)
        ->and($service->coursesOn($second))->toBe([$scratch->id, $python->id])
        ->and(collect($second->completion_data['modules'])->pluck('name')->all())
        ->toBe(['Scratch Basics', 'Scratch Games', 'Python Intro']);
});

it('issues a separate certificate with its own number when asked', function () {
    $student = certStudent();
    [$scratch, $python] = Course::factory()->count(2)->create();
    $service = app(CertificateDataService::class);

    $main = $service->createOrUpdateCertificate($student, $scratch, rows('Scratch Basics'));
    $separate = $service->createOrUpdateCertificate($student, $python, rows('Python Intro'), meta: ['mode' => 'separate']);

    expect($separate->id)->not->toBe($main->id)
        ->and($separate->certificate_number)->toBe("{$student->studentProfile->student_id}-C{$python->id}")
        ->and($service->isSeparate($separate))->toBeTrue()
        ->and($service->coursesOn($main->fresh()))->toBe([$scratch->id]);
});

it('replaces only the re-issued course rows', function () {
    $student = certStudent();
    [$scratch, $python] = Course::factory()->count(2)->create();
    $service = app(CertificateDataService::class);

    $service->createOrUpdateCertificate($student, $scratch, rows('Scratch Basics'));
    $service->createOrUpdateCertificate($student, $python, rows('Python Intro'));
    $certificate = $service->createOrUpdateCertificate($student, $scratch, rows('Scratch Basics', 'Scratch Animation'));

    expect(collect($certificate->completion_data['modules'])->pluck('name')->sort()->values()->all())
        ->toBe(['Python Intro', 'Scratch Animation', 'Scratch Basics']);
});

it('reports new, update and issued candidates', function () {
    $admin = userWithRole('admin');
    [$scratch, $python] = Course::factory()->count(2)->create();
    $service = app(CertificateDataService::class);

    $fresh = certStudent();
    completeCourse($fresh, $scratch);

    $needsUpdate = certStudent();
    completeCourse($needsUpdate, $scratch);
    completeCourse($needsUpdate, $python);
    $service->createOrUpdateCertificate($needsUpdate, $scratch, rows('Scratch Basics'));

    $done = certStudent();
    completeCourse($done, $scratch);
    $service->createOrUpdateCertificate($done, $scratch, rows('Scratch Basics'));

    $statuses = $service->certificateCandidates($admin, status: 'all')->pluck('status', 'user_id');

    expect($statuses[$fresh->id])->toBe('new')
        ->and($statuses[$needsUpdate->id])->toBe('update')
        ->and($statuses[$done->id])->toBe('issued')
        ->and($service->certificateCandidates($admin)->pluck('user_id')->sort()->values()->all())
        ->toBe(collect([$fresh->id, $needsUpdate->id])->sort()->values()->all());
});

it('issues certificates for every ready student at once', function () {
    $admin = userWithRole('admin');
    [$scratch, $python] = Course::factory()->count(2)->create();

    $students = collect(range(1, 3))->map(function ($i) use ($scratch, $python) {
        $student = certStudent();
        completeCourse($student, $scratch);
        completeCourse($student, $python);

        return $student;
    });

    Livewire::actingAs($admin)
        ->test(Generate::class)
        ->call('selectAllMatching')
        ->set('downloadZip', false)
        ->call('issueSelected')
        ->assertHasNoErrors();

    $service = app(CertificateDataService::class);
    foreach ($students as $student) {
        $certificates = Certificate::where('user_id', $student->id)->get();
        expect($certificates)->toHaveCount(1)
            ->and($service->coursesOn($certificates->first()))->toEqualCanonicalizing([$scratch->id, $python->id]);
    }
});
