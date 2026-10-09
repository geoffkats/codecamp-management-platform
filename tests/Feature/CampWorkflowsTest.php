<?php

use App\Livewire\CampRevisions\Create as CreateRevision;
use App\Livewire\CampRevisions\Show as ShowRevision;
use App\Livewire\Comments\Thread;
use App\Mail\MembershipApplicationReceived;
use App\Mail\MembershipPaymentReceived;
use App\Models\CampContentRevision;
use App\Models\CodeCamp;
use App\Models\Course;
use App\Models\DailyReport;
use App\Models\Notification;
use App\Models\RegistrationRequest;
use App\Models\StudentProfile;
use App\Models\SubmissionComment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function campFor(User $creator, string $status = 'active'): CodeCamp
{
    return CodeCamp::create([
        'name' => 'December Camp',
        'start_date' => now()->subWeek()->toDateString(),
        'end_date' => now()->addWeek()->toDateString(),
        'status' => $status,
        'created_by' => $creator->id,
    ]);
}

function dailyReportBy(User $instructor): DailyReport
{
    return DailyReport::create([
        'report_date' => now()->toDateString(),
        'course_id' => Course::factory()->create()->id,
        'instructor_id' => $instructor->id,
        'status' => 'submitted',
        'summary' => 'Covered loops.',
        'submitted_at' => now(),
    ]);
}

it('notifies the trainer when a supervisor comments on their daily report, but not the author', function () {
    $trainer = userWithRole('teacher');
    $supervisor = userWithRole('supervisor');
    $report = dailyReportBy($trainer);

    Livewire::actingAs($supervisor)
        ->test(Thread::class, ['commentable' => $report])
        ->set('body', 'Great session, try pair programming tomorrow.')
        ->call('post')
        ->assertHasNoErrors();

    expect(SubmissionComment::count())->toBe(1)
        ->and(Notification::where('user_id', $trainer->id)->where('type', 'comment')->count())->toBe(1)
        ->and(Notification::where('user_id', $supervisor->id)->count())->toBe(0);

    Livewire::actingAs($trainer)
        ->test(Thread::class, ['commentable' => $report])
        ->set('body', 'Thanks, will do.')
        ->call('post');

    expect(Notification::where('user_id', $supervisor->id)->where('type', 'comment')->count())->toBe(1);
});

it('lets a trainer open their own daily report but not someone else\'s', function () {
    $trainer = userWithRole('teacher');
    $report = dailyReportBy($trainer);

    $this->actingAs($trainer)->get(route('daily-reports.show', $report))->assertOk();
    $this->actingAs(userWithRole('teacher'))->get(route('daily-reports.show', $report))->assertForbidden();
});

it('keeps several uniforms per student and mirrors them onto the legacy columns', function () {
    $profile = StudentProfile::create([
        'user_id' => User::factory()->create()->id,
        'student_id' => 'STU-TEST-0001',
        'full_name' => 'Amina K',
        'parent_guardian_name' => 'Parent',
        'parent_guardian_contact' => '0700000000',
    ]);

    $profile->syncUniforms([
        ['size' => 'M', 'paid' => true],
        ['size' => 'L', 'paid' => false],
        ['size' => '', 'paid' => false],
    ]);

    $profile->refresh();
    expect($profile->uniforms)->toHaveCount(2)
        ->and($profile->uniform_size)->toBe('M')
        ->and((bool) $profile->uniform_paid)->toBeFalse();

    $rows = $profile->uniforms->map(fn ($u) => ['id' => $u->id, 'size' => $u->size, 'paid' => true])->all();
    $profile->syncUniforms([$rows[0]]);

    $profile->refresh();
    expect($profile->uniforms)->toHaveCount(1)
        ->and((bool) $profile->uniform_paid)->toBeTrue();
});

