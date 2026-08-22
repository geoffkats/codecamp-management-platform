<div class="p-6">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Settings</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Manage your application branding and contact information</p>
        </div>

        {{-- Success Message --}}
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-green-800 dark:text-green-200">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        <form wire:submit="save">
            {{-- Branding Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Branding</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- App Name --}}
                    <div>
                        <flux:input 
                            wire:model="settings.app_name" 
                            label="Application Name" 
                            placeholder="Code Academy Uganda"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This will appear in the browser title and throughout the application</p>
                    </div>

                    {{-- Short Name --}}
                    <div>
                        <flux:input 
                            wire:model="settings.app_short_name" 
                            label="Short Name / Abbreviation" 
                            placeholder="CAU"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used in compact spaces</p>
                    </div>

                    {{-- Tagline --}}
                    <div>
                        <flux:input 
                            wire:model="settings.app_tagline" 
                            label="Tagline" 
                            placeholder="E-Learning Platform"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Subtitle or description</p>
                    </div>

                    {{-- Favicon --}}
                    <div>
                        <flux:label>Favicon</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($currentFavicon)
                                <img src="{{ asset('storage/' . $currentFavicon) }}" alt="Current Favicon" class="w-8 h-8 rounded">
                            @endif
                            <flux:input type="file" wire:model="favicon" accept="image/*" />
                        </div>
                        @if($favicon)
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">New favicon selected</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 32x32px or 64x64px ICO, PNG, or SVG</p>
                    </div>

                    {{-- Logo --}}
                    <div>
                        <flux:label>Logo (Light Mode)</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($currentLogo)
                                <img src="{{ asset('storage/' . $currentLogo) }}" alt="Current Logo" class="h-12 rounded">
                            @endif
                            <flux:input type="file" wire:model="logo" accept="image/*" />
                        </div>
                        @if($logo)
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">New logo selected</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: PNG or SVG with transparent background</p>
                    </div>

                    {{-- Logo Dark --}}
                    <div>
                        <flux:label>Logo (Dark Mode)</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($currentLogoDark)
                                <div class="bg-gray-900 p-2 rounded">
                                    <img src="{{ asset('storage/' . $currentLogoDark) }}" alt="Current Dark Logo" class="h-12">
                                </div>
                            @endif
                            <flux:input type="file" wire:model="logo_dark" accept="image/*" />
                        </div>
                        @if($logo_dark)
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">New dark mode logo selected</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Logo optimized for dark backgrounds</p>
                    </div>
                </div>
            </div>

            {{-- Contact Information Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Contact Information</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Email --}}
                    <div>
                        <flux:input 
                            wire:model="settings.contact_email" 
                            label="Contact Email" 
                            type="email"
                            placeholder="info@codeacademy.ug"
                        />
                    </div>

                    {{-- Phone --}}
                    <div>
                        <flux:input 
                            wire:model="settings.contact_phone" 
                            label="Contact Phone" 
                            placeholder="+256 784 781926"
                        />
                    </div>

                    {{-- Address --}}
                    <div>
                        <flux:textarea 
                            wire:model="settings.contact_address" 
                            label="Physical Address" 
                            placeholder="Mpererwe, Mugalu Zone, Kampala"
                            rows="3"
                        />
                    </div>
                </div>
            </div>

            {{-- Certificate Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Certificate</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Branding and artwork used when generating student certificates</p>
                </div>
                <div class="p-6 space-y-6">

                    {{-- Use artwork toggle --}}
                    <label class="flex items-start gap-3">
                        <input type="checkbox" wire:model="certificateUseBackground"
                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block font-medium text-gray-900 dark:text-white">Use certificate artwork</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">When on, certificates are rendered over the uploaded artwork (logo, frame, watermark baked in). When off, a plain HTML layout is used.</span>
                        </span>
                    </label>

                    {{-- Certificate artwork upload --}}
                    <div>
                        <flux:label>Certificate Artwork (full A4 background)</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($currentCertificateBackground)
                                <img src="{{ asset('storage/' . $currentCertificateBackground) }}" alt="Current Certificate Artwork" class="h-32 rounded border border-gray-200 dark:border-gray-700">
                            @else
                                <img src="{{ asset('certs/ict_bg.png') }}" alt="Default Certificate Artwork" class="h-32 rounded border border-gray-200 dark:border-gray-700">
                            @endif
                            <div class="flex-1">
                                <flux:input type="file" wire:model="certificate_background" accept="image/*" />
                                @if($certificate_background)
                                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">New artwork selected</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @if($usingDefaultArtwork)
                                        Currently using the bundled default artwork.
                                    @else
                                        Using a custom uploaded artwork.
                                    @endif
                                    Recommended: A4 portrait (≈1191×1684px), PNG. Dynamic text is positioned to match the default layout.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Colors --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:label>Brand Color (name, modules, frame)</flux:label>
                            <div class="mt-2 flex items-center gap-3">
                                <input type="color" wire:model="settings.certificate_brand_color"
                                    class="h-10 w-14 cursor-pointer rounded border border-gray-300 dark:border-gray-600 bg-transparent">
                                <flux:input wire:model="settings.certificate_brand_color" placeholder="#1546c0" class="flex-1" />
                            </div>
                        </div>
                        <div>
                            <flux:label>Label Color (date, captions)</flux:label>
                            <div class="mt-2 flex items-center gap-3">
                                <input type="color" wire:model="settings.certificate_label_color"
                                    class="h-10 w-14 cursor-pointer rounded border border-gray-300 dark:border-gray-600 bg-transparent">
                                <flux:input wire:model="settings.certificate_label_color" placeholder="#2d7fd4" class="flex-1" />
                            </div>
                        </div>
                    </div>

                    {{-- Executive director / signatory lines --}}
                    <div class="space-y-4 rounded-lg border border-slate-200 p-4 dark:border-zinc-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Signatory names</h3>
                        <flux:input wire:model="settings.certificate_executive_director"
                            label="Default signatory line"
                            placeholder="Edward Ssempala, Executive Director Code Academy Uganda" />
                        <flux:input wire:model="settings.certificate_executive_director_ict"
                            label="ICT program signatory (optional — falls back to default)"
                            placeholder="ICT Director name and title" />
                        <flux:input wire:model="settings.certificate_executive_director_codecamp"
                            label="Code Camp signatory (optional — falls back to default)"
                            placeholder="Code Camp Director name and title" />
                    </div>

                    {{-- Electronic signatures --}}
                    <div class="space-y-4 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4 dark:border-indigo-900/40 dark:bg-indigo-950/20">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Electronic signatures</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Upload PNG with transparent background. Used automatically on every issued certificate.</p>

                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="certificateShowSignature"
                                class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>
                                <span class="block font-medium text-gray-900 dark:text-white">Show signature image</span>
                                <span class="block text-xs text-gray-500">Overlay the uploaded signature on certificates.</span>
                            </span>
                        </label>

                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="certificateShowSignatoryText"
                                class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>
                                <span class="block font-medium text-gray-900 dark:text-white">Show signatory name line (overlay)</span>
                                <span class="block text-xs text-gray-500">Only needed for <strong>custom artwork</strong> without a baked-in name. The default template already shows “Edward Ssempala…” — leave this off to avoid duplicates.</span>
                            </span>
                        </label>

                        @foreach([
                            'default' => ['label' => 'Default signature', 'file' => 'certificate_signature', 'current' => $currentSignatureDefault],
                            'ict' => ['label' => 'ICT signature (optional)', 'file' => 'certificate_signature_ict', 'current' => $currentSignatureIct],
                            'codecamp' => ['label' => 'Code Camp signature (optional)', 'file' => 'certificate_signature_codecamp', 'current' => $currentSignatureCodecamp],
                        ] as $key => $sig)
                            <div>
                                <flux:label>{{ $sig['label'] }}</flux:label>
                                <div class="mt-2 flex items-center gap-4">
                                    @if($sig['current'])
                                        <img src="{{ asset('storage/' . $sig['current']) }}" alt="Signature" class="h-16 rounded border border-gray-200 bg-white p-1 dark:border-gray-700">
                                    @else
                                        <div class="flex h-16 w-28 items-center justify-center rounded border border-dashed border-gray-300 text-xs text-gray-400">No file</div>
                                    @endif
                                    <flux:input type="file" wire:model="{{ $sig['file'] }}" accept="image/png,image/jpeg,image/webp" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Overlay position tuning --}}
                    <div class="space-y-4 rounded-lg border border-slate-200 p-4 dark:border-zinc-700">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Overlay positions (mm)</h3>
                            <div class="flex gap-2">
                                <flux:button type="button" size="sm" variant="ghost" wire:click="refreshCertificatePreview">Refresh preview</flux:button>
                                <flux:button type="button" size="sm" variant="ghost" wire:click="resetCertificatePositions">Reset to defaults</flux:button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Adjust if your artwork layout differs. Save settings to persist.</p>

                        <p class="text-xs font-semibold uppercase text-gray-500">Signature image (anchored from page bottom to the line)</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_sig_bottom_mm" label="Bottom (mm)" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_sig_left_mm" label="Left" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_sig_width_mm" label="Max width" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_sig_max_height_mm" label="Max height" />
                        </div>

                        <p class="text-xs font-semibold uppercase text-gray-500">Signatory text</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_signatory_top_mm" label="Top" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_signatory_left_mm" label="Left" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_signatory_width_mm" label="Width" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_signatory_font_pt" label="Font pt" />
                        </div>

                        <p class="text-xs font-semibold uppercase text-gray-500">Issue date</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_date_top_mm" label="Top" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_date_left_mm" label="Left" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_date_width_mm" label="Width" />
                            <flux:input type="number" step="0.5" wire:model.live.debounce.500ms="settings.certificate_date_font_pt" label="Font pt" />
                        </div>
                    </div>

                    {{-- Live preview --}}
                    <div class="rounded-lg border border-slate-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2 dark:border-zinc-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Live certificate preview</h3>
                            <a href="{{ $certificatePreviewUrl }}" target="_blank" class="text-xs text-blue-600 hover:underline">Open full size</a>
                        </div>
                        <div class="overflow-auto bg-slate-100 p-2 dark:bg-zinc-900" style="max-height: 520px;">
                            <iframe
                                src="{{ $certificatePreviewUrl }}"
                                wire:key="cert-preview-{{ $certificatePreviewKey }}"
                                class="mx-auto border-0 bg-white shadow"
                                style="width: 420px; height: 594px;"
                                title="Certificate preview"
                            ></iframe>
                        </div>
                        <p class="px-4 py-2 text-xs text-gray-500">Signature images appear after you save. Position tweaks refresh automatically.</p>
                    </div>

                    {{-- Numbers --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:input type="number" step="0.5" min="0" max="30"
                                wire:model="settings.certificate_border_width"
                                label="Frame thickness (mm)" placeholder="5" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only used in HTML fallback mode.</p>
                        </div>
                        <div>
                            <flux:input type="number" min="0" max="100"
                                wire:model="settings.certificate_min_progress"
                                label="Min progress to be 'ready' (%)" placeholder="80" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Website Registration API --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-sky-50 to-cyan-50 dark:from-sky-900/20 dark:to-cyan-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Website Registration API</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Connect CodeCamp to the public website so staff can search registrations and autofill student intake forms.</p>
                </div>
                <div class="p-6 space-y-6">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" wire:model="cauRegistrationApiEnabled"
                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block font-medium text-gray-900 dark:text-white">Enable website registration lookup</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Turn this on to show “Import From Website Registration” when creating students. Save settings after enabling.</span>
                        </span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:input
                                wire:model="cauRegistrationApiUrl"
                                label="Website URL"
                                placeholder="https://codeacademyug.org"
                                type="url"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use the site root only, e.g. <span class="font-mono">https://codeacademyug.org</span> — not <span class="font-mono">/api/codecamp</span>. For local testing use your Herd URL, e.g. <span class="font-mono">https://cau-uganda-official.test</span>.</p>
                        </div>
                        <div>
                            <flux:input
                                type="number"
                                min="3"
                                max="60"
                                wire:model="cauRegistrationApiTimeout"
                                label="Request timeout (seconds)"
                                placeholder="10"
                            />
                        </div>
                    </div>

                    <div>
                        <flux:input
                            wire:model="cauRegistrationApiKey"
                            label="API key"
                            type="password"
                            placeholder="{{ $cauRegistrationApiKeyHint ? 'Leave blank to keep current key' : 'Paste key from website admin' }}"
                            autocomplete="off"
                        />
                        @error('cauRegistrationApiKey')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @if ($cauRegistrationApiKeyHint)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Current key: <span class="font-mono">{{ $cauRegistrationApiKeyHint }}</span></p>
                        @else
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Generate a key on the website: Admin → System → CodeCamp API Keys.</p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/40">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Test connection</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tests the URL and key shown above. Click <strong>Save Settings</strong> at the bottom to keep them for student registration search.</p>
                            </div>
                            <flux:button type="button" variant="ghost" wire:click="testWebsiteRegistrationConnection" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="testWebsiteRegistrationConnection">Test connection</span>
                                <span wire:loading wire:target="testWebsiteRegistrationConnection">Testing…</span>
                            </flux:button>
                        </div>
                        @if ($connectionTestMessage)
                            <div @class([
                                'mt-3 rounded-lg border px-3 py-2 text-sm',
                                'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200' => $connectionTestStatus === 'success',
                                'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200' => $connectionTestStatus === 'error',
                            ])>
                                {{ $connectionTestMessage }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Google Analytics / Tag Manager / Ads --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Google Analytics &amp; Ads</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Track site traffic and registration conversions for Google Ads Search campaigns.
                        Prefer GTM; configure GA4 and Ads tags inside the GTM container.
                    </p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-100">
                        Conversion fires only after a successful registration redirect to the thank-you page
                        (<span class="font-mono text-xs">(/register/thank-you)</span>
                        via <span class="font-mono text-xs">registration_complete</span>
                        → map that in GTM to GA4 event <span class="font-mono text-xs">generate_lead</span>.
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:input
                                wire:model="googleGtmId"
                                label="GTM Container ID"
                                placeholder="GTM-XXXXXXX"
                            />
                            @error('googleGtmId')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Preferred. When set, GA4 is loaded through GTM — leave Measurement ID empty or use it only as documentation.</p>
                        </div>
                        <div>
                            <flux:input
                                wire:model="googleGa4MeasurementId"
                                label="GA4 Measurement ID"
                                placeholder="G-XXXXXXXXXX"
                            />
                            @error('googleGa4MeasurementId')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used only when GTM is empty (direct gtag.js). With GTM, add a GA4 Configuration tag in the container instead.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:input
                                wire:model="googleAdsConversionId"
                                label="Google Ads Conversion ID"
                                placeholder="AW-XXXXXXXXX"
                            />
                            @error('googleAdsConversionId')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">For your GTM Google Ads Conversion tag (or Ads UI). Not injected as a hardcoded page script.</p>
                        </div>
                        <div>
                            <flux:input
                                wire:model="googleAdsConversionLabel"
                                label="Google Ads Conversion Label"
                                placeholder="abcdefghijklmnop"
                            />
                            @error('googleAdsConversionLabel')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pair with Conversion ID in GTM. Recommended: import GA4 <span class="font-mono">generate_lead</span> into Google Ads as the primary conversion.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" class="px-8">
                    Save Settings
                </flux:button>
            </div>
        </form>
    </div>
</div>
