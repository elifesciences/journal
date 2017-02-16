<?php

namespace test\eLife\Journal;

use Symfony\Component\HttpFoundation\Response;
use Traversable;

final class LegacyRoutingTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider redirectsProvider
     */
    public function it_redirects_legacy_routes(string $path, string $expected)
    {
        $client = static::createClient();

        $client->request('GET', $path);

        $this->assertSame(Response::HTTP_MOVED_PERMANENTLY, $client->getResponse()->getStatusCode());
        $this->assertSame("http://localhost$expected", $client->getResponse()->headers->get('Location'));
    }

    public function redirectsProvider() : Traversable
    {
        $redirects = [
            '/content/1/e00001' => '/articles/00001',
            '/content/1/e00001v1' => '/articles/00001v1',
            '/content/1/e00001/figures' => '/articles/00001/figures',
            '/content/1/e00001v1/figures' => '/articles/00001v1/figures',
        ];

        foreach ($redirects as $path => $expected) {
            yield $path => [$path, $expected];
        }
    }
}