it('lets a trainer send revised content and a supervisor approve it', function () {
    Storage::fake('local');
    $trainer = userWithRole('teacher');
    $supervisor = userWithRole('supervisor');
    $camp = campFor($supervisor);

    Livewire::actingAs($trainer)
        ->test(CreateRevision::class)
        ->set('campId', $camp->id)
        ->set('title', 'Scratch week 1, reworked')
        ->set('notes', 'Shortened the intro and added a maze game the kids loved.')
        ->set('picked', [UploadedFile::fake()->create('week1.pdf', 200, 'application/pdf')])
        ->call('save')
        ->assertHasNoErrors();

    $revision = CampContentRevision::with('files')->firstOrFail();
    expect($revision->files)->toHaveCount(1)
        ->and(Notification::where('user_id', $supervisor->id)->exists())->toBeTrue();
    Storage::disk('local')->assertExists($revision->files->first()->path);

    Livewire::actingAs($supervisor)
        ->test(ShowRevision::class, ['revision' => $revision])
        ->set('reviewNote', 'Looks good, use it next camp.')
        ->call('approve');

    expect($revision->fresh()->status)->toBe('approved')
        ->and(Notification::where('user_id', $trainer->id)->exists())->toBeTrue();

    $file = $revision->files->first();
    $this->actingAs($trainer)->get(route('camp-revisions.files.download', $file))->assertOk();
    $this->actingAs(userWithRole('teacher'))->get(route('camp-revisions.files.download', $file))->assertForbidden();
});

function membershipPayload(array $overrides = []): array
{
    return array_merge([
        'child_name' => 'Brian Okello Mukasa',
        'date_of_birth' => now()->subYears(10)->toDateString(),
        'gender' => 'Male',
        'school' => 'Kampala Parents School',
        'class_grade' => 'P.5',
        'tshirt_size' => 'M',
        'parent_name' => 'Grace Okello',
        'parent_relationship' => 'Mother',
        'email' => 'grace@example.com',
        'phone' => '0772000000',
        'consent' => '1',
    ], $overrides);
}

it('takes a membership application, emails the parent and opens the payment screen', function () {
    Mail::fake();

    $response = $this->post(route('registration.membership.store'), membershipPayload());

    $application = RegistrationRequest::where('type', 'membership')->firstOrFail();
    $response->assertRedirect(route('registration.membership.pay', $application->public_token));

    expect($application->payment_status)->toBe('unpaid')
        ->and($application->meta['parent_name'])->toBe('Grace Okello');
    Mail::assertSent(MembershipApplicationReceived::class, fn ($mail) => $mail->hasTo('grace@example.com'));

    $this->get(route('registration.membership.pay', $application->public_token))
        ->assertOk()
        ->assertSee('UGX 200,000')
        ->assertSee('Brian Okello Mukasa');
});

it('sends the parent to Pesapal and marks the application paid once Pesapal confirms', function () {
    Mail::fake();
    config([
        'services.pesapal.consumer_key' => 'key',
        'services.pesapal.consumer_secret' => 'secret',
        'services.pesapal.ipn_id' => 'ipn-123',
    ]);

    $status = 'Pending';
    Http::fake([
        '*/api/Auth/RequestToken' => Http::response(['token' => 'tok', 'status' => '200']),
        '*/api/Transactions/SubmitOrderRequest' => Http::response([
            'order_tracking_id' => 'TRACK-1',
            'merchant_reference' => 'x',
            'redirect_url' => 'https://pay.example/checkout',
            'status' => '200',
        ]),
        '*/api/Transactions/GetTransactionStatus*' => function () use (&$status) {
            return Http::response([
                'payment_status_description' => $status,
                'payment_method' => 'MTN Mobile Money',
                'confirmation_code' => 'CONF1',
                'status' => '200',
            ]);
        },
    ]);

    $this->post(route('registration.membership.store'), membershipPayload());
    $application = RegistrationRequest::where('type', 'membership')->firstOrFail();

    $this->post(route('registration.membership.checkout', $application->public_token))
        ->assertRedirect('https://pay.example/checkout');

    $payment = $application->payments()->firstOrFail();
    expect($payment->order_tracking_id)->toBe('TRACK-1')
        ->and($payment->amount)->toBe(200000);

    $status = 'Completed';
    $query = ['OrderTrackingId' => 'TRACK-1', 'OrderMerchantReference' => $payment->merchant_reference, 'OrderNotificationType' => 'IPNCHANGE'];

    $this->get(route('registration.payments.pesapal.ipn', $query))
        ->assertOk()
        ->assertJson(['orderTrackingId' => 'TRACK-1', 'status' => 200]);

    $this->get(route('registration.payments.pesapal.callback', $query))
        ->assertRedirect(route('registration.membership.pay', $application->public_token));

    expect($application->fresh()->isPaid())->toBeTrue()
        ->and($payment->fresh()->status)->toBe('completed');
    Mail::assertSent(MembershipPaymentReceived::class, 1);
});

it('rejects Pesapal notifications for unknown payments', function () {
    $this->get(route('registration.payments.pesapal.ipn', ['OrderTrackingId' => 'nope', 'OrderMerchantReference' => 'nope']))
        ->assertJson(['status' => 500]);
});
