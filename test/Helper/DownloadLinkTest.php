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
        $link = new DownloadLink('http://www.example.com/test.txt');

        $this->assertSame('http://www.example.com/test.txt', $link->getUri());
    }

    /**
     * @test
     */
    public function it_may_have_a_filename()
    {
        $with = new DownloadLink('http://www.example.com/test.txt', 'foo.bar');
        $withOut = new DownloadLink('http://www.example.com/test.txt');

        $this->assertSame('foo.bar', $with->getFilename());
        $this->assertNull($withOut->getFilename());
    }
}
