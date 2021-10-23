<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\CiviCrmClient;
use eLife\Journal\Guzzle\CiviCrmClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\promise_for;

final class MockCiviCrmClient implements CiviCrmClientInterface
{
    public function subscribe(string $identifier, array $preferences, string $preferencesUrl, string $firstName = null, string $lastName = null, array $preferencesBefore = []) : PromiseInterface
    {
        return promise_for(array_filter($this->presetsSubscribe(
            $identifier,
            $preferences,
            $preferencesUrl,
            $firstName,
            $lastName,
            $preferencesBefore
        )));
    }

    private function presetsSubscribe(string $identifer, array $preferences, string $preferencesUrl, string $firstName = null, string $lastName = null, array $preferencesBefore = []) : array
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
     * @return array|null
     */
    private function presetsCheckSubscription(string $identifier, $isPreferencesId = false)
    {
        switch (true) {
            case 'http://localhost/content-alerts/green' === $identifier && $isPreferencesId:
            case 'green@example.com' === $identifier && !$isPreferencesId:
                $preferences = [CiviCrmClient::LABEL_LATEST_ARTICLES];
                return [
                    'contact_id' => 12345,
                    'email' => 'green@example.com',
                    'first_name' => 'Green',
                    'last_name' => 'Example',
                    'preferences' => $preferences,
                    'groups' => implode(',', $preferences),
                    CiviCrmClient::FIELD_PREFERENCES_URL => 'http://localhost/content-alerts/green',
                ];
            default:
                return null;
        }
    }

    public function triggerPreferencesEmail(int $contactId) : PromiseInterface
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

    public function storePreferencesUrl(int $contactId, string $preferencesUrl) : PromiseInterface
    {
        return promise_for($this->presetsStorePreferencesUrl($contactId, $preferencesUrl));
    }

    public function presetsStorePreferencesUrl(int $contactId, string $preferencesUrl) : PromiseInterface
    {
        switch ($contactId) {
            default:
                return promise_for([
                    'contact_id' => $contactId,
                ]);
        }
    }
}
