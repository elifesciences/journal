<?php

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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

    /**
     * @BeforeScenario
     */
    final public function mockSubjectsForFooter()
    {
        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page=1&per-page=50&order=asc',
                [
                    'Accept' => 'application/vnd.elife.subject-list+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.subject-list+json; version=1',
                ],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'id' => 'subject',
                            'name' => 'Subject',
                            'impactStatement' => 'Subject impact statement.',
                            'image' => [
                                'alt' => '',
                                'sizes' => [
                                    '2:1' => [
                                        900 => 'https://placehold.it/900x450',
                                        1800 => 'https://placehold.it/1800x900',
                                    ],
                                    '16:9' => [
                                        250 => 'https://placehold.it/250x141',
                                        500 => 'https://placehold.it/500x281',
                                    ],
                                    '1:1' => [
                                        70 => 'https://placehold.it/70x70',
                                        140 => 'https://placehold.it/140x140',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    final protected function spin(callable $lambda, int $wait = 10)
    {
        for ($i = 0; $i < $wait; ++$i) {
            try {
                if ($lambda()) {
                    return true;
                }
            } catch (Exception $e) {
                // Do nothing.
            }

            sleep(1);
        }

        $backtrace = debug_backtrace();

        throw new Exception(
            'Timeout thrown by '.$backtrace[1]['class'].'::'.$backtrace[1]['function']."()\n".
            $backtrace[1]['file'].', line '.$backtrace[1]['line']
        );
    }
}
