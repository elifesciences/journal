<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MonologStacktracesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definitions = [
            $container->findDefinition('monolog.formatter.json'),
            $container->findDefinition('monolog.formatter.line'),
        ];

        foreach ($definitions as $definition) {
            $definition->addMethodCall('includeStacktraces');
        }
    }
}
