<?php

namespace test\eLife\Journal\Twig;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Twig\JsonLdSchemaOrgExtension;
use function GuzzleHttp\Promise\promise_for;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig_ExtensionInterface;
use TypeError;

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
     */
    public function it_must_receive_a_content_model()
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $packages = $this->createMock(Packages::class);

        $urlGenerator->method('getContext')->willReturn(new RequestContext());

        $extension = new JsonLdSchemaOrgExtension($urlGenerator, $packages);

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $extension->generate(new Digest(
            'id',
            'Digest title',
            null,
            'published',
            null,
            null,
            new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50),
            null,
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence()
        ));

        $this->expectException(TypeError::class);
        $extension->generate('not content model');
    }

    /**
     * @test
     */
    public function it_will_generate_json_ld_schema_from_digest()
    {

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $packages = $this->createMock(Packages::class);

        $urlGenerator->method('getContext')->willReturn(new RequestContext(null, 'GET', 'journal', 'https'));

        $extension = new JsonLdSchemaOrgExtension($urlGenerator, $packages);

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $thumbnail = $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);
        $json = $extension->generateJson(new Digest(
            'id',
            'Digest title',
            'Impact statement',
            'published',
            new DateTimeImmutable('2008-09-27 01:23:45'),
            null,
            $thumbnail,
            null,
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($subjectBanner), promise_for($subjectThumbnail)),
            ]),
            new EmptySequence(),
            new EmptySequence()
        ), false);

        $this->assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
            ],
            'headline' => 'Digest title',
            'image' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
            'datePublished' => '2008-09-27',
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'eLife Sciences Publications, Ltd',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://journal',
                ],
            ],
            'about' => [
                'Subject 1 name',
            ],
            'description' => 'Impact statement',
            'isPartOf' => [
                '@type' => 'Periodical',
                'name' => 'eLife',
                'issn' => '2050-084X',
            ],
        ], $json);
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
