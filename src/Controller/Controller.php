<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Model;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

abstract class Controller implements ContainerAwareInterface
{
    use CanConvertContent;

    /**
     * @var ContainerInterface
     */
    private $container;

    final public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    final protected function get(string $id)
    {
        return $this->container->get($id);
    }

    final protected function has(string $id): bool
    {
        return $this->container->has($id);
    }

    final protected function getViewModelConverter(): ViewModelConverter
    {
        return $this->get('elife.journal.view_model.converter');
    }

    final protected function render(ViewModel ...$viewModels): string
    {
        return $this->get('elife.patterns.pattern_renderer')->render(...$viewModels);
    }

    final protected function createSubsequentPage(Request $request, array $arguments) : Response
    {
        if ($request->isXmlHttpRequest()) {
            $response = new Response($this->render($arguments['listing']->wait()));
        } else {
            $arguments['contentHeader'] = $arguments['paginator']
                ->then(function (Paginator $paginator) {
                    return new ContentHeaderSimple(
                        $paginator->getTitle(),
                        sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                    );
                });

            $template = $arguments['listing']->wait() instanceof ViewModel\GridListing ? '::pagination-grid.html.twig' : '::pagination.html.twig';

            $response = new Response($this->get('templating')->render($template, $arguments));
        }

        $response->headers->set('Vary', 'X-Requested-With', false);

        return $response;
    }

    final protected function defaultPageArguments(PromiseInterface $model = null): array
    {
        return [
            'header' => promise_for($model)->then(function (Model $model = null) : ViewModel\SiteHeader {
                return $this->get('elife.journal.view_model.factory.site_header')->createSiteHeader($model);
            }),
            'footer' => $this->get('elife.journal.view_model.factory.footer')->createFooter(),
        ];
    }
}
