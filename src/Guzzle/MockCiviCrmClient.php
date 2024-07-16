<?php

namespace eLife\Journal\Guzzle;

use eLife\CiviContacts\Etoc\EarlyCareer;
use eLife\CiviContacts\Etoc\ElifeNewsletter;
use eLife\CiviContacts\Etoc\LatestArticles;
use eLife\CiviContacts\Etoc\Newsletter;
use eLife\CiviContacts\Etoc\Subscription;
use eLife\CiviContacts\Guzzle\CiviCrmClientInterface;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;

final class MockCiviCrmClient implements CiviCrmClientInterface
{
    public function optout(int $contactId, array $reasons, string $reasonOther = null) : PromiseInterface
    {
        return Create::promiseFor($this->presetsOptout($contactId, $reasons, $reasonOther));
    }

    private function presetsOptout(int $contactId, array $reasons, string $reasonOther = null) : array
    {
        return [];
    }

    public function unsubscribe(int $contactId, array $groups) : PromiseInterface
    {
        return Create::promiseFor($this->presetsUnsubscribe($contactId, $groups));
    }

    private function presetsUnsubscribe(int $contactId, array $groups) : array
    {
        return [];
    }

    public function subscribe(string $identifier, array $preferences, array $newsletters, string $preferencesUrl, string $unsubscribeUrl = null, string $optoutUrl = null, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : PromiseInterface
    {
        return Create::promiseFor(array_filter($this->presetsSubscribe(
            $identifier,
            $preferences,
            $newsletters,
            $preferencesUrl,
            $unsubscribeUrl,
            $optoutUrl,
            $firstName,
            $lastName,
            $preferencesBefore ?? []
        )));
    }

    private function presetsSubscribe(string $identifer, array $preferences, array $newsletters, string $preferencesUrl, string $unsubscribeUrl = null, string $optoutUrl = null, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : array
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

    public function checkSubscription(string $identifier, bool $isEmail = true, Newsletter $newsletter = null, string $field = null) : PromiseInterface
    {
        return Create::promiseFor($this->presetsCheckSubscription($identifier, $isEmail));
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
            case strpos($identifier, '/content-alerts/unsubscribe/green') !== false && !$isEmail:
            case '/content-alerts/optout/green' === $identifier && !$isEmail:
            case 'green@example.com' === $identifier && $isEmail:
                return new Subscription(
                    12345,
                    false,
                    'green@example.com',
                    'Green',
                    'Example',
                    [LatestArticles::GROUP_ID, ElifeNewsletter::GROUP_ID],
                    'http://localhost/content-alerts/green'
                );
            case '/content-alerts/amber' === $identifier && !$isEmail:
            case strpos($identifier, '/content-alerts/unsubscribe/amber') !== false && !$isEmail:
            case '/content-alerts/optout/amber' === $identifier && !$isEmail:
            case 'amber@example.com' === $identifier && $isEmail:
                return new Subscription(
                    23456,
                    false,
                    'amber@example.com',
                    'Amber',
                    'Example',
                    [EarlyCareer::GROUP_ID]
                );
            case '/content-alerts/red' === $identifier && !$isEmail:
            case strpos($identifier, '/content-alerts/unsubscribe/red') !== false && !$isEmail:
            case '/content-alerts/optout/red' === $identifier && !$isEmail:
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
        return Create::promiseFor($this->presetsTriggerPreferencesEmail($contactId, $preferencesUrl));
    }

    private function presetsTriggerPreferencesEmail(int $contactId, string $preferencesUrl = null) : array
    {
        switch ($contactId) {
            default:
                return [
                    'contact_id' => $contactId,
                ];
        }
    }

    public function triggerUnsubscribeEmail(int $contactId) : PromiseInterface
    {
        return Create::promiseFor($this->presetsTriggerUnsubscribeEmail($contactId));
    }

    private function presetsTriggerUnsubscribeEmail(int $contactId) : array
    {
        switch ($contactId) {
            default:
                return [
                    'contact_id' => $contactId,
                ];
        }
    }

    public function storeSubscriberUrls(Subscription $subscription) : PromiseInterface
    {
        return Create::promiseFor($this->presetsStoreSubscriberUrls($subscription));
    }

    private function presetsStoreSubscriberUrls(Subscription $subscription) : Subscription
    {
        return $subscription;
    }

    public function getAllSubscribers(int $total = 0, int $batchSize = 100, int $offset = 0) : array
    {
        return array_map(function ($i) {
            return new Subscription($i);
        }, range(1, $total));
    }
}
