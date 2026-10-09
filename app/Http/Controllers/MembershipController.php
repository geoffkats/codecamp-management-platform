<?php

namespace App\Http\Controllers;

use App\Mail\MembershipApplicationReceived;
use App\Mail\RegistrationRequestSubmitted;
use App\Models\CodeCamp;
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

    public function create(): View
    {
        return view('registrations.membership', [
            'camps' => $this->openCamps(),
            'courses' => self::COURSES,
            'sizes' => StudentUniform::SIZES,
            'fee' => RegistrationRequest::membershipFee(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $camps = $this->openCamps();

        $data = $request->validate([
            'child_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:'.now()->subYears(25)->toDateString()],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'school' => ['required', 'string', 'max:255'],
            'class_grade' => ['required', 'string', 'max:100'],
            'camp_id' => ['nullable', Rule::in($camps->pluck('id')->all())],
            'course_interest' => ['nullable', Rule::in(self::COURSES)],
            'tshirt_size' => ['nullable', Rule::in(StudentUniform::SIZES)],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_relationship' => ['required', Rule::in(['Mother', 'Father', 'Guardian'])],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'alt_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'how_heard' => ['nullable', 'string', 'max:255'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Please confirm the details are correct and you agree to the camp terms.',
        ]);

        $camp = filled($data['camp_id'] ?? null) ? $camps->firstWhere('id', (int) $data['camp_id']) : null;

        $application = RegistrationRequest::create([
            'public_token' => Str::random(48),
            'type' => 'membership',
            'full_name' => $data['child_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'organization_name' => $data['school'],
            'school_level' => $data['class_grade'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'program_interest' => 'Code Camp membership',
            'course_interest' => $data['course_interest'] ?? null,
            'preferred_schedule' => $camp?->name,
            'message' => $data['medical_notes'] ?? null,
            'status' => 'new',
            'payment_status' => 'unpaid',
            'meta' => array_filter([
                'parent_name' => $data['parent_name'],
                'parent_relationship' => $data['parent_relationship'],
                'alt_phone' => $data['alt_phone'] ?? null,
                'address' => $data['address'] ?? null,
                'camp_id' => $camp?->id,
                'tshirt_size' => $data['tshirt_size'] ?? null,
                'medical_notes' => $data['medical_notes'] ?? null,
                'how_heard' => $data['how_heard'] ?? null,
            ], fn ($value) => filled($value)),
        ]);

        $this->sendEmails($application);

        return redirect()->route('registration.membership.pay', $application->public_token)
            ->with('message', 'Application received. We have emailed the details to '.$application->email.'.')
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

    private function openCamps()
    {
        return CodeCamp::whereIn('status', ['upcoming', 'active'])->orderBy('start_date')->get(['id', 'name', 'start_date', 'end_date', 'status']);
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
