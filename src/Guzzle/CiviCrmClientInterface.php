<?php

namespace eLife\Journal\Guzzle;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;

interface CiviCrmClientInterface
{
    public function __construct(ClientInterface $client, string $apiKey, string $siteKey);

    public function subscribe(string $email, array $preferences, string $firstName = null, string $lastName = null) : PromiseInterface;
}
