<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\CiviCrmClient;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class MockCiviCrmClient
{
    public function subscribe(string $identifier, array $preferences, string $firstName = null, string $lastName = null, array $preferencesBefore = []) : PromiseInterface
    {
        return promise_for(array_filter($this->subscribePresets(
            $identifier,
            $preferences,
            $firstName,
            $lastName,
            $preferencesBefore
        )));
    }

    private function subscribePresets(string $identifer, array $preferences, string $firstName = null, string $lastName = null, array $preferencesBefore = []) : array
    {
        $add = array_values(array_diff($preferences, $preferencesBefore));
        $remove = array_values(array_diff($preferencesBefore, $preferences));
        $unchanged = array_diff($preferencesBefore, $add, $remove);

        $groups = [
            'added' => $add,
            'removed' => $remove,
            'unchanged' => $unchanged,
        ];

        switch ($identifer) {
            default:
                return [
                    'contact_id' => '12345',
                    'groups' => $groups,
                ];
        }
    }

    public function checkSubscription(string $identifier, $isPreferencesId = false) : PromiseInterface
    {
        return promise_for($this->checkSubscriptionPresets($identifier, $isPreferencesId));
    }

    /**
     * @return array|null
     */
    private function checkSubscriptionPresets(string $identifier, $isPreferencesId = false)
    {
        switch ($identifier) {
            case 'http://localhost/content-alerts/green':
                $preferences = [CiviCrmClient::LABEL_LATEST_ARTICLES];
                return [
                    'contact_id' => 12345,
                    'email' => 'green@example.com',
                    'first_name' => 'Green',
                    'last_name' => 'Example',
                    'preferences' => $preferences,
                    'groups' => implode(',', $preferences),
                ];
            default:
                return null;
        }
    }
}
