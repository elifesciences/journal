<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use test\eLife\Journal\WebTestCase;

final class LocalPageStubControllerTest extends WebTestCase
{
    #[Test]
    #[DataProvider('stubbedPathsProvider')]
    public function it_displays_the_local_page_stub_instead_of_a_page_that_is_hosted_elsewhere($path)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $path);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Local Page Stub', $crawler->filter('.content-header__title')->text());
    }

    public static function stubbedPathsProvider() : array
    {
        return [
            ['/about'],
            ['/about/aims-scope'],
            ['/about/peer-review'],
            ['/about/publishing-with-elife'],
            ['/about/research-culture'],
            ['/about/technology'],
            ['/reviewed-preprints/1234'],
        ];
    }
}
