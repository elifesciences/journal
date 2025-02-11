<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class BrowseControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_browse_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Showing 0 articles', trim($crawler->filter('.message-bar')->text()));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Browse the latest research | eLife', $crawler->filter('title')->text());
        $this->assertSame('/browse-demo?include-original=0', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/browse-demo?include-original=0', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Browse the latest research', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:description"]'));
        $this->assertEmpty($crawler->filter('meta[name="description"]'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
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
    public function it_shows_reviewed_preprints_on_results()
    {
        $this->markTestSkipped();
        $client = static::createClient();

        $items = [
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
                            'height' => 4714
                        ],
                        'focalPoint' => [
                            'x' => 25,
                            'y' => 75,
                        ]
                    ]
                ],
            ],
        ];

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=correction&type[]=expression-concern&type[]=registered-report&type[]=replication-study&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=retraction&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=reviewed-preprint&use-date=default",
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => 1,
                    'items' => $items,
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
                        'expression-concern' => 0,
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
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=correction&type[]=expression-concern&type[]=registered-report&type[]=replication-study&type[]=research-advance&type[]=research-article&type[]=research-communication&type[]=retraction&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=reviewed-preprint&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
                json_encode([
                    'total' => 1,
                    'items' => $items,
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
                        'expression-concern' => 0,
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

        $crawler = $client->request('GET', '/browse-demo?page=1&per-page=10');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $listing = $crawler->filter('ol.listing-list > li');

        $this->assertCount(1, $listing);
        $this->assertSame('reviewed preprint title', trim($listing->eq(0)->filter('.teaser__header_text')->text()));
        $this->assertSame('Reviewed Preprint Aug 1, 2022', trim(preg_replace('/\s+/S', ' ', $listing->eq(0)->filter('.teaser__footer .meta')->text())));
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'https://search.filter-by-term.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=short-report&type[]=tools-resources&type[]=reviewed-preprint&use-date=default&elifeAssessmentSignificance[]=landmark&elifeAssessmentSignificance[]=fundamental&elifeAssessmentSignificance[]=important&elifeAssessmentSignificance[]=valuable&elifeAssessmentSignificance[]=useful&elifeAssessmentSignificance[]=not-assigned&elifeAssessmentStrength[]=exceptional&elifeAssessmentStrength[]=compelling&elifeAssessmentStrength[]=convincing&elifeAssessmentStrength[]=solid&elifeAssessmentStrength[]=incomplete&elifeAssessmentStrength[]=inadequate&elifeAssessmentStrength[]=not-assigned',
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
                        'expression-concern' => 0,
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

        $this->mockApiResponse(
            new Request(
                'GET',
                'https://search.filter-by-term.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=short-report&type[]=tools-resources&type[]=reviewed-preprint&use-date=default&elifeAssessmentSignificance[]=landmark&elifeAssessmentSignificance[]=fundamental&elifeAssessmentSignificance[]=important&elifeAssessmentSignificance[]=valuable&elifeAssessmentSignificance[]=useful&elifeAssessmentSignificance[]=not-assigned&elifeAssessmentStrength[]=exceptional&elifeAssessmentStrength[]=compelling&elifeAssessmentStrength[]=convincing&elifeAssessmentStrength[]=solid&elifeAssessmentStrength[]=incomplete&elifeAssessmentStrength[]=inadequate&elifeAssessmentStrength[]=not-assigned',
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
                        'expression-concern' => 0,
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

        return '/browse-demo';
    }
}
