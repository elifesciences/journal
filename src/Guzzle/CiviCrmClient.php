<?php

namespace eLife\Journal\Guzzle;

use eLife\Journal\Exception\CiviCrmResponseError;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Promise\all;

final class CiviCrmClient implements CiviCrmClientInterface
{
    const LABEL_LATEST_ARTICLES = 'latest_articles';
    const LABEL_EARLY_CAREER = 'early_career';
    const LABEL_TECHNOLOGY = 'technology';
    const LABEL_ELIFE_NEWSLETTER = 'elife_newsletter';
    // Perhaps make the constants below configuration.
    const GROUP_LATEST_ARTICLES = 'All_Content_53';
    const GROUP_EARLY_CAREER = 'early_careers_news_317';
    const GROUP_TECHNOLOGY = 'technology_news_435';
    const GROUP_ELIFE_NEWSLETTER = 'eLife_bi_monthly_news_1032';
    const GROUP_ID_LATEST_ARTICLES = 53;
    const GROUP_ID_EARLY_CAREER = 317;
    const GROUP_ID_TECHNOLOGY = 435;
    const GROUP_ID_ELIFE_NEWSLETTER = 1032;
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
                ],
            ],
        ]))->then(function (Response $response) {
            return $this->prepareResponse($response);
        })->then(function ($data) {
            return $data['id'];
        })->then(function ($contactId) use ($preferences, $preferencesBefore) {
            $add = array_values(array_diff($preferences, $preferencesBefore));
            $remove = array_values(array_diff($preferencesBefore, $preferences));
            $unchanged = array_diff($preferencesBefore, $add, $remove);

            return all([
                'added' => !empty($add) ? $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
                    'query' => [
                        'entity' => 'GroupContact',
                        'action' => 'create',
                        'json' => [
                            'status' => 'Added',
                            'group_id' => $this->preferenceGroups($add, empty($preferencesBefore)),
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

                $preferences = $this->preferenceGroupLabels(explode(',', $contact['groups']));

                return [
                    'contact_id' => (int) $contact['contact_id'],
                    'email' => $contact['email'],
                    'first_name' => $contact['first_name'],
                    'last_name' => $contact['last_name'],
                    'preferences' => $preferences,
                    'groups' => implode(',', $preferences),
                    'preferences_url' => $contact[self::FIELD_PREFERENCES_URL],
                ];
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
        $clean = array_map(function ($preference) {
            switch ($preference) {
                case self::LABEL_LATEST_ARTICLES:
                    return self::GROUP_LATEST_ARTICLES;
                case self::LABEL_EARLY_CAREER:
                    return self::GROUP_EARLY_CAREER;
                case self::LABEL_TECHNOLOGY:
                    return self::GROUP_TECHNOLOGY;
                case self::LABEL_ELIFE_NEWSLETTER:
                    return self::GROUP_ELIFE_NEWSLETTER;
                default:
                    return null;
            }
        }, array_intersect([
            self::LABEL_LATEST_ARTICLES,
            self::LABEL_EARLY_CAREER,
            self::LABEL_TECHNOLOGY,
            self::LABEL_ELIFE_NEWSLETTER,
        ], $preferences));

        if ($create) {
            array_push($clean, self::GROUP_JOURNAL_ETOC_SIGNUP);
        }

        return array_values($clean);
    }

    private function preferenceGroupLabels(array $groupIds) : array
    {
        return array_values(array_map(function ($groupId) {
            switch ($groupId) {
                case self::GROUP_ID_LATEST_ARTICLES:
                    return self::LABEL_LATEST_ARTICLES;
                case self::GROUP_ID_EARLY_CAREER:
                    return self::LABEL_EARLY_CAREER;
                case self::GROUP_ID_TECHNOLOGY:
                    return self::LABEL_TECHNOLOGY;
                case self::GROUP_ID_ELIFE_NEWSLETTER:
                    return self::LABEL_ELIFE_NEWSLETTER;
                default:
                    return null;
            }
        }, array_intersect([
            self::GROUP_ID_LATEST_ARTICLES,
            self::GROUP_ID_EARLY_CAREER,
            self::GROUP_ID_TECHNOLOGY,
            self::GROUP_ID_ELIFE_NEWSLETTER,
        ], $groupIds)));
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
