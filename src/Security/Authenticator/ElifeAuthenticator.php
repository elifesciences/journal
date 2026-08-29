<?php

namespace eLife\Journal\Security\Authenticator;

use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Throwable;

final class ElifeAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
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

    public function supports(Request $request) : ?bool
    {
        return 'log-in-check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request) : Passport
    {
        try {
            $accessToken = $this->fetchAccessToken($this->getClient());
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AuthenticationException($e->getMessage(), 0, $e);
        }

        $id = $this->getClient()->fetchUserFromToken($accessToken)->getId();

        return new SelfValidatingPassport(new UserBadge($id));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName) : Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            $this->removeTargetPath($request->getSession(), $firewallName);

            try {
                $uri = new Uri($targetPath);
                if (!Uri::isAbsolute($uri)) {
                    $targetPath = null;
                }
            } catch (InvalidArgumentException $e) {
                $targetPath = null;
            }
        }

        return $this->httpUtils->createRedirectResponse($request, $targetPath ?? '/');
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
