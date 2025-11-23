<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
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

    public function mount()
    {
        // Check if user is admin
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $this->loadSettings();
    }

    public function loadSettings()
    {
        $allSettings = SystemSetting::getAllSettings();
        
        foreach ($allSettings as $key => $value) {
            if (!in_array($key, ['favicon', 'logo', 'logo_dark'])) {
                $this->settings[$key] = $value;
            }
        }
    }

    public function save()
    {
        // Validate
        $this->validate([
            'settings.app_name' => 'required|string|max:255',
            'settings.app_short_name' => 'nullable|string|max:50',
            'settings.app_tagline' => 'nullable|string|max:255',
            'settings.contact_email' => 'nullable|email',
            'settings.contact_phone' => 'nullable|string|max:50',
            'settings.contact_address' => 'nullable|string|max:500',
            'favicon' => 'nullable|image|max:1024', // 1MB
            'logo' => 'nullable|image|max:2048', // 2MB
            'logo_dark' => 'nullable|image|max:2048', // 2MB
        ]);

        // Save text settings
        foreach ($this->settings as $key => $value) {
            SystemSetting::set($key, $value);
        }

        // Handle favicon upload
        if ($this->favicon) {
            $path = $this->favicon->store('settings', 'public');
            SystemSetting::set('favicon', $path);
        }

        // Handle logo upload
        if ($this->logo) {
            $path = $this->logo->store('settings', 'public');
            SystemSetting::set('logo', $path);
        }

        // Handle dark logo upload
        if ($this->logo_dark) {
            $path = $this->logo_dark->store('settings', 'public');
            SystemSetting::set('logo_dark', $path);
        }

        // Clear cache
        SystemSetting::clearCache();

        // Update .env file for app name
        $this->updateEnvFile('APP_NAME', $this->settings['app_name']);

        session()->flash('message', 'Settings saved successfully!');
        
        $this->reset(['favicon', 'logo', 'logo_dark']);
        $this->loadSettings();
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

    public function render()
    {
        return view('livewire.admin.system-settings', [
            'currentFavicon' => SystemSetting::get('favicon'),
            'currentLogo' => SystemSetting::get('logo'),
            'currentLogoDark' => SystemSetting::get('logo_dark'),
        ]);
    }
}
