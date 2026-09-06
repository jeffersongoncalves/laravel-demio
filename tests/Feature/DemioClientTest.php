<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JeffersonGoncalves\Demio\DemioClient;
use JeffersonGoncalves\Demio\Exceptions\DemioAuthenticationException;

it('pings the api', function () {
    Http::fake([
        'my.demio.com/api/v1/ping' => Http::response(['status' => 'ok'], 200),
    ]);

    expect(DemioClient::ping())->toBe(['status' => 'ok']);
});

it('lists events', function () {
    Http::fake([
        'my.demio.com/api/v1/events*' => Http::response([
            ['id' => '1', 'name' => 'Webinar A'],
            ['id' => '2', 'name' => 'Webinar B'],
        ], 200),
    ]);

    expect(DemioClient::events())->toBe([
        ['id' => '1', 'name' => 'Webinar A'],
        ['id' => '2', 'name' => 'Webinar B'],
    ]);
});

it('filters events by type', function () {
    Http::fake([
        'my.demio.com/api/v1/events*' => Http::response([], 200),
    ]);

    DemioClient::events('upcoming');

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'type=upcoming'));
});

it('fetches a single event', function () {
    Http::fake([
        'my.demio.com/api/v1/event/123' => Http::response(['id' => '123', 'name' => 'Webinar A'], 200),
    ]);

    expect(DemioClient::event('123'))->toBe(['id' => '123', 'name' => 'Webinar A']);
});

it('returns null for a missing event', function () {
    Http::fake([
        'my.demio.com/api/v1/event/*' => Http::response('', 404),
    ]);

    expect(DemioClient::event('missing'))->toBeNull();
});

it('fetches an event date', function () {
    Http::fake([
        'my.demio.com/api/v1/event/123/date/456' => Http::response(['id' => '456'], 200),
    ]);

    expect(DemioClient::eventDate('123', '456'))->toBe(['id' => '456']);
});

it('registers a participant', function () {
    Http::fake([
        'my.demio.com/api/v1/event/register' => Http::response(['success' => true], 200),
    ]);

    expect(DemioClient::register('123', 'Jane Doe', 'jane@example.com'))->toBe(['success' => true]);

    Http::assertSent(function (Request $request) {
        $body = $request->data();

        return $body['id'] === '123' && $body['name'] === 'Jane Doe' && $body['email'] === 'jane@example.com';
    });
});

it('includes date_id and ref_url on registration when given', function () {
    Http::fake([
        'my.demio.com/api/v1/event/register' => Http::response(['success' => true], 200),
    ]);

    DemioClient::register('123', 'Jane Doe', 'jane@example.com', '456', 'https://example.com');

    Http::assertSent(function (Request $request) {
        $body = $request->data();

        return $body['date_id'] === '456' && $body['ref_url'] === 'https://example.com';
    });
});

it('lists participants for a date', function () {
    Http::fake([
        'my.demio.com/api/v1/date/456/participants' => Http::response([
            ['email' => 'jane@example.com'],
        ], 200),
    ]);

    expect(DemioClient::participants('456'))->toBe([
        ['email' => 'jane@example.com'],
    ]);
});

it('sends the Api-Key and Api-Secret headers', function () {
    Http::fake([
        'my.demio.com/api/v1/ping' => Http::response(['status' => 'ok'], 200),
    ]);

    DemioClient::ping();

    Http::assertSent(fn (Request $request) => $request->hasHeader('Api-Key', 'fake-key')
        && $request->hasHeader('Api-Secret', 'fake-secret'));
});

it('throws on a 401', function () {
    Http::fake([
        'my.demio.com/api/v1/ping' => Http::response('', 401),
    ]);

    expect(fn () => DemioClient::ping())->toThrow(DemioAuthenticationException::class);
});

it('returns null and logs when the request throws', function () {
    Log::spy();

    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    expect(DemioClient::ping())->toBeNull();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $message === 'DemioClient outbound fetch failed'
            && $context['context'] === 'demio_get')
        ->once();
});
