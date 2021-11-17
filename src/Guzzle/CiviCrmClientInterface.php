<?php

namespace eLife\Journal\Guzzle;

use GuzzleHttp\Promise\PromiseInterface;

interface CiviCrmClientInterface
{
    public function subscribe(string $email, array $preferences, string $preferencesUrl, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : PromiseInterface;

    public function checkSubscription(string $identifier, $isPreferencesId = false) : PromiseInterface;

    public function triggerPreferencesEmail(int $contactId, string $preferencesUrl = null) : PromiseInterface;
}
