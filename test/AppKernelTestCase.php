<?php

namespace test\eLife\Journal;

use eLife\Journal\AppKernel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;

trait AppKernelTestCase
{
    final protected static function bootKernel(array $options = [])
    {
        parent::bootKernel($options);

        (new Filesystem())->remove(static::$kernel->getContainer()->getParameter('api_mock'));
        static::$kernel->getContainer()->get('cache.guzzle')->clear();

        return static::$kernel;
    }

    protected static function createKernel(array $options = [])
    {
        return new AppKernel($options['environment'] ?? 'test', $options['debug'] ?? true, getenv('TEST_TOKEN'));
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
