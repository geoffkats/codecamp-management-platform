<?php

namespace App\Http\Controllers;

use App\Mail\MembershipApplicationReceived;
use App\Mail\RegistrationRequestSubmitted;
use App\Models\RegistrationPayment;
use App\Models\RegistrationRequest;
use App\Models\StudentUniform;
use App\Services\Payments\MembershipPaymentService;
use App\Services\Payments\PesapalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public const COURSES = [
        'Scratch & Game Design',
        'Web Development',
        'Python Programming',
        'Mobile App Development',
        'Robotics & Electronics',
        'Not sure yet',
    ];

    public const RELATIONSHIPS = ['Mother', 'Father', 'Guardian'];

    public const LAPTOPS = [
        'Has their own laptop',
        'Will need a camp laptop',
        'Not sure yet',
    ];

    public const EXPERIENCE = [
        'New to coding',
        'Some Scratch or block coding',
        'Already writes some code',
    ];

    public const HOW_HEARD = [
        'School',
        'Friend or family',
        'Social media',
        'Website',
        'Event or exhibition',
        'Other',
    ];

    public function create(): View
    {
        return view('registrations.membership', [
            'courses' => self::COURSES,
            'sizes' => StudentUniform::SIZES,
            'relationships' => self::RELATIONSHIPS,
            'laptops' => self::LAPTOPS,
            'experienceLevels' => self::EXPERIENCE,
            'sources' => self::HOW_HEARD,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:'.now()->subYears(25)->toDateString()],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'nationality' => ['required', 'string', 'max:100'],
            'school' => ['required', 'string', 'max:255'],
            'class_grade' => ['required', 'string', 'max:100'],
            'course_interest' => ['required', Rule::in(self::COURSES)],
            'tshirt_size' => ['required', Rule::in(StudentUniform::SIZES)],
            'laptop' => ['required', Rule::in(self::LAPTOPS)],
            'experience' => ['required', Rule::in(self::EXPERIENCE)],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_relationship' => ['required', Rule::in(self::RELATIONSHIPS)],
            'parent_occupation' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'parent2_name' => ['nullable', 'string', 'max:255'],
            'parent2_relationship' => ['nullable', Rule::in(self::RELATIONSHIPS)],
            'parent2_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'area' => ['required', 'string', 'max:255'],
            'emergency_name' => ['required', 'string', 'max:255'],
            'emergency_phone' => ['required', 'string', 'max:50'],
            'pickup_person' => ['required', 'string', 'max:255'],
            'how_heard' => ['nullable', Rule::in(self::HOW_HEARD)],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Please confirm the details are correct and you agree to the camp terms.',
        ]);

        $fullName = trim(implode(' ', array_filter([
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['last_name'],
        ])));

        $application = RegistrationRequest::create([
            'public_token' => Str::random(48),
            'type' => 'membership',
            'full_name' => $fullName,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'organization_name' => $data['school'],
            'school_level' => $data['class_grade'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'program_interest' => 'Code Camp membership',
            'course_interest' => $data['course_interest'],
            'message' => $data['medical_notes'] ?? null,
            'status' => 'new',
            'payment_status' => 'unpaid',
            'meta' => array_filter([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'nationality' => $data['nationality'],
                'parent_name' => $data['parent_name'],
                'parent_relationship' => $data['parent_relationship'],
                'parent_occupation' => $data['parent_occupation'] ?? null,
                'parent2_name' => $data['parent2_name'] ?? null,
                'parent2_relationship' => $data['parent2_relationship'] ?? null,
                'parent2_phone' => $data['parent2_phone'] ?? null,
                'alt_phone' => $data['parent2_phone'] ?? null,
                'address' => $data['address'],
                'area' => $data['area'],
                'tshirt_size' => $data['tshirt_size'],
                'laptop' => $data['laptop'],
                'experience' => $data['experience'],
                'medical_notes' => $data['medical_notes'] ?? null,
                'emergency_name' => $data['emergency_name'],
                'emergency_phone' => $data['emergency_phone'],
                'pickup_person' => $data['pickup_person'],
                'how_heard' => $data['how_heard'] ?? null,
                'photo_consent' => $request->boolean('photo_consent') ? 'Yes' : 'No',
            ], fn ($value) => filled($value)),
        ]);

        $this->sendEmails($application);

        return redirect()->route('registration.thank-you')
            ->with('message', 'Application received for '.$application->full_name.'. We have emailed '.$application->email.' and our team will be in touch.')
            ->with('registration_conversion', ['type' => 'membership', 'id' => $application->id]);
    }

    public function pay(string $token, PesapalClient $pesapal, MembershipPaymentService $payments): View
    {
        $application = $this->findApplication($token);
        $latest = $application->latestPayment;

        if ($latest && $latest->status === 'pending' && $latest->order_tracking_id && $pesapal->isConfigured()) {
            try {
                $payments->refresh($latest);
                $application->refresh()->load('latestPayment');
            } catch (\Throwable $e) {
                Log::warning('Pesapal status check failed on pay page', ['payment_id' => $latest->id, 'error' => $e->getMessage()]);
            }
        }

        return view('registrations.membership-pay', [
            'application' => $application,
            'payment' => $application->latestPayment,
            'fee' => RegistrationRequest::membershipFee(),
            'canPayOnline' => $pesapal->isConfigured(),
        ]);
    }

    public function checkout(string $token, MembershipPaymentService $payments): RedirectResponse
    {
        $application = $this->findApplication($token);

        if ($application->isPaid()) {
            return redirect()->route('registration.membership.pay', $token);
        }

        try {
            return redirect()->away($payments->start($application));
        } catch (\Throwable $e) {
            Log::error('Could not start Pesapal payment', ['registration_id' => $application->id, 'error' => $e->getMessage()]);

            return redirect()->route('registration.membership.pay', $token)
                ->with('error', 'We could not open the payment page right now. Please try again in a few minutes.');
        }
    }

    public function callback(Request $request, MembershipPaymentService $payments): RedirectResponse
    {
        $payment = $this->findPayment($request);
        abort_unless($payment, 404);

        try {
            $payments->refresh($payment);
        } catch (\Throwable $e) {
            Log::warning('Pesapal callback status check failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('registration.membership.pay', $payment->registration->public_token);
    }

    public function ipn(Request $request, MembershipPaymentService $payments): JsonResponse
    {
        $payment = $this->findPayment($request);
        $status = 500;

        if ($payment) {
            try {
                $payments->refresh($payment);
                $status = 200;
            } catch (\Throwable $e) {
                Log::warning('Pesapal IPN status check failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'orderNotificationType' => $request->input('OrderNotificationType', 'IPNCHANGE'),
            'orderTrackingId' => $request->input('OrderTrackingId'),
            'orderMerchantReference' => $request->input('OrderMerchantReference'),
            'status' => $status,
        ]);
    }

    private function findApplication(string $token): RegistrationRequest
    {
        return RegistrationRequest::where('type', 'membership')->where('public_token', $token)->firstOrFail();
    }

    private function findPayment(Request $request): ?RegistrationPayment
    {
        $reference = (string) $request->input('OrderMerchantReference', '');
        $tracking = (string) $request->input('OrderTrackingId', '');

        if ($reference === '' || $tracking === '') {
            return null;
        }

        return RegistrationPayment::where('merchant_reference', $reference)
            ->where('order_tracking_id', $tracking)
            ->first();
    }

    private function sendEmails(RegistrationRequest $application): void
    {
        try {
            Mail::to($application->email)->send(new MembershipApplicationReceived($application));

            if ($office = config('mail.from.address')) {
                Mail::to($office)->send(new RegistrationRequestSubmitted($application));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send membership application emails', ['registration_id' => $application->id, 'error' => $e->getMessage()]);
        }
    }
}
