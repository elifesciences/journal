<?php

namespace test\eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;
use Traversable;

final class JobAdvertControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_the_job_advert_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Job advert title', $crawler->filter('.content-header__title')->text());
        $this->assertContains('Closing date for applications is '.date('F j, Y', strtotime('+1 day')).'.', $crawler->filter('main > div.wrapper')->text());
        $this->assertContains('Job advert text.', $crawler->filter('main > div.wrapper')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Job advert title | Jobs | eLife', $crawler->filter('title')->text());
        $this->assertSame('/jobs/1/job-advert-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/jobs/1/job-advert-title', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Job advert title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('job-advert/1', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertSame('elifesciences.org', $crawler->filter('meta[name="dc.relation.ispartof"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_displays_a_message_if_the_job_advert_has_finished()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/job-adverts/1',
                ['Accept' => 'application/vnd.elife.job-advert+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.job-advert+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Job advert title',
                    'published' => '2010-01-01T00:00:00Z',
                    'closingDate' => (new DateTimeImmutable('-1 day'))->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Job advert text.',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/jobs/1/job-advert-title');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('This position is now closed to applications.', trim($crawler->filter('main > div.wrapper')->text()));
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
        return $this->stringProvider('/jobs/1', '/jobs/1/foo');
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_job_advert_is_not_found()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/job-adverts/1',
                ['Accept' => 'application/vnd.elife.job-advert+json; version=1']
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

        $client->request('GET', '/jobs/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/job-adverts/1',
                ['Accept' => 'application/vnd.elife.job-advert+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.job-advert+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Job advert title',
                    'published' => '2010-01-01T00:00:00Z',
                    'closingDate' => (new DateTimeImmutable('+1 day'))->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Job advert text.',
                        ],
                    ],
                ])
            )
        );

        return '/jobs/1/job-advert-title';
    }
}
