<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use test\eLife\Journal\WebTestCase;
use Traversable;

abstract class PageTestCase extends WebTestCase
{
    /**
     * @test
     */
    final public function it_has_the_header()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $header = $crawler->filter('header.site-header');

        $this->assertCount(1, $header);
    }

    /**
     * @test
     */
    final public function it_has_the_footer()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $footer = $crawler->filter('footer.site-footer');

        $this->assertCount(1, $footer);
    }

    final protected static function createClient(array $options = [], array $server = [])
    {
        static::bootKernel($options);

        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    final protected function stringProvider(string ...$strings) : Traversable
    {
        foreach ($strings as $string) {
            yield $string => [$string];
        }
    }

    abstract protected function getUrl() : string;
}
