<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\BrowserKit\Client;

final class ProfileControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_a_404_if_the_feature_flag_is_disabled()
    {
        $client = static::createClient();
        $client->getCookieJar()->clear();

        $client->request('GET', $this->getUrl());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_a_profile_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Preferred Name', $crawler->filter('.content-header-profile__display_name')->text());
        $this->assertEmpty($crawler->filter('.content-header-profile__details'));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $client->request('GET', '/?open-sesame');

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Preferred Name | Profiles | eLife', $crawler->filter('title')->text());
        $this->assertSame('/profiles/1', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/profiles/1', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Preferred Name', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_has_cache_headers()
    {
        $client = static::createClient();

        $client->request('GET', '/?open-sesame');

        $client->request('GET', $this->getUrl());

        $this->assertSame('no-cache, private', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertEmpty($client->getResponse()->getVary());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_profile_is_not_found()
    {
        $client = static::createClient();

        $client->request('GET', '/?open-sesame');

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/profiles/1',
                [
                    'Accept' => 'application/vnd.elife.profile+json; version=1',
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

        $client->request('GET', '/profiles/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/profiles/1',
                ['Accept' => 'application/vnd.elife.profile+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.profile+json; version=1'],
                json_encode([
                    'id' => '1',
                    'name' => [
                        'preferred' => 'Preferred Name',
                        'index' => 'Index Name',
                    ],
                ])
            )
        );

        return '/profiles/1';
    }

    protected static function onCreateClient(Client $client)
    {
        $client->request('GET', '/?open-sesame');
    }
}
