<?php

namespace test\eLife\Journal;

use eLife\Journal\Security\User\OAuthUser;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
        $client->request('GET', '/?open-sesame');

        $session = $client->getContainer()->get('session');

        $token = new PostAuthenticationGuardToken(new OAuthUser('jcarberry', $roles = ['ROLE_USER', 'ROLE_OAUTH_USER']), 'main', $roles);

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
