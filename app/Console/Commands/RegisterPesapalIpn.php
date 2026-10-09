<?php

namespace App\Console\Commands;

use App\Services\Payments\PesapalClient;
use Illuminate\Console\Command;

class RegisterPesapalIpn extends Command
{
    protected $signature = 'pesapal:register-ipn {--url= : Override the IPN URL (defaults to the app route)}';

    protected $description = 'Register the Pesapal IPN URL and print the ipn_id to put in PESAPAL_IPN_ID';

    public function handle(PesapalClient $pesapal): int
    {
        if (! $pesapal->isConfigured()) {
            $this->error('Set PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET first.');

            return self::FAILURE;
        }

        $url = $this->option('url') ?: route('registration.payments.pesapal.ipn');

        try {
            $ipnId = $pesapal->registerIpn($url);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Registered {$url} on Pesapal ({$pesapal->environment()}).");
        $this->line("PESAPAL_IPN_ID={$ipnId}");

        return self::SUCCESS;
    }
}
