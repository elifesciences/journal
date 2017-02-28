<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use Symfony\Component\HttpKernel\UriSigner;
use test\eLife\Journal\KernelTestCase;
use Traversable;
use UnexpectedValueException;

final class DownloadLinkUriGeneratorTest extends KernelTestCase
{
    /** @var DownloadLinkUriGenerator */
    private $downloadLinkUriGenerator;

    /** @var UriSigner */
    private $uriSigner;

    /**
     * @before
     */
    public function setUpDownloadLinkUriGenerator()
    {
        static::bootKernel();

        $this->downloadLinkUriGenerator = static::$kernel->getContainer()->get('elife.journal.helper.download_link_uri_generator');
        $this->uriSigner = static::$kernel->getContainer()->get('uri_signer');
    }

    /**
     * @test
     * @dataProvider validProvider
     */
    public function it_generates_a_uri(DownloadLink $downloadLink, string $expected)
    {
        $this->assertSame($this->uriSigner->sign($expected), $this->downloadLinkUriGenerator->generate($downloadLink));
    }

    /**
     * @test
     * @dataProvider validProvider
     */
    public function it_checks_a_uri(DownloadLink $expected, string $uri)
    {
        $this->assertEquals($expected, $this->downloadLinkUriGenerator->check($this->uriSigner->sign($uri)));
    }

    /**
     * @test
     */
    public function it_rejects_invalid_uris()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->downloadLinkUriGenerator->check('http://localhost/download/aHR0cDovL3d3dy5leGFtcGxlLmNvbS90ZXN0LnR4da==?_hash=uCm1Z0B%2FIH9%2FKn3icYBFW9ZhmguDotpC0Lp4j2vXmB0%3D');
    }

    public function validProvider() : Traversable
    {
        yield 'no file name' => [new DownloadLink('http://www.example.com/test.txt'), 'http://localhost/download/aHR0cDovL3d3dy5leGFtcGxlLmNvbS90ZXN0LnR4dA=='];
        yield 'with file name' => [new DownloadLink('http://www.example.com/test.txt', 'foo.bar'), 'http://localhost/download/aHR0cDovL3d3dy5leGFtcGxlLmNvbS90ZXN0LnR4dA==/foo.bar'];
    }
}
