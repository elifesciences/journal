<?php

namespace eLife\Journal\Security\Authenticator;

use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Throwable;

final class ElifeAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;

    private $clientRegistry;
    private $urlGenerator;
    private $httpUtils;

    public function __construct(ClientRegistry $clientRegistry, UrlGeneratorInterface $urlGenerator, HttpUtils $httpUtils)
    {
        $this->clientRegistry = $clientRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->httpUtils = $httpUtils;
    }

    public function supports(Request $request) : bool
    {
        return 'log-in-check' === $request->attributes->get('_route');
    }

    public function getCredentials(Request $request) : AccessToken
    {
        try {
            return $this->fetchAccessToken($this->getClient());
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AuthenticationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param AccessToken $credentials
     */
    public function getUser($credentials, UserProviderInterface $userProvider) : UserInterface
    {
        return $userProvider->loadUserByUsername($this->getClient()->fetchUserFromToken($credentials)->getId());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) : Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            $this->removeTargetPath($request->getSession(), $providerKey);

            try {
                $targetPath = new Uri($targetPath);
            } catch (InvalidArgumentException $e) {
                $targetPath = null;
            }

            if ($targetPath && !Uri::isAbsolute($targetPath)) {
                $targetPath = null;
            }
        }

        return $this->httpUtils->createRedirectResponse($request, $targetPath ?? 'home');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : Response
    {
        $this->saveAuthenticationErrorToSession($request, $exception);

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function start(Request $request, AuthenticationException $authException = null) : Response
    {
        return $this->getClient()->redirect();
    }

    private function getClient() : OAuth2Client
    {
        return $this->clientRegistry->getClient('elife');
    }
}
