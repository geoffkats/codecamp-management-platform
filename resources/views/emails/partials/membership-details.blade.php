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
        'Medical / special needs' => $meta['medical_notes'] ?? null,
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
