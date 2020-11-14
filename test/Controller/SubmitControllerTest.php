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
     */
    public function it_requires_you_to_be_logged_in()
    {
        $client = static::createClient();

        $client->request('GET', '/submit?return_url='.urlencode('http://foo.elifesciences.org/path'));
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $location = Uri::withoutQueryValue(new Uri($response->headers->get('Location')), 'state');
        $this->assertSameUri('http://api.elifesciences.org/oauth2/authorize?response_type=code&client_id=journal_client_id&redirect_uri=http%3A%2F%2Flocalhost%2Flog-in%2Fcheck', $location);

        $state = parse_query((new Uri($response->headers->get('Location')))->getQuery())['state'];

        $this->readyToken();

        $client->request('GET', "/log-in/check?code=foo&state={$state}");
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $this->assertSameUri('http://localhost/submit?return_url=http%3A%2F%2Ffoo.elifesciences.org%2Fpath', $response->headers->get('Location'));

        $client->followRedirect();
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $location = new Uri($response->headers->get('Location'));
        $token = parse_query($location->getQuery())['token'];

        $this->assertSameUri('http://foo.elifesciences.org/path', $location->withQuery(''));

        $jwt = (array) JWT::decode($token, $this->getParameter('submission_client_secret'), ['HS256']);

        $this->assertTrue($jwt['new-session']);
    }

    /**
     * @test
     */
    public function it_redirects_you_to_a_trusted_url_with_a_jwt()
    {
        $client = static::createClient();
        $this->logIn($client);

        $client->request('GET', '/submit?return_url='.urlencode('http://foo.elifesciences.org/path?query=arg'));
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $location = new Uri($response->headers->get('Location'));
        $query = parse_query($location->getQuery());
        $token = $query['token'];
        unset($query['token']);

        $this->assertSameUri('http://foo.elifesciences.org/path?query=arg', $location->withQuery(http_build_query($query)));

        $jwt = (array) JWT::decode($token, $this->getParameter('submission_client_secret'), ['HS256']);

        $this->assertFalse($jwt['new-session']);
    }

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
