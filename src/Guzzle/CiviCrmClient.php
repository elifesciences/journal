<?php

namespace eLife\Journal\Guzzle;

use eLife\Journal\Exception\CiviCrmResponseError;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

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
    // Assign all users to below group so we can easily identify them.
    const GROUP_JOURNAL_ETOC_SIGNUP = 'Journal_eToc_signup_1922';

    private $client;
    private $apiKey;
    private $siteKey;

    public function __construct(ClientInterface $client, string $apiKey, string $siteKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->siteKey = $siteKey;
    }

    public function subscribe(string $email, array $preferences, string $firstName = null, string $lastName = null) : PromiseInterface
    {
        return $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
            'query' => [
                'entity' => 'Contact',
                'action' => 'create',
                'json' => array_filter([
                    'contact_type' => 'Individual',
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]),
            ],
        ]))->then(function (Response $response) {
            return $this->prepareResponse($response);
        })->then(function ($data) {
            return $data['id'];
        })->then(function ($contactId) use ($preferences) {
            if (empty($preferences)) {
                return [
                    'contact_id' => $contactId,
                ];
            }

            return $this->client->sendAsync($this->prepareRequest('POST'), $this->options([
                'query' => [
                    'entity' => 'GroupContact',
                    'action' => 'create',
                    'json' => [
                        'group_id' => $this->preferenceGroupIds($preferences),
                        'contact_id' => $contactId,
                    ],
                ],
            ]))->then(function (Response $response) {
                return $this->prepareResponse($response);
            })->then(function ($data) use ($contactId) {
                return [
                    'contact_id' => $contactId,
                    'groups_added' => 0 === $data['is_error'],
                ];
            });
        });
    }

    private function preferenceGroupIds(array $preferences) : array
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

        array_push($clean, self::GROUP_JOURNAL_ETOC_SIGNUP);

        return $clean;
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
