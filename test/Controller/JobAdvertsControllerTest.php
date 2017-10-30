<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class JobAdvertsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_job_adverts_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife Jobs', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No vacancies at present.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('eLife Jobs | eLife', $crawler->filter('title')->text());
        $this->assertSame('/jobs', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/jobs', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('eLife Jobs', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    /**
     * @test
     * @dataProvider invalidPageProvider
     */
    public function it_displays_a_404_when_not_on_a_valid_page($page, callable $callable = null)
    {
        $client = static::createClient();

        if ($callable) {
            $callable();
        }

        $client->request('GET', '/jobs?page='.$page);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidPageProvider() : Traversable
    {
        foreach (['-1', '0', 'foo'] as $page) {
            yield 'page '.$page => [$page];
        }

        foreach (['2'] as $page) {
            yield 'page '.$page => [
                $page,
                function () use ($page) {
                    $this->mockApiResponse(
                        new Request(
                            'GET',
                            'http://api.elifesciences.org/job-adverts?page=1&per-page=1&show=open&order=desc',
                            ['Accept' => 'application/vnd.elife.job-advert-list+json; version=1']
                        ),
                        new Response(
                            404,
                            ['Content-Type' => 'application/problem+json'],
                            json_encode(['title' => 'Not found'])
                        )
                    );
                },
            ];
        }
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/job-adverts?page=1&per-page=10&show=open&order=desc',
                ['Accept' => 'application/vnd.elife.job-advert-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.job-advert-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/jobs';
    }
}
