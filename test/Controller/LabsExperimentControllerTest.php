<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class LabsExperimentControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_a_labs_experiment_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Experiment title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Experiment: 001 Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
        $this->assertContains('Experiment text.', $crawler->filter('.wrapper')->text());
    }

    /**
     * @test
     */
    public function it_requires_all_the_fields_to_be_completed()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $crawler = $client->submit($crawler->selectButton('Submit')->form());

        $this->assertCount(3, $crawler->filter('.info-bar'));
        $this->assertSame('Please provide your name.', trim($crawler->filter('.info-bar')->eq(0)->text()));
        $this->assertSame('Please provide your email address.', trim($crawler->filter('.info-bar')->eq(1)->text()));
        $this->assertSame('Please let us know your comment.', trim($crawler->filter('.info-bar')->eq(2)->text()));
    }

    /**
     * @test
     */
    public function it_requires_a_valid_email()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Submit')->form();
        $form['labs_experiment_feedback[name]'] = 'My name';
        $form['labs_experiment_feedback[email]'] = 'foo';
        $form['labs_experiment_feedback[comment]'] = 'My question';

        $crawler = $client->submit($form);

        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertSame('Please provide a valid email address.', trim($crawler->filter('.info-bar')->text()));
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_experiment_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-experiments/1',
                [
                    'Accept' => 'application/vnd.elife.labs-experiment+json; version=1',
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

        $client->request('GET', '/labs/experiment1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-experiments/1',
                ['Accept' => 'application/vnd.elife.labs-experiment+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.labs-experiment+json; version=1'],
                json_encode([
                    'number' => 1,
                    'title' => 'Experiment title',
                    'published' => '2010-01-01T00:00:00Z',
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
                    'impactStatement' => 'Experiment impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Experiment text.',
                        ],
                    ],
                ])
            )
        );

        return '/labs/experiment1';
    }
}
