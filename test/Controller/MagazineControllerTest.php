<?php

namespace test\eLife\Journal\Controller;

final class MagazineControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_magazine_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/magazine');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Magazine', $crawler->filter('main h1')->text());
    }

    protected function getUrl() : string
    {
        return '/magazine';
    }
}
