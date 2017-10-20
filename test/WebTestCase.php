<?php

namespace test\eLife\Journal;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class WebTestCase extends BaseWebTestCase
{
    use AppKernelTestCase;

    final protected function logIn(Client $client)
    {
        $session = $client->getContainer()->get('session');

        $token = new OAuthToken(
            [
                'access_token' => 'token',
                'expires_in' => 3920,
                'token_type' => 'Bearer',
            ],
            ['ROLE_USER', 'ROLE_OAUTH_USER']
        );

        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
