## Laravel Demio

### Overview

A lightweight Demio (my.demio.com) webinar REST API v1 client for Laravel. It
wraps `/ping`, `/events`, `/event/{id}`, `/event/{id}/date/{date_id}`,
`/event/register`, and `/date/{id}/participants` behind a small static
`DemioClient`, threading the `Api-Key`/`Api-Secret` headers and centralising
401 detection so callers get a thrown `DemioAuthenticationException` rather
than a silent null during a bad-credential window.

### Key Concepts

- **DemioClient**: Static client exposing the events, registration, and participant helpers
- **DemioAuthenticationException**: Thrown when Demio answers with a 401 (missing/invalid/revoked credentials)

### Public API

@verbatim
<code-snippet name="demio-client" lang="php">
use JeffersonGoncalves\Demio\DemioClient;

DemioClient::ping();                                   // array<string,mixed>|null
DemioClient::events();                                 // list<array<string,mixed>>
DemioClient::events('upcoming');                       // filtered by type: upcoming|past|all
DemioClient::event('event-id');                        // array<string,mixed>|null
DemioClient::eventDate('event-id', 'date-id');         // array<string,mixed>|null
DemioClient::register('event-id', 'Jane Doe', 'jane@example.com'); // array<string,mixed>|null
DemioClient::participants('date-id');                  // list<array<string,mixed>>
</code-snippet>
@endverbatim

### Authentication Handling

@verbatim
<code-snippet name="auth-exception" lang="php">
use JeffersonGoncalves\Demio\Exceptions\DemioAuthenticationException;

try {
    $events = DemioClient::events();
} catch (DemioAuthenticationException $e) {
    // Api-Key/Api-Secret is missing, wrong, or revoked.
}
</code-snippet>
@endverbatim

### Configuration

@verbatim
<code-snippet name="config-keys" lang="php">
// config/demio.php
'api_key'    => env('DEMIO_API_KEY'),
'api_secret' => env('DEMIO_API_SECRET'),
'base_url'   => env('DEMIO_BASE_URL', 'https://my.demio.com/api/v1'),
'timeout'    => (int) env('DEMIO_TIMEOUT', 8),
</code-snippet>
@endverbatim

### Conventions

- All methods are static — there is no facade or container binding
- Network/timeout/DNS failures are logged and surfaced as `null`/`[]`, never thrown — only a 401 throws
- 401 detection runs outside the network try/catch so the exception propagates to the caller
