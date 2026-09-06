<?php

namespace JeffersonGoncalves\Demio;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JeffersonGoncalves\Demio\Exceptions\DemioAuthenticationException;
use Throwable;

/**
 * Demio (my.demio.com) REST API v1 client. Wraps events, registration, and
 * participant lookups behind a small static client, threading the
 * Api-Key/Api-Secret headers and centralising 401 detection so callers get a
 * thrown DemioAuthenticationException rather than a silent null.
 */
class DemioClient
{
    /**
     * @return array<string, mixed>|null
     */
    public static function ping(): ?array
    {
        return self::decode(self::get('/ping'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function events(?string $type = null): array
    {
        $query = $type !== null ? ['type' => $type] : [];

        $data = self::decode(self::get('/events', $query));

        return is_array($data) ? array_values($data) : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function event(string $eventId): ?array
    {
        return self::decode(self::get("/event/{$eventId}"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function eventDate(string $eventId, string $dateId): ?array
    {
        return self::decode(self::get("/event/{$eventId}/date/{$dateId}"));
    }

    /**
     * Registers a participant for an event (and optionally a specific date).
     *
     * @return array<string, mixed>|null
     */
    public static function register(string $eventId, string $name, string $email, ?string $dateId = null, ?string $refUrl = null): ?array
    {
        $body = ['id' => $eventId, 'name' => $name, 'email' => $email];

        if ($dateId !== null) {
            $body['date_id'] = $dateId;
        }

        if ($refUrl !== null) {
            $body['ref_url'] = $refUrl;
        }

        return self::decode(self::post('/event/register', $body));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function participants(string $dateId): array
    {
        $data = self::decode(self::get("/date/{$dateId}/participants"));

        return is_array($data) ? array_values($data) : [];
    }

    /**
     * @param  array<string, mixed>  $query
     *
     * @throws DemioAuthenticationException
     */
    private static function get(string $path, array $query = []): ?Response
    {
        return self::request(fn () => self::client()->get(self::baseUrl().$path, $query), 'demio_get', $path);
    }

    /**
     * @param  array<string, mixed>  $body
     *
     * @throws DemioAuthenticationException
     */
    private static function post(string $path, array $body): ?Response
    {
        return self::request(fn () => self::client()->post(self::baseUrl().$path, $body), 'demio_post', $path);
    }

    /**
     * @param  callable(): Response  $send
     *
     * @throws DemioAuthenticationException
     */
    private static function request(callable $send, string $context, string $target): ?Response
    {
        try {
            $response = $send();
        } catch (Throwable $e) {
            self::logFailure($context, $target, $e);

            return null;
        }

        // 401 detection runs outside the catch above so the thrown exception
        // propagates to the caller instead of being swallowed and logged as
        // a generic fetch failure.
        if ($response->status() === 401) {
            throw new DemioAuthenticationException;
        }

        return $response;
    }

    /**
     * @return array<string|int, mixed>|null
     */
    private static function decode(?Response $response): ?array
    {
        if ($response === null || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? $data : null;
    }

    private static function client(): PendingRequest
    {
        return Http::timeout(self::timeout())->withHeaders([
            'Api-Key' => (string) config('demio.api_key'),
            'Api-Secret' => (string) config('demio.api_secret'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
    }

    private static function logFailure(string $context, string $target, Throwable $e): void
    {
        Log::warning('DemioClient outbound fetch failed', [
            'context' => $context,
            'target' => $target,
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);
    }

    private static function baseUrl(): string
    {
        $baseUrl = config('demio.base_url');

        return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://my.demio.com/api/v1';
    }

    private static function timeout(): int
    {
        return (int) config('demio.timeout', 8);
    }
}
