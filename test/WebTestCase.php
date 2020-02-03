<?php

namespace test\eLife\Journal;

use function GuzzleHttp\json_encode;
use function GuzzleHttp\Psr7\build_query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

abstract class WebTestCase extends BaseWebTestCase
{
    use AppKernelTestCase;
    use Assertions;

    final protected function logIn(Client $client)
    {
        $session = $client->getContainer()->get('session');

        $token = new PostAuthenticationGuardToken(new OAuthUser('jcarberry', $roles = ['ROLE_USER', 'ROLE_OAUTH_USER']), 'main', $roles);

        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId(), null, null, 'localhost');
        $client->getCookieJar()->set($cookie);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/profiles/jcarberry',
                ['Accept' => 'application/vnd.elife.profile+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.profile+json; version=1'],
                json_encode([
                    'id' => 'jcarberry',
                    'name' => [
                        'preferred' => 'Josiah Carberry',
                        'index' => 'Carberry, Josiah',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                ])
            )
        );
    }

    final protected function readyToken()
    {
        $this->mockApiResponse(
            new Request(
                'POST',
                'http://api.elifesciences.org/oauth2/token',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                build_query(['code' => 'foo', 'grant_type' => 'authorization_code', 'client_id' => 'journal--local-id', 'client_secret' => 'journal--local-secret', 'redirect_uri' => 'http://localhost/log-in/check'])
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'access_token' => 'token',
                    'expires_in' => 3920,
                    'token_type' => 'Bearer',
                    'id' => 'jcarberry',
                    'orcid' => '0000-0002-1825-0097',
                    'name' => 'Josiah Carberry',
                ])
            )
        );
    }
}
