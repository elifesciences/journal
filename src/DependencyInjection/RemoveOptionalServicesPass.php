<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function array_keys;
use function class_exists;

final class RemoveOptionalServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach (array_keys($container->findTaggedServiceIds('optional')) as $id) {
            if (!class_exists($container->getDefinition($id)->getClass())) {
                $container->removeDefinition($id);
            }
        }
    }
}
