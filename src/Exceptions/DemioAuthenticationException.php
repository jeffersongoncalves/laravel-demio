<?php

namespace JeffersonGoncalves\Demio\Exceptions;

use RuntimeException;

/**
 * Raised when Demio answers a call with a 401 — the Api-Key/Api-Secret pair
 * is missing, wrong, or revoked. Thrown instead of returning a silent
 * null/[] so a misconfigured credential doesn't look like "no data".
 */
class DemioAuthenticationException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Demio API authentication failed. Check the DEMIO_API_KEY and DEMIO_API_SECRET credentials.');
    }
}
