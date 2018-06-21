<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function file_get_contents;
use function GuzzleHttp\json_decode;
use function is_readable;

final class PreloadPass implements CompilerPassInterface
{
    const FILE = __DIR__.'/../../build/assets/patterns/preload.json';

    public function process(ContainerBuilder $container)
    {
        if (is_readable(self::FILE)) {
            $container->addResource(new FileResource(self::FILE));
            $preloads = json_decode(file_get_contents(self::FILE), true);
        }

        $container->setParameter('preload_links', $preloads ?? []);
    }
}
