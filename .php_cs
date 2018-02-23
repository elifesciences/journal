<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('build')
    ->exclude('node_modules')
    ->exclude('var')
    ->name('console')
;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(['-empty_return', 'ordered_use'])
    ->finder($finder)
    ->setUsingCache(true)
;
