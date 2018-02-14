<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class GuzzleMiddlewarePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach (['elife_crm', 'file_download', 'oauth'] as $id) {
            $handler = $container->getDefinition("csa_guzzle.client.$id")->getArgument(0)['handler'];

            $handler->setMethodCalls(array_filter($handler->getMethodCalls(), function (array $methodCall) {
                return 'push' !== $methodCall[0] || !in_array($methodCall[1][1], ['cache', 'subject_rewriting']);
            }));
        }
    }
}
