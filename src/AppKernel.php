<?php

namespace eLife\Journal;

use Bobthecow\Bundle\MustacheBundle\BobthecowMustacheBundle;
use Csa\Bundle\GuzzleBundle\CsaGuzzleBundle;
use Puli\SymfonyBundle\PuliBundle;
use Sensio\Bundle\DistributionBundle\SensioDistributionBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles() : array
    {
        $bundles = [
            new AppBundle(),
            new BobthecowMustacheBundle(),
            new CsaGuzzleBundle(),
            new FrameworkBundle(),
            new MonologBundle(),
            new PuliBundle(),
            new TwigBundle(),
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
}
