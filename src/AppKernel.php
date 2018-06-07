<?php

namespace eLife\Journal;

use Bobthecow\Bundle\MustacheBundle\BobthecowMustacheBundle;
use Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle;
use Csa\Bundle\GuzzleBundle\CsaGuzzleBundle;
use eLife\Journal\Expression\ComposerLocateFunctionProvider;
use eLife\Journal\Expression\TimeFunctionProvider;
use Isometriks\Bundle\SpamBundle\IsometriksSpamBundle;
use KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle;
use Nelmio\SecurityBundle\NelmioSecurityBundle;
use PackageVersions\Versions;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Bundle\WebServerBundle\WebServerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle;

class AppKernel extends Kernel
{
    private $version;
    private $instance;

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

    public function registerBundles()
    {
        $bundles = [
            new AppBundle(),
            new BobthecowMustacheBundle(),
            new CocurSlugifyBundle(),
            new CsaGuzzleBundle(),
            new FrameworkBundle(),
            new IsometriksSpamBundle(),
            new KnpUOAuth2ClientBundle(),
            new MonologBundle(),
            new NelmioSecurityBundle(),
            new SecurityBundle(),
            new SwiftmailerBundle(),
            new TwigBundle(),
            new WhiteOctoberPagerfantaBundle(),
        ];

        if (in_array($this->getEnvironment(), ['ci', 'dev'], true)) {
            $bundles[] = new DebugBundle();
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new WebServerBundle();
        }

        return $bundles;
    }

    public function getName()
    {
        return 'journal';
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getRootDir()
    {
        return $this->getProjectDir().'/app';
    }

    public function getProjectDir()
    {
        return __DIR__.'/..';
    }

    public function getCacheDir()
    {
        return "{$this->getProjectDir()}/var/cache/{$this->getEnvironment()}{$this->instance}";
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getProjectDir().'/app/config/config_'.$this->getEnvironment().'.yml');
    }

    public function run(Request $request)
    {
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    protected function buildContainer()
    {
        $builder = parent::buildContainer();

        $builder->addExpressionLanguageProvider(new ComposerLocateFunctionProvider());
        $builder->addExpressionLanguageProvider(new TimeFunctionProvider());

        $builder->setParameter('kernel.instance', $this->instance ?? '');

        return $builder;
    }
}
