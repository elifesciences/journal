<?php

namespace eLife\Journal\DependencyInjection;

use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

final class RedisSessionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('redis_sessions')) {
            return;
        }

        $sessionRedis = new Definition(Redis::class);
        $sessionRedis->setFactory(RedisAdapter::class.'::createConnection');
        $sessionRedis->addArgument($container->getParameter('redis_sessions'));

        $container->setDefinition('elife.journal.session.redis', $sessionRedis);

        $sessionHandler = new Definition(RedisSessionHandler::class);
        $sessionHandler->addArgument($container->findDefinition('elife.journal.session.redis'));
        $sessionHandler->addArgument(['ttl' => $container->getParameter('session.storage.options')['cookie_lifetime'] ?? null]);

        $container->setDefinition('session.handler', $sessionHandler);
    }
}
