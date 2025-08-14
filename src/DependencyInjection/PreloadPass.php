<?php

namespace eLife\Journal\DependencyInjection;

use ComposerLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function file_get_contents;
use function GuzzleHttp\json_decode;

final class PreloadPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $file = '/srv/journal/src/patterns/resources/assets/preload.json';

        $container->addResource(new FileResource($file));
        $preloads = json_decode(file_get_contents($file), true);

        $container->setParameter('preload_links', $preloads);
    }
}
