<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SearchControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_search_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('0 results found', trim($crawler->filter('.message-bar')->text()));
    }

    /**
     * @test
     */
    public function it_has_a_search_box_in_the_header()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Search')->form();

        $form['for'] = 'keyword';

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=keyword&page=1&per-page=1&sort=relevance&order=desc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=keyword&page=1&per-page=6&sort=relevance&order=desc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $client->submit($form);

        $this->assertSame('/search?for=keyword', $client->getRequest()->getRequestUri());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_be_ordered_by_date()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=asc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=asc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $crawler = $client->click($crawler->selectLink('Date')->link());

        $this->assertSame('/search?for=&sort=date&order=ascending', $client->getRequest()->getRequestUri());
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $client->click($crawler->selectLink('Date')->link());

        $this->assertSame('/search?for=&sort=date&order=descending', $client->getRequest()->getRequestUri());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=relevance&order=desc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=relevance&order=desc',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        return '/search';
    }
}
