<?php

namespace test\eLife\Journal;

use eLife\Journal\AppKernel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;

trait AppKernelTestCase
{
    final protected static function getKernelClass() : string
    {
        return AppKernel::class;
    }

    final protected static function bootKernel(array $options = [])
    {
        parent::bootKernel($options);

        (new Filesystem())->remove(static::$kernel->getContainer()->getParameter('api_mock'));
        static::$kernel->getContainer()->get('cache.guzzle')->clear();

        return static::$kernel;
    }

    protected static function createKernel(array $options = [])
    {
        $kernel = parent::createKernel($options);

        if (!$kernel->isDebug()) {
            (new Filesystem())->remove($kernel->getCacheDir());
        }

        return $kernel;
    }

    final protected static function mockApiResponse(RequestInterface $request, ResponseInterface $response)
    {
        static::$kernel->getContainer()
            ->get('elife.guzzle.middleware.mock.storage')
            ->save($request, $response);
    }

    final protected function getParameter(string $parameter)
    {
        return static::$kernel->getContainer()->getParameter($parameter);
    }
}
