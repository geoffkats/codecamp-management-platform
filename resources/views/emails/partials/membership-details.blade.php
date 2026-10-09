@php
    $meta = $application->meta ?? [];
    $rows = [
        'Child' => $application->full_name,
        'Date of birth' => $application->date_of_birth?->format('j F Y'),
        'Gender' => $application->gender,
        'School' => $application->organization_name,
        'Class' => $application->school_level,
        'Camp' => $application->preferred_schedule,
        'Course interest' => $application->course_interest,
        'T-shirt size' => $meta['tshirt_size'] ?? null,
        'Parent / guardian' => trim(($meta['parent_name'] ?? '').(! empty($meta['parent_relationship']) ? ' ('.$meta['parent_relationship'].')' : '')),
        'Phone' => $application->phone,
        'Email' => $application->email,
        'Address' => $meta['address'] ?? null,
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
