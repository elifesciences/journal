<?php

namespace eLife\Journal\DependencyInjection;

use Snc\RedisBundle\Session\Storage\Handler\RedisSessionHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

final class RedisPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('cache.default_redis_provider')) {
            return;
        }

        $this->setCache($container);
        $this->setSessionHandler($container);
    }

    private function setCache(ContainerBuilder $container)
    {
        $redis = new DefinitionDecorator('cache.adapter.redis');
        $redis->setPublic(true);
        $redis->addTag('cache.pool', []);

        $container->setDefinition('cache.app', $redis);
    }

    private function setSessionHandler(ContainerBuilder $container)
    {
        $sessionHandler = new Definition(RedisSessionHandler::class);
        $sessionHandler->addArgument($container->findDefinition('cache.default_redis_provider'));
        $sessionHandler->addArgument($container->getParameter('session.storage.options'));

        $container->setDefinition('session.handler', $sessionHandler);
    }
}
