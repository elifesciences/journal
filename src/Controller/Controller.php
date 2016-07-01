<?php

namespace eLife\Journal\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Controller implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    final public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    final public function get(string $id)
    {
        return $this->container->get($id);
    }

    final public function has(string $id) : bool
    {
        return $this->container->has($id);
    }

    final protected function defaultPageArguments() : array
    {
        return [
            'header' => $this->get('elife.journal.view_model.factory.site_header')->createSiteHeader(),
            'footer' => $this->get('elife.journal.view_model.factory.footer')->createFooter(),
        ];
    }
}
