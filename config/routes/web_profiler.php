<?php

use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

// WebProfilerBundle is require-dev only (see config/bundles.php), so its routes can only be
// imported where it's actually installed - the full dev-dependency "ci" test container and
// local dev, not the production image (also booted under APP_ENV=ci for its mock-API config
// when it gets smoke-tested).
return static function (RoutingConfigurator $routes): void {
    if (!class_exists(WebProfilerBundle::class) || !in_array($routes->env(), ['dev', 'ci'], true)) {
        return;
    }

    $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.php')->prefix('/_wdt');
    $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.php')->prefix('/_profiler');
};
