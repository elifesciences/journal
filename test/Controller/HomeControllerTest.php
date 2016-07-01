<?php

namespace test\eLife\Journal\Controller;

use test\eLife\Journal\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_displays_the_homepage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife', $crawler->filter('h1')->text());
    }
}
