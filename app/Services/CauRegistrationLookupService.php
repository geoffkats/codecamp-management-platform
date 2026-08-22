<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CauRegistrationLookupService
{
    protected ?string $lastError = null;

    public function isEnabled(): bool
    {
        return $this->enabled() && $this->apiKey() !== '';
    }

    public function enabled(): bool
    {
        $stored = SystemSetting::get('cau_registration_api_enabled');

        if ($stored !== null && $stored !== '') {
            return in_array($stored, ['1', 1, true, 'true'], true);
        }

        return (bool) config('cau_registrations.enabled');
    }

    public function baseUrl(): string
    {
        $stored = SystemSetting::get('cau_registration_api_url');

        $url = is_string($stored) && $stored !== ''
            ? $stored
            : (string) config('cau_registrations.base_url', '');

        return $this->normalizeBaseUrl($url);
    }

    public function searchEndpoint(): string
    {
        return $this->baseUrl().'/api/codecamp/registrations/search';
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    protected function normalizeBaseUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');

        if ($url === '') {
            return '';
        }

        foreach (['/api/codecamp/registrations/search', '/api/codecamp', '/api'] as $suffix) {
            if (str_ends_with($url, $suffix)) {
                $url = substr($url, 0, -strlen($suffix));
                $url = rtrim($url, '/');
            }
        }

        return $url;
    }

    public function apiKey(): string
    {
        $stored = SystemSetting::get('cau_registration_api_key');

        if (is_string($stored) && $stored !== '') {
            try {
                return Crypt::decryptString($stored);
            } catch (\Throwable) {
                return $stored;
            }
        }

        $configured = config('cau_registrations.api_key');

        return is_string($configured) ? $configured : '';
    }

    public function hasStoredApiKey(): bool
    {
        $stored = SystemSetting::get('cau_registration_api_key');

        return is_string($stored) && $stored !== '';
    }

    public function maskedApiKey(): ?string
    {
        $key = $this->apiKey();

        if ($key === '') {
            return null;
        }

        return strlen($key) > 12
            ? substr($key, 0, 12).'…'
            : $key;
    }

    public function timeout(): int
    {
        $stored = SystemSetting::get('cau_registration_api_timeout');

        if ($stored !== null && $stored !== '') {
            return max(3, (int) $stored);
        }

        return max(3, (int) config('cau_registrations.timeout', 10));
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function testConnection(
        ?string $url = null,
        ?string $apiKey = null,
    ): array {
        $baseUrl = $this->normalizeBaseUrl($url ?? $this->baseUrl());
        $apiKey = $apiKey ?? $this->apiKey();

        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'An API key is required. Paste the key from the CAU admin site and try again.',
            ];
        }

        if ($baseUrl === '') {
            return [
                'success' => false,
                'message' => 'Website URL is required.',
            ];
        }

        try {
            $endpoint = $baseUrl.'/api/codecamp/registrations/search';

            $response = $this->httpClient($baseUrl, $apiKey)->get('/api/codecamp/registrations/search', [
                'q' => 'test',
                'limit' => 1,
            ]);

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized — check that the API key matches an active key from the website admin (System → CodeCamp API Keys).',
                ];
            }

            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'message' => 'API route not found (HTTP 404) at '.$endpoint.'. '
                        .'The website may not have the latest CodeCamp API code deployed yet. '
                        .'Upload the updated CAU site files to production, run migrations, then run: php artisan route:clear && php artisan optimize:clear. '
                        .'For local testing, set Website URL to your local CAU site (e.g. https://cau-uganda-official.test).',
                ];
            }

            if ($response->status() === 500) {
                $body = $response->body();
                $preview = $body ? ' Server said: '.strip_tags(substr($body, 0, 300)) : '';

                return [
                    'success' => false,
                    'message' => 'The website API returned a server error (HTTP 500) at '.$endpoint.'.'.$preview,
                ];
            }

            if ($response->status() === 503) {
                return [
                    'success' => false,
                    'message' => 'The website API is not configured yet. Generate a key on the CAU site under System → CodeCamp API Keys.',
                ];
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connected successfully to '.$endpoint,
                ];
            }

            return [
                'success' => false,
                'message' => 'Unexpected response (HTTP '.$response->status().') from '.$endpoint.'.',
            ];
        } catch (RequestException $exception) {
            return [
                'success' => false,
                'message' => $this->formatHttpError($exception),
            ];
        } catch (\Throwable $exception) {
            Log::warning('CAU registration connection test failed.', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed: '.$exception->getMessage(),
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, ?string $source = null, int $limit = 20): array
    {
        $this->lastError = null;

        if (! $this->isEnabled() || mb_strlen(trim($query)) < 2) {
            return [];
        }

        try {
            $response = $this->httpClient()->get('/api/codecamp/registrations/search', array_filter([
                'q' => trim($query),
                'source' => $source,
                'limit' => $limit,
            ]))->throw();

            return $response->json('data') ?? [];
        } catch (RequestException $exception) {
            $this->lastError = $this->formatHttpError($exception);

            Log::warning('CAU registration search failed.', [
                'status' => $exception->response?->status(),
                'body' => $exception->response?->json(),
                'endpoint' => $this->searchEndpoint(),
            ]);

            return [];
        } catch (\Throwable $exception) {
            $this->lastError = 'Could not reach the website registration API: '.$exception->getMessage();

            Log::warning('CAU registration search error.', [
                'message' => $exception->getMessage(),
                'endpoint' => $this->searchEndpoint(),
            ]);

            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $externalId): ?array
    {
        $this->lastError = null;

        if (! $this->isEnabled() || $externalId === '') {
            return null;
        }

        try {
            $response = $this->httpClient()
                ->get('/api/codecamp/registrations/external/'.urlencode($externalId))
                ->throw();

            return $response->json('data');
        } catch (RequestException $exception) {
            $this->lastError = $this->formatHttpError($exception);

            Log::warning('CAU registration lookup failed.', [
                'external_id' => $externalId,
                'status' => $exception->response?->status(),
                'body' => $exception->response?->json(),
            ]);

            return null;
        } catch (\Throwable $exception) {
            $this->lastError = 'Could not reach the website registration API: '.$exception->getMessage();

            Log::warning('CAU registration lookup error.', [
                'external_id' => $externalId,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function httpClient(?string $baseUrl = null, ?string $apiKey = null): PendingRequest
    {
        $baseUrl = $this->normalizeBaseUrl($baseUrl ?? $this->baseUrl());

        $client = Http::baseUrl($baseUrl)
            ->timeout($this->timeout())
            ->withToken($apiKey ?? $this->apiKey())
            ->acceptJson();

        if ($this->shouldDisableSslVerification($baseUrl)) {
            $client = $client->withOptions(['verify' => false]);
        }

        return $client;
    }

    protected function shouldDisableSslVerification(?string $baseUrl = null): bool
    {
        if (! app()->environment('local')) {
            return false;
        }

        $host = parse_url($baseUrl ?? $this->baseUrl(), PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        return str_ends_with($host, '.test')
            || str_ends_with($host, '.local')
            || in_array($host, ['localhost', '127.0.0.1', 'codecamp.test'], true);
    }

    protected function formatHttpError(RequestException $exception): string
    {
        $status = $exception->response?->status();
        $endpoint = $this->searchEndpoint();

        return match ($status) {
            401 => 'Website API rejected the API key. Update it under Admin → More → Settings → Website Registration API.',
            404 => 'Website API route not found (404). Check the Website URL setting, or deploy the latest CAU site code.',
            500 => 'Website API returned a server error (500). Run migrations on the codeacademyug.org server.',
            503 => 'Website API is not configured yet. Generate a key on the CAU admin site first.',
            default => 'Website registration search failed (HTTP '.($status ?? 'error').') at '.$endpoint.'.',
        };
    }
}
