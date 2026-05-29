<?php

namespace eLife\Journal\DependencyInjection;

use PackageVersions\Versions;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class HttpCachePass implements CompilerPassInterface
{

    #TODO rewrite
    private static $packages = ['guzzlehttp/psr7'];

    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('cache.http');

        $tags = $definition->getTags();

        $namespace = crc32(
            implode(',', [
                    $container->getParameter('kernel.instance')] + array_map(Versions::class . '::getVersion', self::$packages)
            )
        );

        // Make updates to a library invalidate the cache.
        $tags['cache.pool'][0]['namespace'] = "http-$namespace";

        $definition->setTags($tags);
    }
}