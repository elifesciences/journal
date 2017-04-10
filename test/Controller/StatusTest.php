<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\WebTestCase;

final class StatusTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_returns_200_pong_when_the_application_is_correctly_setup()
    {
        $client = static::createClient();

        $client->request('GET', '/ping');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $client->getResponse()->headers->get('Content-Type'));
        $this->assertSame('pong', $client->getResponse()->getContent());
        $this->assertFalse($client->getResponse()->isCacheable());
        $this->assertSame('none', $client->getResponse()->headers->get('X-Robots-Tag'));
    }

    /**
     * @test
     */
    public function it_returns_200_status_when_the_status_checks_work()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/foo',
                ['Cache-Control' => 'no-cache, no-store']
            ),
            new Response()
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/bar',
                ['Cache-Control' => 'no-cache, no-store']
            ),
            new Response()
        );

        $crawler = $client->request('GET', '/status');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $checks = $crawler->filter('li');
        $this->assertCount(2, $checks);
        $this->assertSame('✔ Check 1', trim($checks->eq(0)->text()));
        $this->assertSame('✔ Check 2', trim($checks->eq(1)->text()));
        $this->assertFalse($client->getResponse()->isCacheable());
        $this->assertSame('none', $client->getResponse()->headers->get('X-Robots-Tag'));
    }

    /**
     * @test
     */
    public function it_returns_500_status_when_a_status_check_does_not_work()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/status');

        $this->assertSame(500, $client->getResponse()->getStatusCode());
        $checks = $crawler->filter('li');
        $this->assertCount(2, $checks);
        $this->assertSame('✘ Check 1', trim($checks->eq(0)->filter('.check__name')->text()));
        $this->assertNotEmpty($checks->eq(0)->filter('.check__message'));
        $this->assertSame('✘ Check 2', trim($checks->eq(1)->filter('.check__name')->text()));
        $this->assertNotEmpty($checks->eq(1)->filter('.check__message'));
        $this->assertFalse($client->getResponse()->isCacheable());
        $this->assertSame('none', $client->getResponse()->headers->get('X-Robots-Tag'));
    }
}
