<?php

namespace test\eLife\Journal\Controller;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Uri;
use test\eLife\Journal\WebTestCase;
use function GuzzleHttp\Psr7\parse_query;

/**
 * @backupGlobals enabled
 */
final class SubmitControllerTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider invalidDomainProvider
     */
    public function it_does_not_redirect_if_return_url_is_not_trusted_with_invalid_domain($domain)
    {
        $client = static::createClient();
        $this->logIn($client);

        $client->request('GET', '/submit?return_url='.urlencode($domain));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function invalidDomainProvider()
    {
        return [
            ['http://elifesciences.org.uk'],
            ['https://elifesciences.org.uk'],
            ['http://subdomain.elifesciences.org.uk'],
            ['https://subdomain.elifesciences.org.uk'],
        ];
    }

    protected static function createClient(array $options = [], array $server = [])
    {
        $client = parent::createClient($options, $server);

        $client->followRedirects(false);

        return $client;
    }
}
