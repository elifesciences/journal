<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\ApiFakerAwareTestCase;

final class LabsExperimentControllerTest extends PageTestCase
{
    use ApiFakerAwareTestCase;

    /**
     * @test
     */
    public function it_displays_a_labs_experiment_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Experiment: 001', $crawler->filter('.meta__type')->text());
        $this->assertNotEquals('', trim($crawler->filter('.wrapper')->text()));
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
                json_encode($this->faker->labsExperimentV1(1))
            )
        );

        return '/labs/experiment1';
    }
}
