<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class OAuthClientPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->findDefinition('knpu.oauth2.provider.elife')
            ->addMethodCall('setHttpClient', [$container->findDefinition('csa_guzzle.client.oauth')]);
    }
}
