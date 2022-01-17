<?php

namespace eLife\Journal\Guzzle;

use eLife\Journal\Etoc\NewsLetter;
use eLife\Journal\Etoc\Subscription;
use eLife\Journal\Exception\CiviCrmResponseError;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Promise\all;

final class CiviCrmClient implements CiviCrmClientInterface
{
    // Assign all users to below group so we can easily identify them.
    const GROUP_JOURNAL_ETOC_SIGNUP = 'Journal_eToc_signup_1922';
    // Add the contact to the below group to trigger email with user preferences link.
    const GROUP_JOURNAL_ETOC_PREFERENCES = 'Journal_eToc_preferences_1923';
    // Custom field to store user preferences link to be included in emails.
    const FIELD_PREFERENCES_URL = 'custom_131';

    private $client;
    private $apiKey;
    private $siteKey;

    public function __construct(ClientInterface $client, string $apiKey, string $siteKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->siteKey = $siteKey;
    }

    private function storePreferencesUrl(int $contactId, string $preferencesUrl) : PromiseInterface
    {
        return $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
            'query' => [
                'entity' => 'Contact',
                'action' => 'create',
                'json' => [
                    'contact_id' => $contactId,
                    self::FIELD_PREFERENCES_URL => $preferencesUrl,
                ],
            ],
        ]))->then(function (Response $response) {
            return $this->prepareResponse($response);
        })->then(function ($data) {
            return ['contact_id' => $data['id']];
        });
    }

    public function subscribe(string $identifier, array $preferences, string $preferencesUrl, string $firstName = null, string $lastName = null, array $preferencesBefore = null) : PromiseInterface
    {
        return $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
            'query' => [
                'entity' => 'Contact',
                'action' => 'create',
                'json' => [
                    'contact_type' => 'Individual',
                    !is_null($preferencesBefore) ? 'contact_id' : 'email' => $identifier,
                    'first_name' => $firstName ?? '',
                    'last_name' => $lastName ?? '',
                    self::FIELD_PREFERENCES_URL => $preferencesUrl,
                    // Interpret submission as confirmation of desire to receive bulk emails.
                    'is_opt_out' => 0,
                ],
            ],
        ]))->then(function (Response $response) {
            return $this->prepareResponse($response);
        })->then(function ($data) {
            return $data['id'];
        })->then(function ($contactId) use ($preferences, $preferencesBefore) {
            $add = array_values(array_diff($preferences, $preferencesBefore ?? []));
            $remove = array_values(array_diff($preferencesBefore ?? [], $preferences));
            $unchanged = array_diff($preferencesBefore ?? [], $add, $remove);

            return all([
                'added' => !empty($add) ? $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
                    'query' => [
                        'entity' => 'GroupContact',
                        'action' => 'create',
                        'json' => [
                            'status' => 'Added',
                            'group_id' => $this->preferenceGroups($add, empty($preferencesBefore ?? [])),
                            'contact_id' => $contactId,
                        ],
                    ],
                ]))
                ->then(function (Response $response) {
                    return $this->prepareResponse($response);
                })
                ->then(function () use ($add) {
                    return $add;
                }) : [],
                'removed' => !empty($remove) ? $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
                    'query' => [
                        'entity' => 'GroupContact',
                        'action' => 'create',
                        'json' => [
                            'status' => 'Removed',
                            'group_id' => $this->preferenceGroups($remove, false),
                            'contact_id' => $contactId,
                        ],
                    ],
                ]))
                ->then(function (Response $response) {
                    return $this->prepareResponse($response);
                })
                ->then(function () use ($remove) {
                    return $remove;
                }) : [],
                'unchanged' => $unchanged,
            ])->then(function ($groups) use ($contactId) {
                return [
                    'contact_id' => $contactId,
                    'groups' => $groups,
                ];
            });
        });
    }

    public function checkSubscription(string $identifier, $isPreferencesId = false) : PromiseInterface
    {
        return $this->client->sendAsync($this->prepareRequest(), $this->options([
            'query' => [
                'entity' => 'Contact',
                'action' => 'get',
                'json' => [
                    $isPreferencesId ? self::FIELD_PREFERENCES_URL : 'email' => $identifier,
                    'return' => [
                        'group',
                        'first_name',
                        'last_name',
                        'email',
                        'is_opt_out',
                        self::FIELD_PREFERENCES_URL,
                    ],
                ],
            ],
        ]))->then(function (Response $response) {
            return $this->prepareResponse($response);
        })->then(function ($data) {
            if ($values = $data['values']) {
                $contactId = min(array_keys($values));
                $contact = $values[$contactId];

                return new Subscription(
                    (int) $contact['contact_id'],
                    ('1' === $contact['is_opt_out']),
                    $contact['email'],
                    $contact['first_name'],
                    $contact['last_name'],
                    explode(',', $contact['groups']),
                    $contact[self::FIELD_PREFERENCES_URL]
                );
            }
        });
    }

    public function triggerPreferencesEmail(int $contactId, string $preferencesUrl = null) : PromiseInterface
    {
        if ($preferencesUrl) {
            return self::storePreferencesUrl($contactId, $preferencesUrl)
                ->then(function ($data) {
                    return self::triggerPreferencesEmail($data['contact_id']);
                });
        }

        return $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
            'query' => [
                'entity' => 'GroupContact',
                'action' => 'create',
                'json' => [
                    'group_id' => [
                        self::GROUP_JOURNAL_ETOC_PREFERENCES,
                    ],
                    'contact_id' => $contactId,
                ],
            ],
        ]))->then(function (Response $response) {
            return $this->prepareResponse($response);
        })->then(function () use ($contactId) {
            return [
                'contact_id' => $contactId,
            ];
        });
    }

    private function preferenceGroups(array $preferences, $create = false) : array
    {
        $clean = array_map(function (NewsLetter $newsletter) {
            return $newsletter->group();
        }, $preferences);

        if ($create) {
            array_push($clean, self::GROUP_JOURNAL_ETOC_SIGNUP);
        }

        return array_values($clean);
    }

    private function prepareRequest(string $method = 'GET', array $headers = []) : Request
    {
        return new Request($method, '', $headers);
    }

    private function options(array $options = []) : array
    {
        $options['query'] = array_map(function ($param) {
            return is_array($param) ? json_encode($param) : $param;
        }, array_merge($options['query'] ?? [], array_filter(['api_key' => $this->apiKey, 'key' => $this->siteKey])));

        return $options;
    }

    /**
     * @param Response $response
     * @return mixed
     * @throws CiviCrmResponseError
     */
    private function prepareResponse(Response $response) : array
    {
        $body = json_decode($response->getBody()->getContents(), true);

        if (!empty($body['is_error'])) {
            throw new CiviCrmResponseError($body['error_message'], $response);
        }

        return $body;
    }
}
