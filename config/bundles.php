<?php

$bundles = [
    eLife\Journal\AppBundle::class => ['all' => true],
    Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle::class => ['all' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    BabDev\PagerfantaBundle\BabDevPagerfantaBundle::class => ['all' => true],
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => ['all' => true],
    Nelmio\SecurityBundle\NelmioSecurityBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
];

// symfony/debug-bundle and symfony/web-profiler-bundle are require-dev only, so they're absent
// from production vendor builds. Only register them where they're actually installed (the full
// dev-dependency "ci" test container and local dev) - the production image is also booted under
// APP_ENV=ci (for its mock-API config) when it gets smoke-tested, and doesn't have them.
if (class_exists(Symfony\Bundle\DebugBundle\DebugBundle::class)) {
    $bundles[Symfony\Bundle\DebugBundle\DebugBundle::class] = ['ci' => true, 'dev' => true];
}
if (class_exists(Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class)) {
    $bundles[Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class] = ['ci' => true, 'dev' => true];
}

return $bundles;
