<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class AboutPeopleControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_people_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Leadership team', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_shows_the_first_paragraph_of_aims_and_scope_for_a_subject()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/subject',
                ['Accept' => 'application/vnd.elife.subject+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject+json; version=1'],
                json_encode([
                    'id' => 'subject',
                    'name' => 'Subject',
                    'impactStatement' => 'Subject impact statement.',
                    'aimsAndScope' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Subject aims and scope.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'Subject aims and scope more.',
                        ],
                    ],
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/image',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/image',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockPeopleApi([['leadership', 'senior-editor'], 'reviewing-editor'], 'subject');

        $crawler = $client->request('GET', '/about/people/subject');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Subject aims and scope.', $crawler->filter('.content-header__impact-statement')->text());
        $this->assertSame('Subject aims and scope.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Subject aims and scope.', $crawler->filter('meta[name="description"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_shows_default_impact_statement_if_subject_without_aims_and_scope()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/subject',
                ['Accept' => 'application/vnd.elife.subject+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject+json; version=1'],
                json_encode([
                    'id' => 'subject',
                    'name' => 'Subject',
                    'impactStatement' => 'Subject impact statement.',
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/image',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/image',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockPeopleApi([['leadership', 'senior-editor'], 'reviewing-editor'], 'subject');

        $crawler = $client->request('GET', '/about/people/subject');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('eLife’s editors, early-career advisors, governing board, and executive staff work in concert to realise our mission.', $crawler->filter('.content-header__impact-statement')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Leadership team | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/people', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/people', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Leadership team', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife’s editors, early-career advisors, governing board, and executive staff work in concert to realise our mission.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife’s editors, early-career advisors, governing board, and executive staff work in concert to realise our mission.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('280', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('200', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    /**
     * @test
     */
    public function it_does_not_display_announcement_info_bar()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertEmpty($crawler->filter('.content-container .info-bar--announcement .info-bar__text'));
    }

    protected function getUrl() : string
    {
        $this->mockPeopleApi(['leadership', 'senior-editor']);

        return '/about/people';
    }

    protected function mockPeopleApi(array $types, $subject = null)
    {
        $subject_filter = ($subject) ? '&subject[]='.$subject : '';
        foreach ($types as $type) {
            $type = implode('', array_map(function (string $type) {
                return "&type[]={$type}";
            }, (array) $type));
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'http://api.elifesciences.org/people?page=1&per-page=1&order=asc'.$subject_filter.$type,
                    ['Accept' => 'application/vnd.elife.person-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                    ])
                )
            );
        }
    }
}
