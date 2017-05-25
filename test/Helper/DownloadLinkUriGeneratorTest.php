<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use Symfony\Component\HttpKernel\UriSigner;
use test\eLife\Journal\KernelTestCase;
use UnexpectedValueException;

final class DownloadLinkUriGeneratorTest extends KernelTestCase
{
    /** @var DownloadLinkUriGenerator */
    private $downloadLinkUriGenerator;

    /** @var UriSigner */
    private $uriSigner;

    /** @var string */
    private $defaultBaseUrl;

    /**
     * @before
     */
    public function setUpDownloadLinkUriGenerator()
    {
        static::bootKernel();

        $container = static::$kernel->getContainer();
        $this->downloadLinkUriGenerator = $container->get('elife.journal.helper.download_link_uri_generator');
        $this->uriSigner = $container->get('uri_signer');
        $this->defaultBaseUrl =
            $container->getParameter('router.request_context.scheme')
            .'://'
            .$container->getParameter('router.request_context.host')
            .($container->getParameter('router.request_context.base_url')
                ? '/'.$container->getParameter('router.request_context.base_url')
                : '');
    }

    /**
     * @test
     */
    public function it_generates_a_uri()
    {
        $this->assertSame(
            $this->uriSigner->sign($this->defaultBaseUrl.'/download/aHR0cDovL3d3dy5leGFtcGxlLmNvbS90ZXN0LnR4dA==/foo.bar'),
            $this->downloadLinkUriGenerator->generate(new DownloadLink('http://www.example.com/test.txt', 'foo.bar'))
        );
    }

    /**
     * @test
     */
    public function it_checks_a_uri()
    {
        $this->assertEquals(new DownloadLink('http://www.example.com/test.txt', 'foo.bar'), $this->downloadLinkUriGenerator->check($this->uriSigner->sign('http://localhost/download/aHR0cDovL3d3dy5leGFtcGxlLmNvbS90ZXN0LnR4dA==/foo.bar')));
    }

    /**
     * @test
     */
    public function it_rejects_invalid_uris()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->downloadLinkUriGenerator->check('http://localhost/download/aHR0cDovL3d3dy5leGFtcGxlLmNvbS90ZXN0LnR4da==?_hash=uCm1Z0B%2FIH9%2FKn3icYBFW9ZhmguDotpC0Lp4j2vXmB0%3D');
    }
}
