<?php

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class Context extends RawMinkContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    final public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    final protected function mockApiResponse(RequestInterface $request, ResponseInterface $response)
    {
        $this->kernel->getContainer()
            ->get('elife.guzzle.middleware.mock.storage')
            ->save($request, $response);
    }

    /**
     * @BeforeScenario
     */
    final public function clearMockedApiResponses()
    {
        (new Filesystem())->remove($this->kernel->getContainer()->getParameter('api_mock'));
    }

    final protected function spin(callable $lambda, int $wait = 10)
    {
        $e = null;
        for ($i = 0; $i < $wait; ++$i) {
            try {
                $lambda();

                return;
            } catch (Exception $e) {
                // Do nothing.
            }

            sleep(1);
        }

        throw new Exception('Timeout: '.$e->getMessage(), -1, $e);
    }

    final protected function isJavaScript()
    {
        try {
            return $this->getSession()->evaluateScript('true');
        } catch (UnsupportedDriverActionException $e) {
            return false;
        }
    }
}
