<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class OAuthClientPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('hwi_oauth.http_client')->getArgument(0)->getArgument(0)
            ->replaceArgument(0, $container->getDefinition('csa_guzzle.client.oauth'));
    }
}
