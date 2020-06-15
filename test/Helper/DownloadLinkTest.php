<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\DownloadLink;
use PHPUnit\Framework\TestCase;

final class DownloadLinkTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $link = new DownloadLink('http://www.example.com/test.txt', 'test.txt');

        $this->assertSame('http://www.example.com/test.txt', $link->getUri());
    }

    /**
     * @test
     */
    public function it_has_a_filename()
    {
        $link = new DownloadLink('http://www.example.com/test.txt', 'foo.bar');

        $this->assertSame('foo.bar', $link->getFilename());
    }

    /**
     * @test
     */
    public function it_may_have_a_canonical_uri()
    {
        $with = new DownloadLink('http://www.example.com/test.txt?canonicalUri=http://www.example.com/canonical', 'foo.bar');
        $withOut = new DownloadLink('http://www.example.com/test.txt', 'foo.bar');

        $this->assertSame('http://www.example.com/canonical', $with->getCanonicalUri());
        $this->assertNull($withOut->getCanonicalUri());
    }

    /**
     * @test
     */
    public function it_can_be_created_from_a_uri()
    {
        $link = DownloadLink::fromUri('http://www.example.com/test.txt');

        $this->assertSame('http://www.example.com/test.txt', $link->getUri());
        $this->assertSame('test.txt', $link->getFilename());
    }

    /**
     * @test
     */
    public function it_can_be_created_from_a_uri_and_rel_canonical()
    {
        $link = DownloadLink::fromUri('http://www.example.com/test.txt?canonicalUri=http://www.example.com/canonical');

        $this->assertSame('http://www.example.com/test.txt', $link->getUri());
        $this->assertSame('test.txt', $link->getFilename());
        $this->assertSame('http://www.example.com/canonical', $link->getCanonicalUri());
    }
}
