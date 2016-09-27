<?php

namespace test\eLife\Journal\Controller;

final class CommunityControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_community_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Community', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/community';
    }
}
