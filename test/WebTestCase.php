<?php

namespace test\eLife\Journal;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class WebTestCase extends BaseWebTestCase
{
    use AppKernelTestCase;

    final protected function logIn(Client $client)
    {
        $client->request('GET', '/?open-sesame');

        $session = $client->getContainer()->get('session');

        $token = new OAuthToken(
            [
                'access_token' => 'token',
                'expires_in' => 3920,
                'token_type' => 'Bearer',
                'id' => 'jcarberry',
                'orcid' => '0000-0002-1825-0097',
                'name' => 'Josiah Carberry',
            ],
            ['ROLE_USER', 'ROLE_OAUTH_USER']
        );
        $token->setUser(new OAuthUser('jcarberry'));

        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
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
}
