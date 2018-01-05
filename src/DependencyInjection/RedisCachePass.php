<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

final class RedisCachePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('cache.default_redis_provider')) {
            return;
        }

        $redis = new DefinitionDecorator('cache.adapter.redis');
        $redis->setPublic(true);
        $redis->addTag('cache.pool', []);

        $container->setDefinition('cache.app', $redis);
    }
}
