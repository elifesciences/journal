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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig_ExtensionInterface;
use TypeError;
use function GuzzleHttp\Promise\promise_for;

final class JsonLdSchemaOrgExtensionTest extends TestCase
{
    private $urlGenerator;
    private $packages;
    private $extension;

    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->packages = $this->createMock(Packages::class);

        $this->extension = new JsonLdSchemaOrgExtension($this->urlGenerator, $this->packages);
    }

    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $this->assertInstanceOf(Twig_ExtensionInterface::class, $this->extension);
    }

    /**
     * @test
     */
    public function it_must_receive_a_content_model()
    {
        $this->urlGenerator->expects($this->once())->method('getContext')->willReturn(new RequestContext());

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $this->extension->generate(new Digest(
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
        $this->extension->generate('not content model');
    }

    /**
     * @test
     */
    public function it_will_generate_json_ld_schema_from_digest()
    {
        $this->urlGenerator->expects($this->once())->method('getContext')->willReturn(new RequestContext(null, 'GET', 'journal', 'https'));
        $this->packages->expects($this->once())->method('getUrl')->willReturn('/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $thumbnail = $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);
        $json = $this->extension->generateJson(new Digest(
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
                    'url' => 'https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png',
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
}
