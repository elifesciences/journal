<?php

namespace eLife\Journal;

use eLife\Journal\DependencyInjection\GuzzleMiddlewarePass;
use eLife\Journal\DependencyInjection\MonologStacktracesPass;
use eLife\Journal\DependencyInjection\ViewModelConverterPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GuzzleMiddlewarePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);
        $container->addCompilerPass(new MonologStacktracesPass());
        $container->addCompilerPass(new ViewModelConverterPass());
    }
}
