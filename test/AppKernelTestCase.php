<?php

namespace test\eLife\Journal;

use eLife\Journal\Kernel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

trait AppKernelTestCase
{
    private static bool $kernelBootedInCurrentTest = false;

    final protected static function getKernelClass() : string
    {
        return Kernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (static::$kernelBootedInCurrentTest) {
            static::$kernelBootedInCurrentTest = false;
            restore_exception_handler();
        }
    }

    final protected static function bootKernel(array $options = []) : KernelInterface
    {
        parent::bootKernel($options);

        // FrameworkBundle::boot() calls ErrorHandler::register() which installs a
        // PHP exception handler via set_exception_handler() without restoring it.
        // PHPUnit 10+ fails tests that increase the handler stack depth. We track
        // the boot so tearDown() can restore the handler.
        static::$kernelBootedInCurrentTest = true;

        (new Filesystem())->remove(static::$kernel->getContainer()->getParameter('api_mock'));

        return static::$kernel;
    }

    final protected static function createKernel(array $options = []) : KernelInterface
    {
        $kernel = parent::createKernel($options);

        if (!$kernel->isDebug()) {
            (new Filesystem())->remove($kernel->getCacheDir());
        }

        return $kernel;
    }

    final protected static function mockApiResponse(RequestInterface $request, ResponseInterface $response): void
    {
        // Scoped http clients are auto-decorated with UriTemplateHttpClient by
        // FrameworkExtension, so the plain service id resolves to the decorator;
        // the ".uri_template.inner" id reaches the MockApiHttpClient underneath.
        // getContainer() (not $kernel->getContainer()) is required because that
        // inner id is private.
        $container = static::getContainer();
        $container->get('elife_api.uri_template.inner')->save($request, $response);
        $container->get('elife_api_search_page.uri_template.inner')->save($request, $response);
        $container->get('streamer.uri_template.inner')->save($request, $response);
        $container->get('oauth.uri_template.inner')->save($request, $response);
    }

    final protected function getParameter(string $parameter)
    {
        return static::$kernel->getContainer()->getParameter($parameter);
    }
}
