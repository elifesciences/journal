<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\JsonLdSchemaOrgExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_ExtensionInterface;

final class JsonLdSchemaOrgExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $packages = $this->createMock(Packages::class);

        $extension = new JsonLdSchemaOrgExtension($urlGenerator, $packages);

        $this->assertInstanceOf(Twig_ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_turns_model_into_json_ld_schema()
    {
        $this->assertTrue(true);
    }
}
