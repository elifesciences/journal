<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ProfileControllerTest extends PageTestCase
{
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
        $this->assertContains('No annotations available.', $crawler->text());
    }

    /**
     * @test
     */
    public function it_displays_public_annotations_when_it_is_not_your_profile_page()
    {
        $client = static::createClient();

        $this->logIn($client);

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No annotations available.', $crawler->text());
    }

    /**
     * @test
     */
    public function it_displays_a_profile_page_with_public_information()
    {
        $client = static::createClient();

        $url = $this->getUrl();

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
                    'emailAddresses' => [
                        [
                            'value' => 'j.carberry@restricted.example.com',
                            'access' => 'restricted',
                        ],
                        [
                            'value' => 'j.carberry@orcid.org',
                            'access' => 'public',
                        ],
                        [
                            'value' => 'j.carberry2@orcid.org',
                            'access' => 'public',
                        ],
                    ],
                    'affiliations' => [
                        [
                            'value' => [
                                'name' => [
                                    'Department 1',
                                    'University 1',
                                ],
                                'address' => [
                                    'formatted' => [
                                        'Middletown',
                                        'CT',
                                        'United States',
                                    ],
                                    'components' => [
                                        'locality' => [
                                            'Middletown',
                                        ],
                                        'area' => [
                                            'CT',
                                        ],
                                        'country' => 'United States',
                                    ],
                                ],
                            ],
                            'access' => 'public',
                        ],
                        [
                            'value' => [
                                'name' => [
                                    'Department 2',
                                    'University 2',
                                ],
                                'address' => [
                                    'formatted' => [
                                        'Middletown',
                                        'CT',
                                        'United States',
                                    ],
                                    'components' => [
                                        'locality' => [
                                            'Middletown',
                                        ],
                                        'area' => [
                                            'CT',
                                        ],
                                        'country' => 'United States',
                                    ],
                                ],
                            ],
                            'access' => 'restricted',
                        ],
                        [
                            'value' => [
                                'name' => [
                                    'Department 3',
                                    'University 3',
                                ],
                                'address' => [
                                    'formatted' => [
                                        'Middletown',
                                        'CT',
                                        'United States',
                                    ],
                                    'components' => [
                                        'locality' => [
                                            'Middletown',
                                        ],
                                        'area' => [
                                            'CT',
                                        ],
                                        'country' => 'United States',
                                    ],
                                ],
                            ],
                            'access' => 'public',
                        ],
                        [
                            'value' => [
                                'name' => [
                                    'Department 4',
                                    'University 1',
                                ],
                                'address' => [
                                    'formatted' => [
                                        'Middletown',
                                        'CT',
                                        'United States',
                                    ],
                                    'components' => [
                                        'locality' => [
                                            'Middletown',
                                        ],
                                        'area' => [
                                            'CT',
                                        ],
                                        'country' => 'United States',
                                    ],
                                ],
                            ],
                            'access' => 'public',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $url);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Preferred Name', $crawler->filter('.content-header-profile__display_name')->text());
        $this->assertSame(['University 1', 'University 3'],
            array_map('trim', $crawler->filter('.content-header-profile__affiliation')->extract(['_text'])));
        $this->assertSame('j.carberry@orcid.org', $crawler->filter('.content-header-profile__email')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Preferred Name | Profiles | eLife', $crawler->filter('title')->text());
        $this->assertSame('/profiles/1', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/profiles/1', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Preferred Name', $crawler->filter('meta[property="og:title"]')->attr('content'));
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
    public function it_displays_a_404_if_the_profile_is_not_found()
    {
        $client = static::createClient();

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

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/annotations?by=1&page=1&per-page=10&order=desc&use-date=updated&access=public',
                ['Accept' => 'application/vnd.elife.annotation-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.annotation-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/profiles/1';
    }
}
