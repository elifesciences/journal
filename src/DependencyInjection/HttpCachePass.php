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
        $definition = $container->findDefinition('cache.guzzle');

        $tags = $definition->getTags();

        $namespace = crc32(implode(',', array_map(Versions::class . '::getVersion', self::$packages)));

        // Make updates to library invalidate the cache.
        $tags['cache.pool'][0]['namespace'] = "http-$namespace";

        // Add a long-term default so entries can be evicted.
        $tags['cache.pool'][0]['default_lifetime'] = 60 * 60 * 24 * 365;

        $definition->setTags($tags);
    }
}
