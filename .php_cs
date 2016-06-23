<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('var')
    ->name('console')
;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(['-empty_return'])
    ->finder($finder)
;
