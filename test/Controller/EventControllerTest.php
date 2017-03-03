<?php

namespace test\eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;
use Traversable;

final class EventControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_an_event_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Event title', $crawler->filter('.content-header__title')->text());
        $this->assertContains('Event text.', $crawler->filter('.wrapper')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Event title | Events | eLife', $crawler->filter('title')->text());
        $this->assertSame('/events/1/event-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/events/1/event-title', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Event title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_displays_a_message_if_the_event_has_finished()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/events/1',
                ['Accept' => 'application/vnd.elife.event+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.event+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Event title',
                    'published' => '2010-01-01T00:00:00Z',
                    'starts' => (new DateTimeImmutable('-2 days'))->format(ApiSdk::DATE_FORMAT),
                    'ends' => (new DateTimeImmutable('-1 day'))->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Event text.',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/events/1/event-title');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('This event has finished.', trim($crawler->filter('.info-bar--attention')->text()));
        $this->assertSame('noindex', $crawler->filter('head > meta[name="robots"]')->attr('content'));
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
        return $this->stringProvider('/events/1', '/events/1/foo');
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_event_is_not_found()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/events/1',
                [
                    'Accept' => 'application/vnd.elife.event+json; version=1',
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

        $client->request('GET', '/events/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/events/1',
                ['Accept' => 'application/vnd.elife.event+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.event+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Event title',
                    'published' => '2010-01-01T00:00:00Z',
                    'starts' => (new DateTimeImmutable('+1 day'))->format(ApiSdk::DATE_FORMAT),
                    'ends' => (new DateTimeImmutable('+2 days'))->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Event text.',
                        ],
                    ],
                ])
            )
        );

        return '/events/1/event-title';
    }
}
