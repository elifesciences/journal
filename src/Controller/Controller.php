<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    final protected function defaultPageArguments(): array
    {
        return [
            'header' => $this->get('elife.journal.view_model.factory.site_header')->createSiteHeader(),
            'footer' => $this->get('elife.journal.view_model.factory.footer')->createFooter(),
        ];
    }
}
