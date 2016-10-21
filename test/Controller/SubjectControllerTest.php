<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SubjectControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_a_subject_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Subject', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Subject impact statement.', trim($crawler->filter('main .lead-paras')->text()));
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_subject_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/subject',
                [
                    'Accept' => 'application/vnd.elife.subject+json; version=1',
                ]
            ),
            new Response(
                404,
                [
                    'Content-Type' => 'application/problem+json',
                ],
                json_encode([
                    'title' => 'Not found',
                ])
            )
        );

        $client->request('GET', '/subjects/subject');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/subject',
                [
                    'Accept' => 'application/vnd.elife.subject+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.subject+json; version=1',
                ],
                json_encode([
                    'id' => 'subject',
                    'name' => 'Subject',
                    'impactStatement' => 'Subject impact statement.',
                    'image' => [
                        'banner' => [
                            'alt' => '',
                            'sizes' => [
                                '2:1' => [
                                    900 => 'https://placehold.it/900x450',
                                    1800 => 'https://placehold.it/1800x900',
                                ],
                            ],

                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'sizes' => [
                                '16:9' => [
                                    250 => 'https://placehold.it/250x141',
                                    500 => 'https://placehold.it/500x281',
                                ],
                                '1:1' => [
                                    70 => 'https://placehold.it/70x70',
                                    140 => 'https://placehold.it/140x140',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&subject[]=subject&type[]=research-article&type[]=research-advance&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection',
                [
                    'Accept' => 'application/vnd.elife.search+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.search+json; version=1',
                ],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Subject',
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
                        'event' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        return '/subjects/subject';
    }
}
