<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class HomeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_homepage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
        $this->assertEmpty($client->getResponse()->headers->getCookies());
    }

    /**
     * @test
     */
    public function it_has_announcements()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/highlights/announcements?page=1&per-page=3&order=desc',
                ['Accept' => 'application/vnd.elife.highlight-list+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.highlight-list+json; version=3'],
                json_encode([
                    'total' => 2,
                    'items' => [
                        [
                            'title' => 'Article highlight',
                            'item' => [
                                'status' => 'vor',
                                'stage' => 'preview',
                                'id' => '00001',
                                'version' => 1,
                                'type' => 'research-article',
                                'doi' => '10.7554/eLife.00001',
                                'title' => 'Article',
                                'volume' => 1,
                                'elocationId' => 'e00001',
                                'copyright' => [
                                    'license' => 'CC-BY-4.0',
                                    'holder' => 'Bar',
                                    'statement' => 'Copyright statement.',
                                ],
                                'subjects' => [
                                    [
                                        'id' => 'subject',
                                        'name' => 'Subject',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Podcast episode highlight',
                            'item' => [
                                'type' => 'podcast-episode',
                                'number' => 1,
                                'title' => 'Podcast episode',
                                'published' => '2000-01-01T00:00:00Z',
                                'image' => [
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
                                'sources' => [
                                    [
                                        'mediaType' => 'audio/mpeg',
                                        'uri' => 'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.05.mp3',
                                    ],
                                ],
                                'subjects' => [
                                    [
                                        'id' => 'subject',
                                        'name' => 'Subject',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(2, $crawler->filter('.list-heading:contains("New from eLife") + .listing-list > .listing-list__item'));
        $this->assertContains('Article highlight', $crawler->filter('.list-heading:contains("New from eLife") + .listing-list > .listing-list__item:nth-child(1)')->text());
        $this->assertContains('Podcast episode highlight', $crawler->filter('.list-heading:contains("New from eLife") + .listing-list > .listing-list__item:nth-child(2)')->text());
    }

    /**
     * @test
     */
    public function it_displays_the_correct_dates_in_the_latest_research_list()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=reviewed-preprint&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => 4,
                    'items' => [
                        [
                            'status' => 'reviewed',
                            'stage' => 'published',
                            'id' => '4',
                            'type' => 'reviewed-preprint',
                            'doi' => '10.7554/eLife.4',
                            'title' => 'Reviewed preprint 4 title',
                            'published' => '2014-01-01T00:00:00Z',
                            'versionDate' => '2014-01-01T00:00:00Z',
                            'reviewedDate' => '2014-01-01T00:00:00Z',
                            'statusDate' => '2014-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e4',
                            'authorLine' => 'Foo Bar',
                        ],
                        [
                            'status' => 'reviewed',
                            'stage' => 'published',
                            'id' => '3',
                            'type' => 'reviewed-preprint',
                            'doi' => '10.7554/eLife.3',
                            'title' => 'Reviewed preprint 3 title',
                            'published' => '2012-01-01T00:00:00Z',
                            'versionDate' => '2013-01-01T00:00:00Z',
                            'reviewedDate' => '2012-07-01T00:00:00Z',
                            'statusDate' => '2013-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e3',
                            'authorLine' => 'Foo Bar',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '2',
                            'version' => 2,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.2',
                            'title' => 'Article 2 title',
                            'published' => '2012-01-01T00:00:00Z',
                            'versionDate' => '2013-01-01T00:00:00Z',
                            'statusDate' => '2013-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e2',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author et al.',
                                'statement' => 'Creative Commons Attribution License.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                        [
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => '1',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.1',
                            'title' => 'Article 1 title',
                            'published' => '2012-01-01T00:00:00Z',
                            'versionDate' => '2012-01-01T00:00:00Z',
                            'statusDate' => '2012-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e1',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author et al.',
                                'statement' => 'Creative Commons Attribution License.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $teasers = $crawler->filter('.list-heading:contains("Latest research") + ol > li');
        $this->assertCount(4, $teasers);

        $this->assertSame('Reviewed preprint 4 title', trim($teasers->eq(0)->filter('.teaser__header_text')->text()));
        $this->assertSame('Reviewed Preprint Jan 1, 2014', trim(preg_replace('/\s+/S', ' ', $teasers->eq(0)->filter('.teaser__footer .meta')->text())));

        $this->assertSame('Reviewed preprint 3 title', trim($teasers->eq(1)->filter('.teaser__header_text')->text()));
        $this->assertSame('Reviewed Preprint Updated Jan 1, 2013', trim(preg_replace('/\s+/S', ' ', $teasers->eq(1)->filter('.teaser__footer .meta')->text())));

        $this->assertSame('Article 2 title', trim($teasers->eq(2)->filter('.teaser__header_text')->text()));
        $this->assertSame('Research Article Updated Jan 1, 2013', trim(preg_replace('/\s+/S', ' ', $teasers->eq(2)->filter('.teaser__footer .meta')->text())));

        $this->assertSame('Article 1 title', trim($teasers->eq(3)->filter('.teaser__header_text')->text()));
        $this->assertSame('Research Article Jan 1, 2012', trim(preg_replace('/\s+/S', ' ', $teasers->eq(3)->filter('.teaser__footer .meta')->text())));
    }

    /**
     * @test
     */
    public function it_displays_reviewed_preprint_on_homepage_listing()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=reviewed-preprint&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'id' => '19560',
                            'type' => 'reviewed-preprint',
                            'doi' => '10.7554/eLife.19560',
                            'status' => 'reviewed',
                            'authorLine' => 'Lee R Berger et al.',
                            'title' => 'reviewed preprint title',
                            'indexContent' => 'reviewed preprint index content',
                            'titlePrefix' => 'Title prefix',
                            'stage' => 'published',
                            'published' => '2022-08-01T00:00:00Z',
                            'reviewedDate' => '2022-08-01T00:00:00Z',
                            'statusDate' => '2022-08-01T00:00:00Z',
                            'volume' => 4,
                            'elocationId' => 'e19560',
                            'pdf' => 'https://elifesciences.org/content/4/e19560.pdf',
                            'subjects' => [
                                [
                                    'id' => 'genomics-evolutionary-biology',
                                    'name' => 'Genomics and Evolutionary Biology',
                                ],
                            ],
                            'curationLabels' => [
                                'Ground-breaking',
                                'Convincing',
                            ],
                            'image' => [
                                'thumbnail' => [
                                    'uri' => 'https://iiif.elifesciences.org/lax/19560%2Felife-19560-fig1-v1.tif',
                                    'alt' => '',
                                    'source' => [
                                        'mediaType' => 'image/jpeg',
                                        'uri' => 'https://iiif.elifesciences.org/lax/19560%2Felife-19560-fig1-v1.tif/full/full/0/default.jpg',
                                        'filename' => 'an-image.jpg',
                                    ],
                                    'size' => [
                                        'width' => 4194,
                                        'height' => 4714,
                                    ],
                                    'focalPoint' => [
                                        'x' => 25,
                                        'y' => 75,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 1,
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $teasers = $crawler->filter('.list-heading:contains("Latest research") + ol > li');
        $this->assertCount(1, $teasers);

        $this->assertSame('reviewed preprint title', trim($teasers->eq(0)->filter('.teaser__header_text')->text()));
        $this->assertSame('Reviewed Preprint Aug 1, 2022', trim(preg_replace('/\s+/S', ' ', $teasers->eq(0)->filter('.teaser__footer .meta')->text())));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Latest research | eLife', $crawler->filter('title')->text());
        $this->assertSame('/', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Latest research', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife works to improve research communication through open science and open technology innovation', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife works to improve research communication through open science and open technology innovation', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('application/rss+xml', $crawler->filter('link[rel="alternate"]')->attr('type'));
        $this->assertSame('Read the latest life sciences research from eLife Sciences', $crawler->filter('link[rel="alternate"]')->attr('title'));
        $this->assertSame('/rss/recent.xml', $crawler->filter('link[rel="alternate"]')->attr('href'));
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
    public function it_configures_javascript_libraries_through_a_script_element()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $scriptTag = $crawler->filter('script')->last()->text();
        $this->assertContains('window.elifeConfig.domain = \'localhost\';', $scriptTag);
    }

    /**
     * @test
     */
    public function it_displays_the_homepage_even_if_the_api_is_unavailable()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=reviewed-preprint&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                [
                    'Cache-Control' => 'public, max-age=0, stale-if-error=60',
                    'Content-Type' => 'application/vnd.elife.search+json; version=2',
                ],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
        $this->assertEmpty($client->getResponse()->headers->getCookies());

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                503,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => 'Service unavailable',
                ])
            )
        );

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
        $this->assertEmpty($client->getResponse()->headers->getCookies());
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

        $client->request('GET', '/?page=' . $page);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidPageProvider(): Traversable
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
                            'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=reviewed-preprint&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                            ['Accept' => 'application/vnd.elife.search+json; version=2']
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

    /**
     * @test
     */
    public function it_displays_a_subjects_list()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page=1&per-page=100&order=asc',
                ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'id' => 'subject',
                            'name' => 'Subject name',
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
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame('Subject name', trim($crawler->filter('.section-listing__list_item')->text()));
    }

    /**
     * @test
     */
    public function subjects_are_rewritten()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page=1&per-page=100&order=asc',
                ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                json_encode([
                    'total' => 2,
                    'items' => [
                        [
                            'id' => 'old-subject',
                            'name' => 'Old Subject',
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
                        ],
                        [
                            'id' => 'new-subject',
                            'name' => 'New Subject',
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
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(['New Subject'], array_map('trim', $crawler->filter('.section-listing__list_item')->extract('_text')));
    }

    /**
     * @test
     * @dataProvider coversProvider
     */
    public function it_displays_different_types_in_highlight_item(
        array $cover,
        string $expectedTitle,
        string $expectedImpactStatement,
        string $expectedMetaType,
        string $expectedDate,
        string $expectedAuthorLine = null
    )
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers/current',
                ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                json_encode([
                        'total' => 4,
                        'items' => [
                            $this->prepareCover('research-article', 1),
                            $cover,
                            $this->prepareCover('podcast-episode', 2),
                            $this->prepareCover('interview', 3),
                        ]
                    ]
                )
            )
        );

        $crawler = $client->request('GET', $this->getUrl());
        $this->assertEquals(3, $crawler->filter('.highlight-item')->count());
        $highlightItem = $crawler->filter('.highlight__items')->eq(0);
        $this->assertSame($expectedTitle, trim($highlightItem
            ->filter('.highlight-item__title_link')->text()));
        $this->assertSame($expectedImpactStatement, trim($crawler->filter('.highlight__items')->eq(0)
            ->filter('.highlight-item .highlight-item__body p')->text()));

        if ($expectedAuthorLine) {
            $this->assertSame($expectedAuthorLine, trim($highlightItem->filter('.author-line')->text()));
        } else {
            $this->assertCount(0, $highlightItem->filter('.author-line'));
        }
        $this->assertSame($expectedMetaType, trim($highlightItem->filter('.meta__type')->text()));
        $this->assertSame($expectedDate, trim($highlightItem->filter('.date')->text()));
    }

    /**
     * @test
     * @dataProvider coversProvider
     */
    public function it_displays_different_types_in_hero_banner(
        array $cover,
        string $expectedTitle,
        string $expectedImpactStatement,
        string $expectedMetaType,
        string $expectedDate,
        string $expectedAuthorLine = null,
        array $expectedSubjects = []
    )
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers/current',
                ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                json_encode([
                        'total' => 4,
                        'items' => [
                            $cover,
                            $this->prepareCover('research-article', 1),
                            $this->prepareCover('podcast-episode', 2),
                            $this->prepareCover('interview', 3),
                        ]
                    ]
                )
            )
        );

        $crawler = $client->request('GET', $this->getUrl());
        $heroDetails = $crawler->filter('.hero-banner__details');
        $this->assertSame($expectedTitle, trim($heroDetails->filter('.hero-banner__title_link')->text()));
        $this->assertSame($expectedImpactStatement, trim($heroDetails->filter('.hero-banner__summary')->text()));
        if ($expectedAuthorLine) {
            $this->assertSame($expectedAuthorLine, trim($heroDetails->filter('.author-line')->text()));
        } else {
            $this->assertCount(0, $heroDetails->filter('.author-line'));
        }
        $this->assertSame($expectedMetaType, trim($heroDetails->filter('.meta__type')->text()));
        $this->assertSame($expectedDate, trim($heroDetails->filter('.date')->text()));

        if (!empty($expectedSubjects)) {
            $subjectLinks = $heroDetails->filter('.hero-banner__subject_link');
            $this->assertCount(count($expectedSubjects), $subjectLinks);

            $co = 0;
            foreach ($expectedSubjects as $url => $name) {
                $link = $subjectLinks->eq($co++);
                $this->assertSame($url, $link->attr('href'));
                $this->assertSame($name, trim($link->text()));
            }
        } else {
            $this->assertCount(0, $heroDetails->filter('.hero-banner__subject_link'));
        }
    }

    public function coversProvider(): array
    {
        return [
            'research-article' => [
                $this->prepareCover('research-article'),
                'research-article title',
                'research-article impact statement',
                'Research Article',
                'Updated Sep 11, 2015',
                'Nicholas P Lesner, Xun Wang ... Prashant Mishra',
                [
                    '/subjects/genomics-evolutionary-biology' => 'Genomics and Evolutionary Biology',
                    '/subjects/genetics-genomics' => 'Genetics and Genomics',
                ],
            ],
            'research-article-poa' => [
                $this->prepareCover('research-article-poa'),
                'research-article-poa title',
                'research-article-poa impact statement',
                'Research Article',
                'Sep 10, 2015',
                null,
                [
                    '/subjects/cancer-biology' => 'Cancer Biology',
                ],
            ],
            'blog-article' => [
                $this->prepareCover('blog-article'),
                'blog-article title',
                'blog-article impact statement',
                'Inside eLife',
                'Sep 12, 2015',
                null,
                [
                    '/subjects/genomics-evolutionary-biology' => 'Genomics and Evolutionary Biology',
                ],
            ],
            'interview' => [
                $this->prepareCover('interview'),
                'interview title',
                'interview impact statement',
                'Interview',
                'Sep 13, 2015',
            ],
            'podcast-episode' => [
                $this->prepareCover('podcast-episode'),
                'podcast-episode title',
                'podcast-episode impact statement',
                'Podcast',
                'Jul 1, 2016',
            ],
        ];
    }

    private function prepareCover(string $type, $titleSuffix = null) : array
    {
        switch ($type) {
            case 'podcast-episode':
                $item = [
                    'type' => 'podcast-episode',
                    'number' => 30,
                    'title' => 'podcast-episode title',
                    'published' => '2016-07-01T08:30:15Z',
                    'image' => [
                        'thumbnail' => [
                            'uri' => 'https://iiif.elifesciences.org/lax/09560%2Felife-09560-fig1-v1.tif',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/lax/09560%2Felife-09560-fig1-v1.tif/full/full/0/default.jpg',
                                'filename' => 'an-image.jpg',
                            ],
                            'size' => [
                                'width' => 4194,
                                'height' => 4714,
                            ],
                        ],
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => 'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.06.mp3',
                        ],
                    ],
                ];
                break;
            case 'interview':
                $item = [
                    'type' => 'interview',
                    'id' => '2',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'Alicia Rosello',
                            'index' => 'Rosello, Alicia',
                        ],
                    ],
                    'title' => 'interview title',
                    'published' => '2015-09-13T00:00:00Z',

                ];
                break;
            case 'blog-article':
                $item = [
                    'id' => '1',
                    'type' => 'blog-article',
                    'title' => 'blog-article title',
                    'published' => '2015-09-12T00:00:00Z',
                    'subjects' => [
                        [
                            'id' => 'genomics-evolutionary-biology',
                            'name' => 'Genomics and Evolutionary Biology',
                        ],
                    ],
                ];
                break;
            case 'research-article-poa':
                $item = [
                    'status' => 'poa',
                    'id' => '09561',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09561',
                    'title' => 'research-article title',
                    'stage' => 'published',
                    'published' => '2015-09-10T00:00:00Z',
                    'statusDate' => '2015-09-10T00:00:00Z',
                    'volume' => 4,
                    'elocationId' => 'e09561',
                    'subjects' => [
                        [
                            'id' => 'cancer-biology',
                            'name' => 'Cancer Biology',
                        ],
                    ],
                ];
                break;
            default:
                $item = [
                    'status' => 'vor',
                    'id' => '09560',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'title' => 'research-article title',
                    'authorLine' => 'Nicholas P Lesner, Xun Wang ... Prashant Mishra',
                    'stage' => 'published',
                    'published' => '2015-09-10T00:00:00Z',
                    'statusDate' => '2015-09-11T00:00:00Z',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'subjects' => [
                        [
                            'id' => 'genomics-evolutionary-biology',
                            'name' => 'Genomics and Evolutionary Biology',
                        ],
                        [
                            'id' => 'genetics-genomics',
                            'name' => 'Genetics and Genomics',
                        ],
                    ],
                ];
        }

        if ($titleSuffix) {
            $item['title'] .= $titleSuffix;
        }

        return [
            'title' => $type.' title'.$titleSuffix,
            'impactStatement' => $type.' impact statement',
            'image' => [
                'uri' => 'https://iiif.elifesciences.org/lax/09560%2Felife-09560-fig1-v1.tif',
                'alt' => '',
                'source' => [
                    'mediaType' => 'image/jpeg',
                    'uri' => 'https://iiif.elifesciences.org/lax/09560%2Felife-09560-fig1-v1.tif/full/full/0/default.jpg',
                    'filename' => 'an-image.jpg',
                ],
                'size' => [
                    'width' => 4194,
                    'height' => 4714,
                ]
            ],
            'item' => $item,
        ];
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=reviewed-preprint&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ])
            )
        );

        return '/';
    }
}
