<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class InterviewControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_interview_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Interview title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('An interview with Interviewee', $crawler->filter('.content-header__strapline')->text());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_interview_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews/1',
                [
                    'Accept' => 'application/vnd.elife.interview+json; version=1',
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

        $client->request('GET', '/interviews/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews/1',
                ['Accept' => 'application/vnd.elife.interview+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.interview+json; version=1'],
                json_encode([
                    'id' => '1',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'Interviewee',
                            'index' => 'Interviewee',
                        ],
                        'cv' => [
                            [
                                'date' => '2013 – Present',
                                'text' => 'Somewhere',
                            ],
                        ],
                    ],
                    'title' => 'Interview title',
                    'published' => '2010-01-01T00:00:00Z',
                    'impactStatement' => 'Interview impact statement',
                    'content' => [
                        [
                            'type' => 'question',
                            'question' => 'Question?',
                            'answer' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Answer.',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        return '/interviews/1';
    }
}
