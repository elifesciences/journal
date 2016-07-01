<?php

namespace test\eLife\Journal\Controller;

final class ResourcesControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_resources_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resources');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Resources', $crawler->filter('h1')->text());
    }

    protected function getUrl() : string
    {
        return '/resources';
    }
}
