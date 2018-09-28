<?php

namespace test\eLife\Journal\Controller;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Uri;
use test\eLife\Journal\WebTestCase;

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
        $this->assertSameUri('/log-in', $response->headers->get('Location'));
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

        $redirectUri = new Uri($response->headers->get('Location'));

        $this->assertSameUri('http://submit.elifesciences.org/path', $redirectUri->withFragment(''));

        JWT::decode($redirectUri->getFragment(), $this->getParameter('xpub_client_secret'), ['HS256']);
    }

    protected static function createClient(array $options = [], array $server = [])
    {
        $client = parent::createClient($options, $server);

        $client->followRedirects(false);

        return $client;
    }
}
