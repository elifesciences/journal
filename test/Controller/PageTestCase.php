<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use test\eLife\Journal\WebTestCase;

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

    /**
     * @test
     */
    final public function it_has_global_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame('eLife', $crawler->filter('meta[property="og:site_name"]')->attr('content'));
        $this->assertSame('en', $crawler->filter('meta[property="og:locale"]')->attr('content'));
        $this->assertSame('@eLife', $crawler->filter('meta[name="twitter:site"]')->attr('content'));
    }

    final protected static function createClient(array $options = [], array $server = [])
    {
        static::bootKernel($options);

        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    abstract protected function getUrl() : string;
}
