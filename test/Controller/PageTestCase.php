<?php

namespace test\eLife\Journal\Controller;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
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
    final public function it_may_have_a_call_to_action()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $callsToAction = $crawler->filter('.call-to-action');

        // Call to actions are limited to 1 until we resolve issues with display of multiple.
        $this->assertCount(1, $callsToAction);
        $this->assertContains('Call to action 3', $callsToAction->eq(0)->text());
    }

    /**
     * @test
     */
    public function it_has_the_sign_up_cta()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $emailCta = $crawler->filter('.email-cta');

        $this->assertCount(1, $emailCta);
    }

    /**
     * @test
     */
    public function it_has_the_footer()
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
        $this->assertSame('@eLife', $crawler->filter('meta[name="twitter:site"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_has_cache_headers()
    {
        $client = static::createClient();

        $client->request('GET', $this->getUrl());

        $this->assertSame('max-age=300, public, stale-if-error=86400, stale-while-revalidate=300', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertContains('Cookie', $client->getResponse()->getVary());

        $client->request('GET', $this->getUrl(), [], [], ['HTTP_IF_NONE_MATCH' => $client->getResponse()->headers->get('Etag')]);

        $this->assertSame(Response::HTTP_NOT_MODIFIED, $client->getResponse()->getStatusCode());
    }

    final protected static function createClient(array $options = [], array $server = [])
    {
        static::bootKernel($options);

        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        static::onCreateClient($client);

        return $client;
    }

    abstract protected function getUrl() : string;

    protected function crawlerText(Crawler $crawler, $normalize = true) : string
    {
        $text = $crawler->text();

        if ($normalize) {
            // See: https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/DomCrawler/Crawler.php#L558
            // Not introduced to Crawler::text until Symfony 4.4
            $text = trim(preg_replace('/(?:\s{2,}+|[^\S ])/', ' ', $text));
        }

        return $text;
    }

    protected static function onCreateClient(Client $client)
    {
        // Do nothing.
    }
}
