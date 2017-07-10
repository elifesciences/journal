<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RedisCachePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('cache.default_redis_provider')) {
            $container->setDefinition('cache.app', $container->findDefinition('cache.adapter.redis'));
        }
    }
}
