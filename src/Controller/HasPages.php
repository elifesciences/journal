<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Helper\Paginator;
use GuzzleHttp\Promise\PromiseInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

trait HasPages
{
    public function paginator(PromiseInterface $pagerfantaPromise, Request $request, string $title, string $route)
    {
        return $pagerfantaPromise
            ->then(function (Pagerfanta $pagerfanta) use ($request, $title, $route) {
                return new Paginator(
                    $title,
                    $pagerfanta,
                    function (int $page = null) use ($request, $request, $route) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate($route, $routeParams);
                    }
                );
            });

    }
}
