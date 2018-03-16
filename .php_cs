<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('build')
    ->exclude('node_modules')
    ->exclude('var')
    ->name('console')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'simplified_null_return' => false,
        'ordered_imports' => true,
        'return_type_declaration' => ['space_before' => 'one'],
    ])
    ->setUsingCache(true)
;
