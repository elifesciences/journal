<?php

namespace eLife\Journal\DependencyInjection;

use Redis;
use Snc\RedisBundle\Session\Storage\Handler\RedisSessionHandler;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
        $sessionHandler->addArgument($container->getParameter('session.storage.options'));

        $container->setDefinition('session.handler', $sessionHandler);
    }
}
