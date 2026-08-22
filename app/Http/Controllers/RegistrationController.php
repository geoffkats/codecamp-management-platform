<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationRequestSubmitted;
use App\Models\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function showCodecamp(): View
    {
        return view('registrations.codecamp');
    }

    public function showSchool(): View
    {
        return view('registrations.school');
    }

    public function showIcdl(): View
    {
        return view('registrations.icdl');
    }

    public function showCodeclub(): View
    {
        return view('registrations.codeclub');
    }

    public function thankYou(): View
    {
        return view('registrations.thank-you');
    }

    public function storeCodecamp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'course_interest' => ['nullable', 'string', 'max:255'],
            'preferred_schedule' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration = RegistrationRequest::create([
            'type' => 'codecamp',
            ...$data,
        ]);

        $this->notify($registration);

        return $this->redirectAfterRegistration(
            $registration,
            'Thanks for your interest! We will contact you with next steps.'
        );
    }

    public function storeSchool(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'school_level' => ['required', 'string', 'max:255'],
            'students_count' => ['nullable', 'integer', 'min:1'],
            'program_interest' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration = RegistrationRequest::create([
            'type' => 'school',
            ...$data,
        ]);

        $this->notify($registration);

        return $this->redirectAfterRegistration(
            $registration,
            'Thank you! Our partnerships team will reach out shortly.'
        );
    }

    public function storeIcdl(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'national_id' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Male,Female'],
            'preferred_exam_date' => ['nullable', 'date'],
            'icdl_modules' => ['required', 'array', 'min:1'],
            'icdl_modules.*' => ['string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration = RegistrationRequest::create([
            'type' => 'icdl',
            ...$data,
            'icdl_modules' => $data['icdl_modules'] ?? [],
        ]);

        $this->notify($registration);

        return $this->redirectAfterRegistration(
            $registration,
            'Your ICDL exam request has been received. We will confirm your booking.'
        );
    }

    public function storeCodeclub(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'school_level' => ['required', 'string', 'max:255'],
            'students_count' => ['nullable', 'integer', 'min:1'],
            'age_group' => ['required', 'string', 'max:50'],
            'preferred_schedule' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $registration = RegistrationRequest::create([
            'type' => 'codeclub',
            'organization_name' => $data['organization_name'],
            'full_name' => $data['full_name'],
            'role_title' => $data['role_title'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'school_level' => $data['school_level'],
            'students_count' => $data['students_count'] ?? null,
            'preferred_schedule' => $data['preferred_schedule'] ?? null,
            'message' => $data['message'] ?? null,
            'program_interest' => 'CodeClub',
            'meta' => [
                'age_group' => $data['age_group'],
                'location' => $data['location'] ?? null,
            ],
        ]);

        $this->notify($registration);

        return $this->redirectAfterRegistration(
            $registration,
            'Thanks for registering your school! We will share CodeClub schedules and next steps soon.'
        );
    }

    /**
     * Redirect to the thank-you page and flash a one-time conversion payload.
     * The analytics conversion partial reads this flash and fires only once
     * (Laravel flash data is consumed after the thank-you response).
     */
    private function redirectAfterRegistration(RegistrationRequest $registration, string $message): RedirectResponse
    {
        return redirect()
            ->route('registration.thank-you')
            ->with('message', $message)
            ->with('registration_conversion', [
                'type' => $registration->type,
                'id' => $registration->id,
            ]);
    }

    private function notify(RegistrationRequest $registration): void
    {
        $toAddress = config('mail.from.address');

        if (!$toAddress) {
            return;
        }

        Mail::to($toAddress)->send(new RegistrationRequestSubmitted($registration));
    }
}
