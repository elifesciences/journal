<?php

namespace eLife\Journal\Router;

use BadMethodCallException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

final class ParameterResolvingRouter implements RouterInterface, RequestMatcherInterface
{
    private $router;
    private $parameterResolver;

    public function __construct(RouterInterface $router, ParameterResolver $parameterResolver)
    {
        $this->router = $router;
        $this->parameterResolver = $parameterResolver;
    }

    public function setContext(RequestContext $context)
    {
        return $this->router->setContext($context);
    }

    public function getContext()
    {
        return $this->router->getContext();
    }

    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($name, $this->parameterResolver->resolve($name, $parameters), $referenceType);
    }

    public function match($pathinfo)
    {
        return $this->router->match($pathinfo);
    }

    public function matchRequest(Request $request)
    {
        if (!$this->router instanceof RequestMatcherInterface) {
            throw new BadMethodCallException('Router does not implement '.RequestMatcherInterface::class);
        }

        return $this->router->matchRequest($request);
    }
}
