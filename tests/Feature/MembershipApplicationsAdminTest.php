<?php

use App\Livewire\Admin\MembershipApplications\Show;
use App\Models\RegistrationPayment;
use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function membershipApplication(array $overrides = []): RegistrationRequest
{
    return RegistrationRequest::create(array_merge([
        'public_token' => 'tok_'.uniqid(),
        'type' => 'membership',
        'full_name' => 'Amina Okello',
        'email' => 'parent@example.com',
        'phone' => '0700111222',
        'organization_name' => 'Kampala Junior',
        'school_level' => 'P6',
        'date_of_birth' => '2014-05-12',
        'gender' => 'Female',
        'program_interest' => 'Code Camp membership',
        'status' => 'new',
        'payment_status' => 'unpaid',
        'meta' => [
            'first_name' => 'Amina',
            'last_name' => 'Okello',
            'nationality' => 'Ugandan',
            'parent_name' => 'Sarah Okello',
            'parent_relationship' => 'Mother',
            'laptop' => 'Yes',
            'experience' => 'Beginner',
            'teacher1_name' => 'Mr Kato',
            'teacher1_school' => 'Kampala Junior',
            'teacher1_subject' => 'Math',
            'teacher1_phone' => '0700333444',
            'teacher2_name' => 'Ms Nalwoga',
            'teacher2_school' => 'Kampala Junior',
            'teacher2_subject' => 'Science',
            'teacher2_phone' => '0700555666',
            'emergency_name' => 'Uncle Joe',
            'emergency_phone' => '0700777888',
            'pickup_person' => 'Mother',
            'photo_consent' => 'Yes',
            'mental_illness' => 'No',
            'term_report_1' => 'membership-reports/1/term1.jpg',
        ],
    ], $overrides));
}

it('lets admins open the membership applications list and detail page', function () {
    $admin = userWithRole('admin');
    $application = membershipApplication();

    $this->actingAs($admin)
        ->get(route('admin.membership-applications.index'))
        ->assertOk()
        ->assertSee('Membership Applications')
        ->assertSee('Amina Okello');

    $this->actingAs($admin)
        ->get(route('admin.membership-applications.show', $application))
        ->assertOk()
        ->assertSee('Amina Okello')
        ->assertSee('Sarah Okello')
        ->assertSee('Mr Kato')
        ->assertSee('Delete application');
});

it('blocks students from membership applications', function () {
    $application = membershipApplication();

    $this->actingAs(userWithRole('student'))
        ->get(route('admin.membership-applications.index'))
        ->assertForbidden();

    $this->actingAs(userWithRole('student'))
        ->get(route('admin.membership-applications.show', $application))
        ->assertForbidden();
});

it('returns 404 when opening a non-membership registration on the membership show page', function () {
    $request = RegistrationRequest::create([
        'type' => 'school',
        'full_name' => 'ICT School Lead',
        'email' => 'school@example.com',
        'phone' => '0700999000',
        'status' => 'new',
    ]);

    $this->actingAs(userWithRole('admin'))
        ->get(route('admin.membership-applications.show', $request))
        ->assertNotFound();
});

it('deletes a membership application with report files and payment rows', function () {
    Storage::fake('public');

    $application = membershipApplication([
        'meta' => [
            'first_name' => 'Amina',
            'last_name' => 'Okello',
            'term_report_1' => 'membership-reports/tmp/term1.jpg',
            'term_report_2' => 'membership-reports/tmp/term2.jpg',
            'term_report_3' => 'membership-reports/tmp/term3.jpg',
        ],
    ]);

    Storage::disk('public')->put('membership-reports/tmp/term1.jpg', 'one');
    Storage::disk('public')->put('membership-reports/tmp/term2.jpg', 'two');
    Storage::disk('public')->put('membership-reports/'.$application->id.'/extra.jpg', 'extra');

    RegistrationPayment::create([
        'registration_request_id' => $application->id,
        'gateway' => 'pesapal',
        'merchant_reference' => 'ref-'.$application->id,
        'amount' => 50000,
        'currency' => 'UGX',
        'status' => 'pending',
    ]);

    Livewire::actingAs(userWithRole('admin'))
        ->test(Show::class, ['application' => $application])
        ->call('deleteApplication')
        ->assertRedirect(route('admin.membership-applications.index'));

    expect(RegistrationRequest::find($application->id))->toBeNull()
        ->and(RegistrationPayment::where('registration_request_id', $application->id)->exists())->toBeFalse()
        ->and(Storage::disk('public')->exists('membership-reports/tmp/term1.jpg'))->toBeFalse()
        ->and(Storage::disk('public')->exists('membership-reports/'.$application->id.'/extra.jpg'))->toBeFalse();
});
