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
    public function it_displays_an_empty_reviewed_preprints_page()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/reviewed-preprints?page=1&per-page=10&order=desc&use-date=default',
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

        $crawler = $client->request('GET', '/reviewed-preprints');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Reviewed Preprints', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No items available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_displays_a_reviewed_preprints_listing_page()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/reviewed-preprints?page=1&per-page=10&order=desc&use-date=default',
                [
                    'Accept' => 'application/vnd.elife.reviewed-preprint-list+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.reviewed-preprint-list+json; version=1',
                ],
                json_encode([
                    'total' => 2,
                    'items' => [
                        [
                            'id' => '09560',
                            'doi' => '10.7554/eLife.09560',
                            'status' => 'final',
                            'authorLine' => 'Lee R Berger, John Hawks ... Scott A Williams',
                            'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                            'indexContent' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                            'stage' => 'published',
                            'published' => '2015-09-10T00:00:00Z',
                            'reviewedDate' => '2015-08-10T00:00:00Z',
                            'statusDate' => '2015-09-10T00:00:00Z',
                            'volume' => 4,
                            'elocationId' => 'e09560',
                            'pdf' => 'https://elifesciences.org/content/4/e09560.pdf',
                            'subjects' => [
                                0 => [
                                    'id' => 'biochemistry',
                                    'name' => 'Biochemistry',
                                ],
                                1 => [
                                    'id' => 'genomics-evolutionary-biology',
                                    'name' => 'Genomics and Evolutionary Biology',
                                ],
                            ],
                            'curationLabels' => [
                                0 => 'Ground-breaking',
                                1 => 'Convincing',
                            ],
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
                                    'focalPoint' => [
                                        'x' => 25,
                                        'y' => 75,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => '29561',
                            'doi' => '10.7554/eLife.29561',
                            'status' => 'reviewed',
                            'authorLine' => 'Yongjian Huang',
                            'title' => 'An example of a Reviewed preprint',
                            'indexContent' => 'An example of a Reviewed preprint that is not yet vor',
                            'titlePrefix' => 'Title prefix',
                            'stage' => 'published',
                            'published' => '2022-08-01T00:00:00Z',
                            'reviewedDate' => '2022-08-01T00:00:00Z',
                            'statusDate' => '2022-08-01T00:00:00Z',
                            'volume' => 4,
                            'elocationId' => 'e29561',
                            'pdf' => 'https://elifesciences.org/content/4/e29561.pdf',
                            'subjects' => [
                                0 => [
                                    'id' => 'biochemistry',
                                    'name' => 'Biochemistry',
                                ],
                                1 => [
                                    'id' => 'biophysics-structural-biology',
                                    'name' => 'Biophysics and Structural Biology',
                                ],
                            ],
                            'curationLabels' => [
                                0 => 'Ground-breaking',
                            ],
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/reviewed-preprints');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(2, $crawler->filter('.list-heading:contains("Latest") + .listing-list > .listing-list__item'));
        $this->assertContains('Homo naledi, a new species of the genus Homo from the Dinaledi Chamber, South Africa', $crawler->filter('.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child(1)')->text());
        $this->assertContains('An example of a Reviewed preprint', $crawler->filter('.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child(2)')->text());
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
        $this->assertSame('Preprints that have been invited for review by eLife are published as Reviewed Preprints and include an eLife assessment, public reviews and a response from the authors (if available).', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Preprints that have been invited for review by eLife are published as Reviewed Preprints and include an eLife assessment, public reviews and a response from the authors (if available).', $crawler->filter('meta[name="description"]')->attr('content'));
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
        file_put_contents('aa'.$page, $client->getResponse()->getContent());

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
                            'http://api.elifesciences.org/reviewed-preprints?page=1&per-page=1&order=desc&use-date=default',
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
                'http://api.elifesciences.org/reviewed-preprints?page=1&per-page=10&order=desc&use-date=default',
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
}
