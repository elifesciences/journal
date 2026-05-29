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

        return static::$kernel;
    }

    final protected static function createKernel(array $options = [])
    {
        $kernel = parent::createKernel($options);

        if (!$kernel->isDebug()) {
            (new Filesystem())->remove($kernel->getCacheDir());
        }

        return $kernel;
    }

    final protected static function mockApiResponse(RequestInterface $request, ResponseInterface $response)
    {
        $container = static::$kernel->getContainer();
        $container->get('framework.http_client.clients.elife_api')->save($request, $response);
        $container->get('framework.http_client.clients.elife_api_search_page')->save($request, $response);
        $container->get('framework.http_client.clients.streamer')->save($request, $response);
        $container->get('framework.http_client.clients.oauth')->save($request, $response);
    }

    final protected function getParameter(string $parameter)
    {
        return static::$kernel->getContainer()->getParameter($parameter);
    }
}
