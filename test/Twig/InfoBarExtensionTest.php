<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\InfoBarExtension;
use eLife\Patterns\PatternRenderer\CallbackPatternRenderer;
use eLife\Patterns\ViewModel\InfoBar;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;

final class InfoBarExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new InfoBarExtension(new CallbackPatternRenderer(function (InfoBar $infoBar) : string {
            return $infoBar['type'].':'.$infoBar['text'];
        }));

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_renders_an_info_bar()
    {
        $twigLoader = new ArrayLoader(['foo' => '{{info_bar("foo")}}|{{info_bar("bar", "success")}}']);
        $twig = new Environment($twigLoader);
        $twig->addExtension(new InfoBarExtension(new CallbackPatternRenderer(function (InfoBar $infoBar) : string {
            return $infoBar['type'].':'.$infoBar['text'];
        })));

        $this->assertSame('info:foo|success:bar', $twig->render('foo'));
    }
}
