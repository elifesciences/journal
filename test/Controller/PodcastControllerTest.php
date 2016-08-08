<?php

namespace test\eLife\Journal\Controller;

final class PodcastControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_podcast_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/podcast');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife podcast', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/podcast';
    }
}
