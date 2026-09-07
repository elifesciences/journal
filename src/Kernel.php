<?php

namespace eLife\Journal;

use eLife\Journal\Expression\ComposerLocateFunctionProvider;
use eLife\Journal\Expression\TimeFunctionProvider;
use PackageVersions\Versions;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private string $version;
    private ?string $instance;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $originalVersion = Versions::getVersion('elife/journal');
        list($version, $reference) = explode('@', $originalVersion);
        if (false !== strpos($version, 'dev')) {
            if (40 === strlen($reference)) {
                $version = implode('@', [$version, substr($reference, 0, 7)]);
            } else {
                $version = $originalVersion;
            }
        }

        $this->version = $version;
        $this->instance = getenv('JOURNAL_INSTANCE') ?: null;
    }

    public function getName(): string
    {
        return 'journal';
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getCacheDir(): string
    {
        return "{$this->getProjectDir()}/var/cache/{$this->getEnvironment()}{$this->instance}";
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/logs';
    }


    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $configDir = $this->getProjectDir().'/config';

        // web_profiler.yaml configures the WebProfilerBundle extension, which - like the bundle
        // itself in config/bundles.php - is only registered when the (require-dev-only) class is
        // actually installed. Loading its config when the bundle isn't there fails with
        // "Container extension \"web_profiler\" is not registered."
        foreach (glob($configDir.'/packages/*.{yaml,yml}', GLOB_BRACE) as $file) {
            if ('web_profiler.yaml' === basename($file) && !class_exists(WebProfilerBundle::class)) {
                continue;
            }
            $loader->load($file);
        }

        $envPackages = $configDir.'/packages/'.$this->getEnvironment();
        if (is_dir($envPackages)) {
            $loader->load($envPackages.'/*.{yaml,yml}', 'glob');
        }

        $loader->load($configDir.'/services.yaml');

        $envServices = $configDir.'/services_'.$this->getEnvironment().'.yaml';
        if (file_exists($envServices)) {
            $loader->load($envServices);
        }
    }

    protected function getContainerBuilder(): ContainerBuilder
    {
        $builder = parent::getContainerBuilder();

        $builder->addExpressionLanguageProvider(new ComposerLocateFunctionProvider());
        $builder->addExpressionLanguageProvider(new TimeFunctionProvider());

        return $builder;
    }

    protected function buildContainer(): ContainerBuilder
    {
        $builder = parent::buildContainer();

        $builder->setParameter('kernel.instance', $this->instance ?? '');

        return $builder;
    }

    public function run(Request $request): void
    {
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }
}
