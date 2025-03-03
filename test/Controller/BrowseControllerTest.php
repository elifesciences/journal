<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Uri;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

final class BrowseControllerTest extends PageTestCase
{
    private $researchTypes = ['correction', 'expression-concern', 'registered-report', 'replication-study', 'research-advance', 'research-article', 'research-communication', 'retraction', 'review-article', 'scientific-correspondence', 'short-report', 'tools-resources', 'reviewed-preprint'];

    

    /**
     * @test
     */
    public function it_displays_the_browse_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertStatusCodeIs200($client);
        $this->assertSame('0 results found', trim($crawler->filter('.message-bar')->text()));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertStatusCodeIs200($client);

        $this->assertSame('Browse the latest research | eLife', $crawler->filter('title')->text());
        $this->assertSame('/browse', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/browse', $crawler->filter('meta[property="og:url"]')->attr('content'));
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
            $this->buildSearchApiRequestForOneItem(),
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
            $this->buildSearchApiRequestForTenItems(),
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

        $crawler = $client->request('GET', '/browse');
        $this->assertStatusCodeIs200($client);
        $listing = $crawler->filter('ol.listing-list > li');

        $this->assertCount(1, $listing);
        $this->assertSame('reviewed preprint title', trim($listing->eq(0)->filter('.teaser__header_text')->text()));
        $this->assertSame('Reviewed Preprint Aug 1, 2022', trim(preg_replace('/\s+/S', ' ', $listing->eq(0)->filter('.teaser__footer .meta')->text())));
    }

    /**
     * @test
     */
    public function it_displays_the_requested_minimum_significance_in_the_filter_dropdown_when_minimum_elife_significance_is_selected() {
        $client = static::createClient();
        $this->setUpApiMocksForMinimumSignificanceQuery();

        $crawler = $client->request('GET', '/browse?minimumSignificance=landmark');

        $this->assertStatusCodeIs200($client);

        $selectedMinimumSignificanceDropdownValue = $crawler->filter('select[name=minimumSignificance]>option[selected]')->attr('value');
        $this->assertSame($selectedMinimumSignificanceDropdownValue, 'landmark');
    }

    /**
     * @test
     */
    public function it_displays_the_requested_minimum_strength_in_the_filter_dropdown_when_minimum_elife_strength_is_selected() {
        $client = static::createClient();
        $this->setUpApiMocksForMinimumStrengthQuery();

        $this->markTestSkipped();
        $crawler = $client->request('GET', '/browse?minimumStrength=solid');

        $this->assertStatusCodeIs200($client);

        $selectedMinimumStrengthDropdownValue = $crawler->filter('select[name=minimumStrength]>option[selected]')->attr('value');
        $this->assertSame($selectedMinimumStrengthDropdownValue, 'solid');
    }

    protected function setUpApiMocksForMinimumStrengthQuery()
    {
    }

    protected function setUpApiMocksForMinimumSignificanceQuery()
    {

        $this->mockApiResponse(
            $this->buildSearchApiRequestForOneItemWithLandmarkSignificance(),
            $this->getEmptyResponse()
        );

        $this->mockApiResponse(
            $this->buildSearchApiRequestForTenItemsWithLandmarkSignificance(),
            $this->getEmptyResponse()
        );
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            $this->buildSearchApiRequestForOneItem(),
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
            $this->buildSearchApiRequestForTenItems(),
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

        return '/browse';
    }

    private function buildSearchApiRequestForOneItem()
    {
        return $this->buildApiRequest([
            'for' => '',
            'page' => '1',
            'per-page' => '1',
            'sort' => 'date',
            'order' => 'desc',
            'elifeAssessmentSignificance[]' => ['important', 'fundamental', 'landmark', 'useful', 'valuable', 'not-assigned'],
            'type[]' => $this->researchTypes,
            'use-date' => 'default',
        ]);
    }

    private function buildSearchApiRequestForOneItemWithLandmarkSignificance()
    {
        return $this->buildApiRequest([
            'for' => '',
            'page' => '1',
            'per-page' => '1',
            'sort' => 'date',
            'order' => 'desc',
            'elifeAssessmentSignificance[]' => ['landmark'],
            'type[]' => $this->researchTypes,
            'use-date' => 'default',
        ]);
    }

    private function buildSearchApiRequestForTenItems()
    {
        return $this->buildApiRequest([
            'for' => '',
            'page' => '1',
            'per-page' => '10',
            'sort' => 'date',
            'order' => 'desc',
            'elifeAssessmentSignificance[]' => ['important', 'fundamental', 'landmark', 'useful', 'valuable', 'not-assigned'],
            'type[]' => $this->researchTypes,
            'use-date' => 'default',
        ]);
    }

    private function buildSearchApiRequestForTenItemsWithLandmarkSignificance()
    {
        return $this->buildApiRequest([
            'for' => '',
            'page' => '1',
            'per-page' => '10',
            'sort' => 'date',
            'order' => 'desc',
            'elifeAssessmentSignificance[]' => ['landmark'],
            'type[]' => $this->researchTypes,
            'use-date' => 'default',
        ]);
    }

    private function buildApiRequest(array $query)
    {
        $parts = [
            'scheme' => 'http',
            'host' => 'api.elifesciences.org',
            'path' => 'search',
            'query' => $query,
        ];
    
        if (!empty($parts['query'])) {
            $parts['query'] = Query::build(array_filter($parts['query']), false);
        }

        $uri = Uri::fromParts($parts);

        if (!isset(Query::parse($uri->getQuery())['for'])) {
            $uri = $uri->withQuery('for=&'.$uri->getQuery());
        }

        return new Request(
            'GET',
            $uri,
            ['Accept' => 'application/vnd.elife.search+json; version=2']
        );
    }

    private function assertStatusCodeIs200(Client $client)
    {
        $crawler = $client->getCrawler();
        $errorMessage = $crawler->filter('title')->text();
        $this->assertSame(200, $client->getResponse()->getStatusCode(), $errorMessage);
    }
    
    private function getEmptyResponse()
    {
        return new Response(
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
                );
    }
}
