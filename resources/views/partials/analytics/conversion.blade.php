{{--
    Registration conversion — fire ONLY when the controller flashes
    registration_conversion after a successful DB save + redirect.

    GTM: pushes dataLayer event "registration_complete" (configure a GTM trigger
         on that custom event → GA4 Event tag with event name generate_lead).

    GA4 (no GTM): fires gtag generate_lead directly.
--}}
@php
    use App\Support\GoogleAnalytics;

    $conversion = session('registration_conversion');
    $gtmId = GoogleAnalytics::gtmId();
    $ga4Id = GoogleAnalytics::ga4MeasurementId();
@endphp

@if (is_array($conversion))
    @php
        $payload = [
            'event' => 'registration_complete',
            'registration_type' => $conversion['type'] ?? null,
            'registration_id' => $conversion['id'] ?? null,
        ];
    @endphp

    @if (filled($gtmId))
        <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(@json($payload));
        </script>
    @elseif (filled($ga4Id))
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('event', 'generate_lead', {
                registration_type: @json($payload['registration_type']),
                registration_id: @json($payload['registration_id']),
            });
        </script>
    @endif
@endif
