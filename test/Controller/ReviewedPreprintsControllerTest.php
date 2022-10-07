<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class ReviewedPreprintsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_a_list_of_reviewed_preprints()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Reviewed Preprints', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No reviewed preprints available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Reviewed Preprints | eLife', $crawler->filter('title')->text());
        $this->assertSame('/reviewed-preprints', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/reviewed-preprints', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Reviewed Preprints', $crawler->filter('meta[property="og:title"]')->attr('content'));
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
     * @dataProvider invalidPageProvider
     */
    public function it_displays_a_404_when_not_on_a_valid_page($page, callable $callable = null)
    {
        $client = static::createClient();

        if ($callable) {
            $callable();
        }

        $client->request('GET', '/reviewed-preprints?page='.$page);

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
                            'http://api.elifesciences.org/reviewed-preprints?page=1&per-page=10&order=desc',
                            ['Accept' => 'application/vnd.elife.reviewed-preprint-list+json; version=1']
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
                'http://api.elifesciences.org/reviewed-preprints?page=1&per-page=10&order=desc',
                ['Accept' => 'application/vnd.elife.reviewed-preprint-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.reviewed-preprint-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/reviewed-preprints';
    }

//    protected function getUrl() : string
//    {
//        static::mockApiResponse(
//            new Request(
//                'GET',
//                'http://api.elifesciences.org/reviewed-preprints?page=1&per-page=10&order=desc',
//                [
//                    'Accept' => 'application/vnd.elife.reviewed-preprint-list+json; version=1',
//                ]
//            ),
//            new Response(
//                200,
//                [
//                    'Content-Type' => 'application/vnd.elife.reviewed-preprint-list+json; version=1',
//                ],
//                json_encode([
//                    'total' => 1,
//                    'items' => [
//                        [
//                            'id' => '19560',
//                            'type' => 'reviewed-preprint',
//                            'doi' => '10.7554/eLife.19560',
//                            'status' => 'reviewed',
//                            'authorLine' => 'Lee R Berger et al.',
//                            'title' => 'reviewed preprint title',
//                            'indexContent' => 'reviewed preprint index content',
//                            'titlePrefix' => 'Title prefix',
//                            'stage' => 'published',
//                            'published' => '2022-08-01T00:00:00Z',
//                            'reviewedDate' => '2022-08-01T00:00:00Z',
//                            'statusDate' => '2022-08-01T00:00:00Z',
//                            'volume' => 4,
//                            'elocationId' => 'e19560',
//                            'pdf' => 'https://elifesciences.org/content/4/e19560.pdf',
//                            'subjects' => [
//                                [
//                                    'id' => 'genomics-evolutionary-biology',
//                                    'name' => 'Genomics and Evolutionary Biology',
//                                ],
//                            ],
//                            'curationLabels' => [
//                                'Ground-breaking',
//                                'Convincing',
//                            ],
//                            'image' => [
//                                'thumbnail' => [
//                                    'uri' => 'https://iiif.elifesciences.org/lax/19560%2Felife-19560-fig1-v1.tif',
//                                    'alt' => '',
//                                    'source' => [
//                                        'mediaType' => 'image/jpeg',
//                                        'uri' => 'https://iiif.elifesciences.org/lax/19560%2Felife-19560-fig1-v1.tif/full/full/0/default.jpg',
//                                        'filename' => 'an-image.jpg',
//                                    ],
//                                    'size' => [
//                                        'width' => 4194,
//                                        'height' => 4714
//                                    ],
//                                    'focalPoint' => [
//                                        'x' => 25,
//                                        'y' => 75,
//                                    ]
//                                ]
//                            ],
//                        ],
//                    ],
//                ])
//            )
//        );
//
//        return '/reviewed-preprints';
//    }
}
