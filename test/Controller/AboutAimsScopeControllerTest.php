<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class AboutAimsScopeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_aims_and_scope_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Aims and scope', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_shows_aims_and_scope_for_each_subject()
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
                            'id' => 'subject1',
                            'name' => 'Subject 1',
                            'impactStatement' => 'Subject 1 impact statement.',
                            'aimsAndScope' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Paragraph 1.',
                                ],
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Paragraph 2.',
                                ],
                            ],
                            'image' => [
                                'banner' => [
                                    'uri' => 'https://www.example.com/iiif/ban%2Fner',
                                    'alt' => '',
                                    'source' => [
                                        'mediaType' => 'image/jpeg',
                                        'uri' => 'https://www.example.com/banner.jpg',
                                        'filename' => 'image.jpg',
                                    ],
                                    'size' => [
                                        'width' => 1800,
                                        'height' => 1600,
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
                            'id' => 'subject2',
                            'name' => 'Subject 2',
                            'impactStatement' => 'Subject 2 impact statement.',
                            'image' => [
                                'banner' => [
                                    'uri' => 'https://www.example.com/iiif/ban%2Fner',
                                    'alt' => '',
                                    'source' => [
                                        'mediaType' => 'image/jpeg',
                                        'uri' => 'https://www.example.com/banner.jpg',
                                        'filename' => 'image.jpg',
                                    ],
                                    'size' => [
                                        'width' => 1800,
                                        'height' => 1600,
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

        $crawler = $client->request('GET', '/about/aims-scope');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(2, $crawler->filter('.article-section'));
        $this->assertSame('Subject 1', trim($crawler->filter('section:nth-of-type(1) .article-section__header')->text()));
        $this->assertSame("Paragraph 1.\nParagraph 2. See editors", trim($crawler->filter('section:nth-of-type(1) .article-section__body')->text()));
        $this->assertSame('Subject 2', trim($crawler->filter('section:nth-of-type(2) .article-section__header')->text()));
        $this->assertSame('See editors', trim($crawler->filter('section:nth-of-type(2) .article-section__body')->text()));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Aims and scope | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/aims-scope', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/aims-scope', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Aims and scope', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife welcomes the submission of Research Articles, Short Reports, Tools and Resources articles, Research Advances, Scientific Correspondence and Review Articles in the subject areas below.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife welcomes the submission of Research Articles, Short Reports, Tools and Resources articles, Research Advances, Scientific Correspondence and Review Articles in the subject areas below.', $crawler->filter('meta[name="description"]')->attr('content'));
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
    public function it_displays_announcement_info_bar()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'eLife\'s peer-review process is changing. From January 2023, eLife will no longer make accept/reject decisions after peer review. Instead, every preprint sent for peer review will be published on the eLife website as a “Reviewed Preprint” that includes an eLife assessment, public reviews, and a response from the authors (if available). When writing the eLife assessment, the editors and reviewers will use a common vocabulary to summarize the significance of the findings and the strength of the evidence reported in the preprint. <a href="/inside-elife/54d63486">Read about the new process</a>.',
            $crawler->filter('.content-container .info-bar--announcement .info-bar__text')->html()
        );
    }

    protected function getUrl() : string
    {
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
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/about/aims-scope';
    }
}
