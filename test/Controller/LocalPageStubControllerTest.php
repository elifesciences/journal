<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class LocalPageStubControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_local_page_stub_instead_of_a_page_that_is_hosted_elsewhere()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/about');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Local Page Stub', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/about';
    }
}
