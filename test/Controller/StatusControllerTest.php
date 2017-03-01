<?php

namespace test\eLife\Journal\Controller;

use test\eLife\Journal\WebTestCase;

final class StatusControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_displays_apis_which_are_not_working()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(500, $client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(11, $crawler->filter('li')->count());
        $this->assertRegexp('/articles: The promise was rejected with reason: .*/', $crawler->filter('li:nth-child(1)')->text());
    }

    protected function getUrl() : string
    {
        return '/status';
    }
}
