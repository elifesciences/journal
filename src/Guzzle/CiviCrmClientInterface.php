<?php

namespace eLife\Journal\Guzzle;

use GuzzleHttp\Promise\PromiseInterface;

interface CiviCrmClientInterface
{
    public function subscribe(string $email, array $preferences, string $preferencesUrl, string $firstName = null, string $lastName = null) : PromiseInterface;

    public function checkSubscription(string $identifier, $isPreferencesId = false) : PromiseInterface;

    public function triggerPreferencesEmail(int $contactId) : PromiseInterface;

    public function storePreferencesUrl(int $contactId, string $preferencesUrl) : PromiseInterface
}
