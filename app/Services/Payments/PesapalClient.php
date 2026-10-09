<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin client for Pesapal API v3.
 *
 * @see https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/api-reference
 */
class PesapalClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.pesapal.consumer_key')) && filled(config('services.pesapal.consumer_secret'));
    }

    public function environment(): string
    {
        return config('services.pesapal.environment') === 'live' ? 'live' : 'sandbox';
    }

    /**
     * @param  array<string, mixed>  $order  id, currency, amount, description, callback_url, billing_address
     * @return array{order_tracking_id: string, merchant_reference: string, redirect_url: string}
     */
    public function submitOrder(array $order): array
    {
        $response = $this->post('/api/Transactions/SubmitOrderRequest', [
            ...$order,
            'notification_id' => $this->ipnId(),
        ]);

        if (empty($response['redirect_url']) || empty($response['order_tracking_id'])) {
            throw new RuntimeException('Pesapal did not return a payment page.');
        }

        return $response;
    }

    /**
     * @return array<string, mixed> includes payment_status_description, status_code, payment_method, confirmation_code, amount
     */
    public function transactionStatus(string $orderTrackingId): array
    {
        $response = $this->request()
            ->withToken($this->token())
            ->get($this->url('/api/Transactions/GetTransactionStatus'), ['orderTrackingId' => $orderTrackingId]);

        return $this->decode($response->json(), $response->status());
    }

    public function registerIpn(string $url): string
    {
        $response = $this->post('/api/URLSetup/RegisterIPN', [
            'url' => $url,
            'ipn_notification_type' => 'GET',
        ]);

        return (string) ($response['ipn_id'] ?? throw new RuntimeException('Pesapal did not return an IPN id.'));
    }

    public function ipnId(): string
    {
        if (filled(config('services.pesapal.ipn_id'))) {
            return (string) config('services.pesapal.ipn_id');
        }

        $url = route('registration.payments.pesapal.ipn');

        return Cache::rememberForever(
            'pesapal.ipn.'.$this->environment().'.'.md5($url),
            fn () => $this->registerIpn($url)
        );
    }

    private function token(): string
    {
        // Tokens last five minutes; refresh a little early.
        return Cache::remember('pesapal.token.'.$this->environment(), now()->addMinutes(4), function () {
            $response = $this->request()->post($this->url('/api/Auth/RequestToken'), [
                'consumer_key' => config('services.pesapal.consumer_key'),
                'consumer_secret' => config('services.pesapal.consumer_secret'),
            ]);

            $data = $this->decode($response->json(), $response->status());

            return (string) ($data['token'] ?? throw new RuntimeException('Pesapal did not return a token.'));
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload): array
    {
        $response = $this->request()->withToken($this->token())->post($this->url($path), $payload);

        return $this->decode($response->json(), $response->status());
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(mixed $data, int $httpStatus): array
    {
        if (! is_array($data)) {
            throw new RuntimeException("Pesapal returned an unexpected response (HTTP {$httpStatus}).");
        }

        $error = $data['error'] ?? null;
        if ($httpStatus >= 400 || (is_array($error) && array_filter($error)) || (is_string($error) && $error !== '')) {
            $message = is_array($error)
                ? (filled($error['message'] ?? null) ? $error['message'] : (filled($error['code'] ?? null) ? $error['code'] : json_encode($error)))
                : ($error ?: "HTTP {$httpStatus}");

            throw new RuntimeException('Pesapal error: '.$message);
        }

        return $data;
    }

    private function request(): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Pesapal keys are not configured.');
        }

        return Http::acceptJson()->asJson()->timeout(20);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.pesapal.base_urls.'.$this->environment()), '/').$path;
    }
}
