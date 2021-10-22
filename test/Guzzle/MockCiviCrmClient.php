<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\CiviCrmClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class MockCiviCrmClient implements CiviCrmClientInterface
{
    public function subscribe(string $email, array $preferences, string $firstName = null, string $lastName = null) : PromiseInterface
    {
        return promise_for(array_filter([
            'contact_id' => '12345',
            'groups_added' => true,
        ]));
    }
}
