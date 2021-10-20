<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\CiviCrmClient;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class MockCiviCrmClient
{
    public function subscribe(string $email, array $preferences, string $firstName = null, string $lastName = null) : PromiseInterface
    {
        $groupIds = CiviCrmClient::preferenceGroupIds($preferences);

        return promise_for([
            'contact_id' => '12345',
            'email' => $email,
            'subscribe' => array_combine($groupIds, array_fill(0, count($groupIds), true)),
        ]);
    }
}
