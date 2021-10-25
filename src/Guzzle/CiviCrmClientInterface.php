<?php

namespace eLife\Journal\Guzzle;

use GuzzleHttp\Promise\PromiseInterface;

interface CiviCrmClientInterface
{
    public function subscribe(string $email, array $preferences, string $firstName = null, string $lastName = null) : PromiseInterface;
}
