<?php

namespace eLife\Journal\Router;

use BadMethodCallException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class ParameterResolvingRouter implements RouterInterface, RequestMatcherInterface, WarmableInterface
{
    private $router;
    private $parameterResolver;

    public function __construct(RouterInterface $router, ParameterResolver $parameterResolver)
    {
        $this->router = $router;
        $this->parameterResolver = $parameterResolver;
    }

    public function setContext(RequestContext $context) : void
    {
        $this->router->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->router->getRouteCollection();
    }

    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->router->generate($name, $this->parameterResolver->resolve($name, $parameters), $referenceType);
    }

    public function match(string $pathinfo): array
    {
        return $this->router->match($pathinfo);
    }

    public function matchRequest(Request $request): array
    {
        if (!$this->router instanceof RequestMatcherInterface) {
            throw new BadMethodCallException('Router does not implement '.RequestMatcherInterface::class);
        }

        return $this->router->matchRequest($request);
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        if (!$this->router instanceof WarmableInterface) {
            return [];
        }

        return $this->router->warmUp($cacheDir);
    }
}
