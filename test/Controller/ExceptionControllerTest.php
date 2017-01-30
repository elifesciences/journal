<?php

namespace test\eLife\Journal\Controller;

use test\eLife\Journal\WebTestCase;

final class ExceptionControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_returns_a_404_when_a_route_is_not_found()
    {
        $client = static::createClient(['debug' => false]);

        $crawler = $client->request('GET', '/foo');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertContains('The page you were looking for is not found.', $crawler->text());
    }

    /**
     * @test
     */
    public function it_returns_a_404_when_previewing_a_404_page()
    {
        $client = static::createClient(['debug' => true]);

        $crawler = $client->request('GET', '/_error/404');

        $this->assertContains('The page you were looking for is not found.', $crawler->text());
    }
}
