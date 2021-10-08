<?php

namespace eLife\Journal\Guzzle;

use GuzzleHttp\ClientInterface;
use function GuzzleHttp\Promise\coroutine;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class CiviCrmClient
{
    const LABEL_LATEST_ARTICLES = 'latest_articles';
    const LABEL_EARLY_CAREER = 'early_career';
    const LABEL_TECHNOLOGY = 'technology';
    const LABEL_ELIFE_NEWSLETTER = 'elife_newsletter';
    const GROUP_LATEST_ARTICLES = 'All_Content_53';
    const GROUP_EARLY_CAREER = 'early_careers_news_317';
    const GROUP_TECHNOLOGY = 'technology_news_435';
    const GROUP_ELIFE_NEWSLETTER = 'eLife_bi_monthly_news_1032';

    private $client;
    private $apiKey;
    private $siteKey;

    public function __construct(ClientInterface $client, string $apiKey, string $siteKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->siteKey = $siteKey;
    }

    public function subscribe(string $email, string $firstName, string $lastName, array $preferences)
    {
        return coroutine(function () use ($email, $firstName, $lastName, $preferences) {
            $contactId = (yield $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
                'query' => [
                    'entity' => 'Contact',
                    'action' => 'create',
                    'json' => $this->prepareJsonOptions([
                        'contact_type' => 'Individual',
                        'email' => $email,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]),
                ],
            ]))->then(function (Response $response) {
                return json_decode($response->getBody()->getContents(), true);
            })->then(function ($data) {
                return $data['id'];
            }));

            yield $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
                'query' => [
                    'entity' => 'GroupContact',
                    'action' => 'create',
                    'json' => $this->prepareJsonOptions([
                        'group_id' => $this->preferenceGroupIds($preferences),
                        'contact_id' => $contactId,
                    ]),
                ],
            ]))->then(function (Response $response) {
                return json_decode($response->getBody()->getContents(), true);
            })->then(function ($data) use ($contactId) {
                return [
                    'contact_id' => $contactId,
                    'groups_added' => 0 === $data['is_error'],
                ];
            });
        });
    }

    public function getUserFromEmail(string $email) : PromiseInterface
    {
        return $this->client->sendAsync($this->prepareRequest('GET'), $this->options(
            [
                'query' => [
                    'entity' => 'Contact',
                    'action' => 'get',
                    'json' => $this->prepareJsonOptions([
                        'email' => $email,
                    ]),
                ],
            ]
        ))->then(function (Response $response) {
            return json_decode($response->getBody()->getContents(), true);
        })->then(function ($data) {
            return array_keys($data['values'] ?? []);
        })->then(function ($users) {
            return min($users);
        });
    }

    public function getMsa() : PromiseInterface
    {
        return $this->client->sendAsync($this->prepareRequest('GET'), $this->options(
            [
                'query' => [
                    'entity' => 'Tag',
                    'action' => 'get',
                    'json' => $this->prepareJsonOptions([
                        'name' => [
                            'LIKE' => 'Interest - %',
                        ],
                    ]),
                ],
            ]
        ))->then(function (Response $response) {
            return json_decode($response->getBody()->getContents(), true);
        })->then(function ($data) {
            return array_map(function ($msa) {
                return preg_replace('/^Interest \- (.+)/', '$1', $msa['name']);
            }, $data['values'] ?? []);
        })->then(function ($msa) {
            sort($msa);

            return $msa;
        });
    }

    private function preferenceGroupIds(array $preferences)
    {
        return array_map(function ($preference) {
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
    }

    private function prepareRequest(string $method, array $headers = []) : Request
    {
        return new Request($method, '', $headers);
    }

    private function options(array $options = []) : array
    {
        $options['query'] = array_merge($options['query'] ?? [], ['api_key' => $this->apiKey, 'key' => $this->siteKey]);

        return $options;
    }

    private function prepareJsonOptions(array $options = [])
    {
        return json_encode($options);
    }
}
