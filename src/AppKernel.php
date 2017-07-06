<?php

namespace eLife\Journal;

use Bobthecow\Bundle\MustacheBundle\BobthecowMustacheBundle;
use Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle;
use Csa\Bundle\GuzzleBundle\CsaGuzzleBundle;
use eLife\Journal\Expression\ComposerLocateFunctionProvider;
use Irozgar\GulpRevVersionsBundle\IrozgarGulpRevVersionsBundle;
use Isometriks\Bundle\SpamBundle\IsometriksSpamBundle;
use Nelmio\SecurityBundle\NelmioSecurityBundle;
use PackageVersions\Versions;
use Sensio\Bundle\DistributionBundle\SensioDistributionBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle;

class AppKernel extends Kernel
{
    private $version;

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
    }

    public function registerBundles() : array
    {
        $bundles = [
            new AppBundle(),
            new BobthecowMustacheBundle(),
            new CocurSlugifyBundle(),
            new CsaGuzzleBundle(),
            new FrameworkBundle(),
            new IrozgarGulpRevVersionsBundle(),
            new IsometriksSpamBundle(),
            new MonologBundle(),
            new NelmioSecurityBundle(),
            new SwiftmailerBundle(),
            new TwigBundle(),
            new WhiteOctoberPagerfantaBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test', 'ci'], true)) {
            $bundles[] = new DebugBundle();
            $bundles[] = new SensioDistributionBundle();
            $bundles[] = new WebProfilerBundle();
        }

        return $bundles;
    }

    public function getName() : string
    {
        return 'journal';
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function getRootDir() : string
    {
        return __DIR__.'/../app';
    }

    public function getCacheDir() : string
    {
        return $this->getRootDir().'/../var/cache/'.$this->getEnvironment();
    }

    public function getLogDir() : string
    {
        return $this->getRootDir().'/../var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function run(Request $request)
    {
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    protected function buildContainer() : ContainerBuilder
    {
        $builder = parent::buildContainer();

        $builder->addExpressionLanguageProvider(new ComposerLocateFunctionProvider());

        return $builder;
    }
}
