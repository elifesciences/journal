<?php

namespace eLife\Journal\DependencyInjection;

use PackageVersions\Versions;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class HttpCachePass implements CompilerPassInterface
{
    private static $packages = ['guzzlehttp/psr7', 'kevinrob/guzzle-cache-middleware'];

    public function process(ContainerBuilder $container)
    {
        $seed = $container->getParameter('cache.prefix.seed');

        $definition = $container->findDefinition('cache.guzzle');

        $tags = $definition->getTags();

        $namespace = crc32(implode(',', array_map(Versions::class.'::getVersion', self::$packages)));

        // Make updates to a library invalidate the cache.
        $tags['cache.pool'][0]['namespace'] = "$seed-http-$namespace";

        $definition->setTags($tags);
    }
}
