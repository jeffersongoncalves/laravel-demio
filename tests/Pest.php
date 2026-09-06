<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Demio\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => Http::preventStrayRequests())
    ->in('Feature');
