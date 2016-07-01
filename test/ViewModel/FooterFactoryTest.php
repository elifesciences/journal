<?php

namespace test\eLife\Journal\ViewModel;

use eLife\Journal\ViewModel\FooterFactory;
use eLife\Patterns\ViewModel\Footer;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

final class FooterFactoryTest extends KernelTestCase
{
    /**
     * @var FooterFactory
     */
    private $footerFactory;

    /**
     * @before
     */
    public function createFooterFactory()
    {
        static::bootKernel();
        (new Filesystem())->remove(static::$kernel->getContainer()->getParameter('api_mock'));

        static::$kernel->getContainer()
            ->get('elife.guzzle.middleware.mock.storage')
            ->save(
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
                                            250 => 'https:\/\/placehold.it\/250x141',
                                            500 => 'https:\/\/placehold.it\/500x281',
                                        ],
                                        '1:1' => [
                                            70 => 'https://placehold.it\/70x70',
                                            140 => 'https://placehold.it/140x140',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ])
                )
            );

        $this->footerFactory = static::$kernel->getContainer()->get('elife.journal.view_model.factory.footer');
    }

    /**
     * @test
     */
    public function it_returns_a_promise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->footerFactory->createFooter());
    }

    /**
     * @test
     * @depends it_returns_a_promise
     */
    public function it_returns_a_footer()
    {
        $footer = $this->footerFactory->createFooter()->wait();

        $this->assertInstanceOf(Footer::class, $footer);
    }
}
