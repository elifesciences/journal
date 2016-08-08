<?php

namespace test\eLife\Journal\Controller;

final class CollectionsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_collections_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/collections');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife collections', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/collections';
    }
}
