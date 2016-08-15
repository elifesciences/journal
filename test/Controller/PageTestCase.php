<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\WebTestCase;

abstract class PageTestCase extends WebTestCase
{
    /**
     * @test
     */
    final public function it_has_the_header()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $header = $crawler->filter('header.site-header');

        $this->assertCount(1, $header);
    }

    /**
     * @test
     */
    final public function it_has_the_footer()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $footer = $crawler->filter('footer.site-footer');

        $this->assertCount(1, $footer);
    }

    final protected static function createClient(array $options = [], array $server = [])
    {
        static::bootKernel($options);

        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page=1&per-page=50&order=asc',
                [
                    'Accept' => 'application/vnd.elife.subject-list+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.subject-list+json; version=1',
                ],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'id' => 'subject',
                            'name' => 'Subject',
                            'impactStatement' => 'Subject impact statement.',
                            'image' => [
                                'alt' => '',
                                'sizes' => [
                                    '2:1' => [
                                        900 => 'https://placehold.it/900x450',
                                        1800 => 'https://placehold.it/1800x900',
                                    ],
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
                    ],
                ])
            )
        );

        return $client;
    }

    abstract protected function getUrl() : string;
}
