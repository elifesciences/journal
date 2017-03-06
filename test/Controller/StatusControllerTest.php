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
    }

    protected function getUrl() : string
    {
        return '/status';
    }
}
