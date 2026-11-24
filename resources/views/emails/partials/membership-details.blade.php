@php
    $meta = $application->meta ?? [];
    $rows = [
        'Child' => $application->full_name,
        'Date of birth' => $application->date_of_birth?->format('j F Y'),
        'Gender' => $application->gender,
        'Nationality' => $meta['nationality'] ?? null,
        'School' => $application->organization_name,
        'Class' => $application->school_level,
        'Course interest' => $application->course_interest,
        'T-shirt size' => $meta['tshirt_size'] ?? null,
        'Laptop' => $meta['laptop'] ?? null,
        'Coding experience' => $meta['experience'] ?? null,
        'Parent / guardian' => trim(($meta['parent_name'] ?? '').(! empty($meta['parent_relationship']) ? ' ('.$meta['parent_relationship'].')' : '')),
        'Occupation' => $meta['parent_occupation'] ?? null,
        'Phone' => $application->phone,
        'Email' => $application->email,
        'Second parent' => trim(($meta['parent2_name'] ?? '').(! empty($meta['parent2_relationship']) ? ' ('.$meta['parent2_relationship'].')' : '').(! empty($meta['parent2_phone']) ? ' · '.$meta['parent2_phone'] : '')),
        'Address' => trim(($meta['address'] ?? '').(! empty($meta['area']) ? ', '.$meta['area'] : '')),
        'Emergency contact' => trim(($meta['emergency_name'] ?? '').(! empty($meta['emergency_phone']) ? ' · '.$meta['emergency_phone'] : '')),
        'Pickup' => $meta['pickup_person'] ?? null,
        'Photos' => $meta['photo_consent'] ?? null,
        'Medical conditions' => $meta['medical_notes'] ?? null,
        'History of mental illness' => $meta['mental_illness'] ?? null,
        'Special care or attention' => $meta['special_care'] ?? null,
        'Passions' => $meta['passions'] ?? null,
        'Known weaknesses' => $meta['weaknesses'] ?? null,
        'Teacher 1' => trim(implode(' · ', array_filter([
            $meta['teacher1_name'] ?? null,
            $meta['teacher1_school'] ?? null,
            $meta['teacher1_subject'] ?? null,
            $meta['teacher1_phone'] ?? null,
        ]))),
        'Teacher 2' => trim(implode(' · ', array_filter([
            $meta['teacher2_name'] ?? null,
            $meta['teacher2_school'] ?? null,
            $meta['teacher2_subject'] ?? null,
            $meta['teacher2_phone'] ?? null,
        ]))),
    ];
    $reports = [
        'Most recent term' => $meta['term_report_1'] ?? null,
        'Previous term' => $meta['term_report_2'] ?? null,
        'Term before that' => $meta['term_report_3'] ?? null,
    ];
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
    @foreach($rows as $label => $value)
        @if(filled($value))
            <tr>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#64748b;width:40%;vertical-align:top;">{{ $label }}</td>
                <td style="padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#0f172a;font-weight:600;">{{ $value }}</td>
            </tr>
        @endif
    @endforeach
</table>
@if(collect($reports)->filter()->isNotEmpty())
    <p style="margin:18px 0 8px;font-size:15px;color:#0f172a;">School reports</p>
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            @foreach($reports as $label => $path)
                @if(filled($path))
                    @php $url = \Illuminate\Support\Facades\Storage::disk('public')->url($path); @endphp
                    <td style="padding:0 8px 0 0;vertical-align:top;width:33%;">
                        <a href="{{ $url }}" style="color:#ea580c;font-size:12px;font-weight:700;text-decoration:none;">{{ $label }}</a>
                        <img src="{{ $url }}" alt="{{ $label }}" width="180" style="display:block;margin-top:6px;width:100%;max-width:180px;height:auto;border-radius:8px;border:1px solid #e2e8f0;">
                    </td>
                @endif
            @endforeach
        </tr>
    </table>
@endif
