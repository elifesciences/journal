<?php

namespace eLife\Journal;

use eLife\Journal\DependencyInjection\MonologConverterPass;
use eLife\Journal\DependencyInjection\ViewModelConverterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MonologConverterPass());
        $container->addCompilerPass(new ViewModelConverterPass());
    }
}
