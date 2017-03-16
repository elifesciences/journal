<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\DownloadLink;
use PHPUnit_Framework_TestCase;

final class DownloadLinkTest extends PHPUnit_Framework_TestCase
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
    public function it_can_be_created_from_a_uri()
    {
        $link = DownloadLink::fromUri('http://www.example.com/test.txt');

        $this->assertSame('http://www.example.com/test.txt', $link->getUri());
        $this->assertSame('test.txt', $link->getFilename());
    }
}
