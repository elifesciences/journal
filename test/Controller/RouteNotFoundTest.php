<?php

namespace test\eLife\Journal\Controller;

use test\eLife\Journal\WebTestCase;

final class RouteNotFoundTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_returns_a_404_when_a_route_is_not_found()
    {
        $client = static::createClient();

        $client->request('GET', '/foo');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
