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
     * @before
     */
    public function enableFeatureFlag()
    {
        $_ENV['FEATURE_XPUB'] = true;
    }

    /**
     * @test
     */
    public function it_does_not_redirect_if_the_feature_flag_is_disabled()
    {
        $_ENV['FEATURE_XPUB'] = false;

        $client = static::createClient();

        $client->request('GET', '/submit');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_requires_you_to_be_logged_in()
    {
        $client = static::createClient();

        $client->request('GET', '/submit');
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $location = Uri::withoutQueryValue(new Uri($response->headers->get('Location')), 'state');
        $this->assertSameUri('http://api.elifesciences.org/oauth2/authorize?response_type=code&client_id=journal_client_id&redirect_uri=http%3A%2F%2Flocalhost%2Flog-in%2Fcheck', $location);

        $state = parse_query((new Uri($response->headers->get('Location')))->getQuery())['state'];

        $this->readyToken();

        $client->request('GET', "/log-in/check?code=foo&state={$state}");
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $this->assertSameUri('http://localhost/submit', $response->headers->get('Location'));

        $client->followRedirect();
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $location = new Uri($response->headers->get('Location'));

        $this->assertSameUri('http://submit.elifesciences.org/path', $location->withFragment(''));

        JWT::decode($location->getFragment(), $this->getParameter('xpub_client_secret'), ['HS256']);
    }

    /**
     * @test
     */
    public function it_redirects_you_to_xpub_with_a_jwt()
    {
        $client = static::createClient();
        $this->logIn($client);

        $client->request('GET', '/submit');
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $location = new Uri($response->headers->get('Location'));

        $this->assertSameUri('http://submit.elifesciences.org/path', $location->withFragment(''));

        JWT::decode($location->getFragment(), $this->getParameter('xpub_client_secret'), ['HS256']);
    }

    protected static function createClient(array $options = [], array $server = [])
    {
        $client = parent::createClient($options, $server);

        $client->followRedirects(false);

        return $client;
    }
}
