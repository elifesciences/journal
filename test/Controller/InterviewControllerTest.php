<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;
use Traversable;

final class InterviewControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_an_interview_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Interview title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Interview Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
        $this->assertSame('An interview with Interviewee', $crawler->filter('.content-header__strapline')->text());
    }

    /**
     * @test
     * @dataProvider incorrectSlugProvider
     */
    public function it_redirects_if_the_slug_is_not_correct(string $url)
    {
        $client = static::createClient();

        $expectedUrl = $this->getUrl();

        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect($expectedUrl));
    }

    public function incorrectSlugProvider() : Traversable
    {
        return $this->stringProvider('/interviews/1', '/interviews/1/foo');
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

    /**
     * @test
     */
    public function it_displays_content()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Question?',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(1) > header > h3')->text());
        $this->assertSame('Answer.',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(1) > div > p')->text());
        $this->assertSame('Interviewee CV',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(2) > header > h2')->text());
        $this->assertSame('2013 – Present: Somewhere',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(2) > div > ol > li')->text());
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

        return '/interviews/1/interviewee';
    }
}
