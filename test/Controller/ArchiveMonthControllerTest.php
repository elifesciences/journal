<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group time-sensitive
 */
final class ArchiveMonthControllerTest extends PageTestCase
{
    public function setUp()
    {
        ClockMock::withClockMock(strtotime('2016-01-01 00:00:00'));
    }

    /**
     * @test
     */
    public function it_displays_the_archive_page_for_a_month()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('December 2015', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     * @dataProvider invalidYearProvider
     */
    public function it_returns_a_404_for_an_invalid_year_and_month($year, string $month)
    {
        $client = static::createClient();

        $client->request('GET', "/archive/$year/$month");

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidYearProvider() : array
    {
        return [
            'before eLife' => [2012, 'september'],
            'current month' => [2016, 'january'],
            'next month' => [2016, 'february'],
            'not a year' => ['foo', 'january'],
            'not a month' => [2016, 'smarch'],
        ];
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&start-date=2015-12-01&end-date=2015-12-31',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        return '/archive/2015/december';
    }
}
