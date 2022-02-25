<?php

namespace eLife\Journal\Guzzle;

use eLife\Journal\Etoc\Newsletter;
use GuzzleHttp\Promise\PromiseInterface;

interface CiviCrmClientInterface
{
    public function subscribe(string $email, array $preferences, array $newsletters, string $preferencesUrl, string $unsubscribeUrl = null, string $optoutUrl = null, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : PromiseInterface;

    public function unsubscribe(int $contactId, array $groups) : PromiseInterface;

    public function optout(int $contactId, array $reasons, string $reasonOther = null) : PromiseInterface;

    public function checkSubscription(string $identifier, bool $isEmail = true, Newsletter $newsletter = null, string $field = null) : PromiseInterface;

    public function triggerPreferencesEmail(int $contactId, string $preferencesUrl = null) : PromiseInterface;
}
