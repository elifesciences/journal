<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Etoc\EarlyCareer;
use eLife\Journal\Etoc\LatestArticles;
use eLife\Journal\Etoc\Newsletter;
use eLife\Journal\Etoc\Subscription;
use eLife\Journal\Guzzle\CiviCrmClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class MockCiviCrmClient implements CiviCrmClientInterface
{
    public function unsubscribe(int $contactId, array $groups) : PromiseInterface
    {
        return promise_for($this->presetsUnsubscribe($contactId, $groups));
    }

    private function presetsUnsubscribe(int $contactId, array $groups) : array
    {
        return [];
    }

    public function subscribe(string $identifier, array $preferences, string $preferencesUrl, array $newsletters, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : PromiseInterface
    {
        return promise_for(array_filter($this->presetsSubscribe(
            $identifier,
            $preferences,
            $preferencesUrl,
            $newsletters,
            $firstName,
            $lastName,
            $preferencesBefore ?? []
        )));
    }

    private function presetsSubscribe(string $identifer, array $preferences, string $preferencesUrl, array $newsletters, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : array
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

    public function checkSubscription(string $identifier, bool $isEmail = true, Newsletter $newsletter = null) : PromiseInterface
    {
        return promise_for($this->presetsCheckSubscription($identifier, $isEmail));
    }

    /**
     * @return Subscription|null
     */
    private function presetsCheckSubscription(string $identifier, bool $isEmail = true, Newsletter $newsletter = null)
    {
        if (!$isEmail) {
            $identifier = parse_url($identifier)['path'];
        }

        switch (true) {
            case '/content-alerts/green' === $identifier && !$isEmail:
            case '/content-alerts/unsubscribe/green' === $identifier && !$isEmail:
            case 'green@example.com' === $identifier && $isEmail:
                return new Subscription(
                    12345,
                    false,
                    'green@example.com',
                    'Green',
                    'Example',
                    [LatestArticles::GROUP_ID],
                    'http://localhost/content-alerts/green'
                );
            case '/content-alerts/amber' === $identifier && !$isEmail:
            case '/content-alerts/unsubscribe/amber' === $identifier && !$isEmail:
            case 'amber@example.com' === $identifier && $isEmail:
                return new Subscription(
                    23456,
                    false,
                    'amber@example.com',
                    'Amber',
                    'Example',
                    []
                );
            case '/content-alerts/red' === $identifier && !$isEmail:
            case '/content-alerts/unsubscribe/red' === $identifier && !$isEmail:
            case 'red@example.com' === $identifier && $isEmail:
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
