<?php

namespace eLife\Journal\Guzzle;

use eLife\Journal\Etoc\EarlyCareer;
use eLife\Journal\Etoc\LatestArticles;
use eLife\Journal\Etoc\Subscription;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class MockCiviCrmClient implements CiviCrmClientInterface
{
    public function subscribe(string $identifier, array $preferences, string $preferencesUrl, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : PromiseInterface
    {
        return promise_for(array_filter($this->presetsSubscribe(
            $identifier,
            $preferences,
            $preferencesUrl,
            $firstName,
            $lastName,
            $preferencesBefore ?? []
        )));
    }

    private function presetsSubscribe(string $identifer, array $preferences, string $preferencesUrl, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : array
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
        return promise_for($this->presetsCheckSubscription($identifier, $isPreferencesId));
    }

    /**
     * @return Subscription|null
     */
    private function presetsCheckSubscription(string $identifier, $isPreferencesId = false)
    {
        if ($isPreferencesId) {
            $identifier = parse_url($identifier)['path'];
        }

        switch (true) {
            case '/content-alerts/green' === $identifier && $isPreferencesId:
            case 'green@example.com' === $identifier && !$isPreferencesId:
                return new Subscription(
                    12345,
                    false,
                    'green@example.com',
                    'Green',
                    'Example',
                    [LatestArticles::GROUP_ID],
                    'http://localhost/content-alerts/green'
                );
            case '/content-alerts/amber' === $identifier && $isPreferencesId:
            case 'amber@example.com' === $identifier && !$isPreferencesId:
                return new Subscription(
                    23456,
                    false,
                    'amber@example.com',
                    'Amber',
                    'Example',
                    []
                );
            case '/content-alerts/red' === $identifier && $isPreferencesId:
            case 'red@example.com' === $identifier && !$isPreferencesId:
                return new Subscription(
                    34567,
                    true,
                    'red@example.com',
                    'Red',
                    'Example',
                    [LatestArticles::GROUP_ID, EarlyCareer::GROUP_ID],
                    'http://localhost/content-alerts/red'
                );
            default:
                return null;
        }
    }

    public function triggerPreferencesEmail(int $contactId, string $preferencesUrl = null) : PromiseInterface
    {
        return promise_for($this->presetsTriggerPreferencesEmail($contactId));
    }

    private function presetsTriggerPreferencesEmail(int $contactId) : array
    {
        switch ($contactId) {
            default:
                return [
                    'contact_id' => $contactId,
                ];
        }
    }
}
