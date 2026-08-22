<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
use App\Services\CauRegistrationLookupService;
use App\Services\CertificateDataService;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class SystemSettings extends Component
{
    use WithFileUploads;

    public $settings = [];

    public $favicon;

    public $logo;

    public $logo_dark;

    public $certificate_background;

    public $certificate_signature;

    public $certificate_signature_ict;

    public $certificate_signature_codecamp;

    public bool $certificateUseBackground = true;

    public bool $certificateShowSignature = true;

    public bool $certificateShowSignatoryText = true;

    public int $certificatePreviewKey = 0;

    public bool $cauRegistrationApiEnabled = false;

    public string $cauRegistrationApiUrl = '';

    public string $cauRegistrationApiKey = '';

    public string $cauRegistrationApiTimeout = '10';

    public ?string $cauRegistrationApiKeyHint = null;

    public ?string $connectionTestMessage = null;

    public ?string $connectionTestStatus = null;

    public string $googleGtmId = '';

    public string $googleGa4MeasurementId = '';

    public string $googleAdsConversionId = '';

    public string $googleAdsConversionLabel = '';

    public function mount()
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $this->loadSettings();
    }

    public function loadSettings()
    {
        $allSettings = SystemSetting::getAllSettings();

        $excluded = [
            'favicon', 'logo', 'logo_dark',
            'certificate_background', 'certificate_use_background',
            'certificate_signature', 'certificate_signature_ict', 'certificate_signature_codecamp',
            'certificate_show_signature', 'certificate_show_signatory_text',
            'cau_registration_api_enabled', 'cau_registration_api_url',
            'cau_registration_api_key', 'cau_registration_api_timeout',
            'google_gtm_id', 'google_ga4_measurement_id',
            'google_ads_conversion_id', 'google_ads_conversion_label',
        ];

        foreach ($allSettings as $key => $value) {
            if (! in_array($key, $excluded, true)) {
                $this->settings[$key] = $value;
            }
        }

        $useBg = $allSettings['certificate_use_background'] ?? null;
        $this->certificateUseBackground = $useBg !== null
            ? in_array($useBg, ['1', 1, true, 'true'], true)
            : (bool) config('certificate.use_background_image', true);

        $showSig = $allSettings['certificate_show_signature'] ?? null;
        $this->certificateShowSignature = $showSig !== null
            ? in_array($showSig, ['1', 1, true, 'true'], true)
            : true;

        $showText = $allSettings['certificate_show_signatory_text'] ?? null;
        $this->certificateShowSignatoryText = $showText !== null
            ? in_array($showText, ['1', 1, true, 'true'], true)
            : false;

        $defaults = app(CertificateDataService::class)->defaultLayoutSettingKeys();

        $this->settings['certificate_brand_color'] = $this->settings['certificate_brand_color']
            ?? config('certificate.brand_color', '#1546c0');
        $this->settings['certificate_label_color'] = $this->settings['certificate_label_color']
            ?? config('certificate.label_color', '#2d7fd4');
        $this->settings['certificate_border_width'] = $this->settings['certificate_border_width']
            ?? (string) config('certificate.border_width_mm', 5);
        $this->settings['certificate_executive_director'] = $this->settings['certificate_executive_director']
            ?? config('certificate.executive_director');
        $this->settings['certificate_executive_director_ict'] = $this->settings['certificate_executive_director_ict'] ?? '';
        $this->settings['certificate_executive_director_codecamp'] = $this->settings['certificate_executive_director_codecamp'] ?? '';
        $this->settings['certificate_min_progress'] = $this->settings['certificate_min_progress']
            ?? (string) config('certificate.min_progress_percent', 80);

        foreach ($defaults as $key => $value) {
            $this->settings[$key] = $this->settings[$key] ?? $value;
        }

        $this->loadWebsiteRegistrationSettings();
        $this->loadGoogleAnalyticsSettings();
    }

    protected function loadWebsiteRegistrationSettings(): void
    {
        $lookup = app(CauRegistrationLookupService::class);

        $this->cauRegistrationApiEnabled = $lookup->enabled();
        $this->cauRegistrationApiUrl = $lookup->baseUrl() !== ''
            ? $lookup->baseUrl()
            : rtrim((string) config('cau_registrations.base_url', 'https://codeacademyug.org'), '/');
        $this->cauRegistrationApiTimeout = (string) $lookup->timeout();
        $this->cauRegistrationApiKey = '';
        $this->cauRegistrationApiKeyHint = $lookup->maskedApiKey();
        $this->connectionTestMessage = null;
        $this->connectionTestStatus = null;
    }

    protected function loadGoogleAnalyticsSettings(): void
    {
        $all = SystemSetting::getAllSettings();

        $this->googleGtmId = array_key_exists('google_gtm_id', $all)
            ? (string) $all['google_gtm_id']
            : (string) config('services.google.gtm_id', '');
        $this->googleGa4MeasurementId = array_key_exists('google_ga4_measurement_id', $all)
            ? (string) $all['google_ga4_measurement_id']
            : (string) config('services.google.ga4_measurement_id', '');
        $this->googleAdsConversionId = array_key_exists('google_ads_conversion_id', $all)
            ? (string) $all['google_ads_conversion_id']
            : (string) config('services.google.ads_conversion_id', '');
        $this->googleAdsConversionLabel = array_key_exists('google_ads_conversion_label', $all)
            ? (string) $all['google_ads_conversion_label']
            : (string) config('services.google.ads_conversion_label', '');
    }

    public function resetCertificatePositions(): void
    {
        foreach (app(CertificateDataService::class)->defaultLayoutSettingKeys() as $key => $value) {
            $this->settings[$key] = $value;
            SystemSetting::set($key, $value);
        }

        SystemSetting::clearCache();
        $this->certificatePreviewKey++;
        session()->flash('message', 'Certificate overlay positions reset to defaults.');
    }

    public function refreshCertificatePreview(): void
    {
        $this->certificatePreviewKey++;
    }

    public function save()
    {
        $this->validate([
            'settings.app_name' => 'required|string|max:255',
            'settings.app_short_name' => 'nullable|string|max:50',
            'settings.app_tagline' => 'nullable|string|max:255',
            'settings.contact_email' => 'nullable|email',
            'settings.contact_phone' => 'nullable|string|max:50',
            'settings.contact_address' => 'nullable|string|max:500',
            'favicon' => 'nullable|image|max:1024',
            'logo' => 'nullable|image|max:2048',
            'logo_dark' => 'nullable|image|max:2048',
            'settings.certificate_brand_color' => 'nullable|string|max:9',
            'settings.certificate_label_color' => 'nullable|string|max:9',
            'settings.certificate_border_width' => 'nullable|numeric|min:0|max:30',
            'settings.certificate_executive_director' => 'nullable|string|max:255',
            'settings.certificate_executive_director_ict' => 'nullable|string|max:255',
            'settings.certificate_executive_director_codecamp' => 'nullable|string|max:255',
            'settings.certificate_min_progress' => 'nullable|integer|min:0|max:100',
            'certificate_background' => 'nullable|image|max:8192',
            'certificate_signature' => 'nullable|image|max:4096',
            'certificate_signature_ict' => 'nullable|image|max:4096',
            'certificate_signature_codecamp' => 'nullable|image|max:4096',
            'cauRegistrationApiUrl' => 'required_if:cauRegistrationApiEnabled,true|nullable|url|max:255',
            'cauRegistrationApiKey' => 'nullable|string|max:255',
            'cauRegistrationApiTimeout' => 'nullable|integer|min:3|max:60',
            'googleGtmId' => ['nullable', 'string', 'max:50', 'regex:/^(GTM-[A-Z0-9]+)?$/i'],
            'googleGa4MeasurementId' => ['nullable', 'string', 'max:50', 'regex:/^(G-[A-Z0-9]+)?$/i'],
            'googleAdsConversionId' => ['nullable', 'string', 'max:50', 'regex:/^(AW-[0-9]+)?$/i'],
            'googleAdsConversionLabel' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]*$/'],
        ], [
            'googleGtmId.regex' => 'GTM Container ID must look like GTM-XXXXXXX.',
            'googleGa4MeasurementId.regex' => 'GA4 Measurement ID must look like G-XXXXXXXXXX.',
            'googleAdsConversionId.regex' => 'Google Ads Conversion ID must look like AW-XXXXXXXXX.',
            'googleAdsConversionLabel.regex' => 'Conversion Label may only contain letters, numbers, underscores, and hyphens.',
        ]);

        if ($this->cauRegistrationApiEnabled && $this->cauRegistrationApiKey === '' && ! app(CauRegistrationLookupService::class)->apiKey()) {
            $this->addError('cauRegistrationApiKey', 'An API key is required when the integration is enabled.');

            return;
        }

        foreach ($this->settings as $key => $value) {
            SystemSetting::set($key, $value);
        }

        SystemSetting::set('certificate_use_background', $this->certificateUseBackground ? '1' : '0');
        SystemSetting::set('certificate_show_signature', $this->certificateShowSignature ? '1' : '0');
        SystemSetting::set('certificate_show_signatory_text', $this->certificateShowSignatoryText ? '1' : '0');

        SystemSetting::set('cau_registration_api_enabled', $this->cauRegistrationApiEnabled ? '1' : '0');
        SystemSetting::set('cau_registration_api_url', rtrim($this->cauRegistrationApiUrl, '/'));
        SystemSetting::set('cau_registration_api_timeout', (string) $this->cauRegistrationApiTimeout);

        if ($this->cauRegistrationApiKey !== '') {
            SystemSetting::set(
                'cau_registration_api_key',
                Crypt::encryptString($this->cauRegistrationApiKey)
            );
        }

        SystemSetting::set('google_gtm_id', trim($this->googleGtmId));
        SystemSetting::set('google_ga4_measurement_id', trim($this->googleGa4MeasurementId));
        SystemSetting::set('google_ads_conversion_id', trim($this->googleAdsConversionId));
        SystemSetting::set('google_ads_conversion_label', trim($this->googleAdsConversionLabel));

        if ($this->favicon) {
            SystemSetting::set('favicon', $this->favicon->store('settings', 'public'));
        }
        if ($this->logo) {
            SystemSetting::set('logo', $this->logo->store('settings', 'public'));
        }
        if ($this->logo_dark) {
            SystemSetting::set('logo_dark', $this->logo_dark->store('settings', 'public'));
        }
        if ($this->certificate_background) {
            SystemSetting::set('certificate_background', $this->certificate_background->store('certificates', 'public'));
        }
        if ($this->certificate_signature) {
            SystemSetting::set('certificate_signature', $this->certificate_signature->store('certificates/signatures', 'public'));
        }
        if ($this->certificate_signature_ict) {
            SystemSetting::set('certificate_signature_ict', $this->certificate_signature_ict->store('certificates/signatures', 'public'));
        }
        if ($this->certificate_signature_codecamp) {
            SystemSetting::set('certificate_signature_codecamp', $this->certificate_signature_codecamp->store('certificates/signatures', 'public'));
        }

        SystemSetting::clearCache();
        $this->updateEnvFile('APP_NAME', $this->settings['app_name']);

        session()->flash('message', 'Settings saved successfully!');

        $this->reset([
            'favicon', 'logo', 'logo_dark',
            'certificate_background',
            'certificate_signature', 'certificate_signature_ict', 'certificate_signature_codecamp',
        ]);
        $this->loadSettings();
        $this->certificatePreviewKey++;
    }

    public function testWebsiteRegistrationConnection(): void
    {
        $this->validate([
            'cauRegistrationApiUrl' => 'required_if:cauRegistrationApiEnabled,true|nullable|url|max:255',
            'cauRegistrationApiKey' => 'nullable|string|max:255',
        ]);

        $service = app(CauRegistrationLookupService::class);

        $apiKey = $this->cauRegistrationApiKey !== ''
            ? $this->cauRegistrationApiKey
            : $service->apiKey();

        $result = $service->testConnection(
            url: $this->cauRegistrationApiUrl,
            apiKey: $apiKey,
        );

        $this->connectionTestStatus = $result['success'] ? 'success' : 'error';
        $this->connectionTestMessage = $result['message'];
    }

    private function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $value = '"' . str_replace('"', '\"', $value) . '"';
            file_put_contents($path, preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                file_get_contents($path)
            ));
        }
    }

    private function buildCertificatePreviewUrl(CertificateDataService $dataService): string
    {
        return $dataService->buildPreviewUrl(
            candidateName: 'KASIMBA ISHAKA TENDO',
            candidateNo: 'STU-2025-0063',
            signatureDate: now()->format('Y-m-d'),
            modules: [
                ['name' => 'Scratch Intermediate', 'version' => '1.0', 'date' => '2025-12-19'],
                ['name' => 'Scratch Advanced', 'version' => '1.0', 'date' => '2026-01-29'],
            ],
            context: [
                'signatory_key' => 'default',
                'layout_overrides' => [
                    'signature' => [
                        'bottom_mm' => $this->settings['certificate_sig_bottom_mm'] ?? null,
                        'left_mm' => $this->settings['certificate_sig_left_mm'] ?? null,
                        'width_mm' => $this->settings['certificate_sig_width_mm'] ?? null,
                        'max_height_mm' => $this->settings['certificate_sig_max_height_mm'] ?? null,
                    ],
                    'signatory' => [
                        'top_mm' => $this->settings['certificate_signatory_top_mm'] ?? null,
                        'left_mm' => $this->settings['certificate_signatory_left_mm'] ?? null,
                        'width_mm' => $this->settings['certificate_signatory_width_mm'] ?? null,
                        'font_size_pt' => $this->settings['certificate_signatory_font_pt'] ?? null,
                    ],
                    'date' => [
                        'top_mm' => $this->settings['certificate_date_top_mm'] ?? null,
                        'left_mm' => $this->settings['certificate_date_left_mm'] ?? null,
                        'width_mm' => $this->settings['certificate_date_width_mm'] ?? null,
                        'font_size_pt' => $this->settings['certificate_date_font_pt'] ?? null,
                    ],
                ],
            ],
        );
    }

    public function render()
    {
        $dataService = app(CertificateDataService::class);

        return view('livewire.admin.system-settings', [
            'currentFavicon' => SystemSetting::get('favicon'),
            'currentLogo' => SystemSetting::get('logo'),
            'currentLogoDark' => SystemSetting::get('logo_dark'),
            'currentCertificateBackground' => SystemSetting::get('certificate_background'),
            'usingDefaultArtwork' => ! SystemSetting::get('certificate_background'),
            'currentSignatureDefault' => SystemSetting::get('certificate_signature'),
            'currentSignatureIct' => SystemSetting::get('certificate_signature_ict'),
            'currentSignatureCodecamp' => SystemSetting::get('certificate_signature_codecamp'),
            'signatoryProfiles' => $dataService->signatoryProfileOptions(),
            'certificatePreviewUrl' => $this->buildCertificatePreviewUrl($dataService) . '&_=' . $this->certificatePreviewKey,
        ]);
    }
}
