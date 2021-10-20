<?php

namespace test\eLife\Journal\Twig;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Twig\SchemaOrgMetadataExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;
use TypeError;
use function GuzzleHttp\Promise\promise_for;

final class SchemaOrgMetadataExtensionTest extends TestCase
{
    private $urlGenerator;
    private $packages;
    /** @var SchemaOrgMetadataExtension */
    private $extension;
    /** @var Environment */
    private $twig;

    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->packages = $this->createMock(Packages::class);

        $this->extension = new SchemaOrgMetadataExtension($this->urlGenerator, $this->packages);

        $this->twig = new Environment(new ArrayLoader(['foo' => '{{ schema_org_metadata(item) }}']));
        $this->twig->addExtension($this->extension);
    }

    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_must_receive_a_content_model()
    {
        $this->urlGenerator->expects($this->once())->method('getContext')->willReturn(new RequestContext());

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $this->twig->render('foo', ['item' => new Digest(
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
        )]);

        $this->expectException(TypeError::class);
        $this->twig->render('foo', ['item' => 'not content model']);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_generates_schema_org_metadata()
    {
        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/articles/digest-id');
        $this->urlGenerator->expects($this->once())->method('getContext')->willReturn(new RequestContext(null, 'GET', 'journal', 'https'));
        $this->packages->expects($this->once())->method('getUrl')->willReturn('/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "NewsArticle",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/articles/digest-id"',
            '    },',
            '    "headline": "Digest title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "publisher": {',
            '        "@type": "Organization",',
            '        "name": "eLife Sciences Publications, Ltd",',
            '        "logo": {',
            '            "@type": "ImageObject",',
            '            "url": "https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png"',
            '        }',
            '    },',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new Digest(
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
        )]));
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_will_generate_metadata_from_digest()
    {
        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/articles/digest-id');
        $this->urlGenerator->expects($this->once())->method('getContext')->willReturn(new RequestContext(null, 'GET', 'journal', 'https'));
        $this->packages->expects($this->once())->method('getUrl')->willReturn('/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $thumbnail = $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "NewsArticle",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/articles/digest-id"',
            '    },',
            '    "headline": "Digest title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "datePublished": "2008-09-27",',
            '    "publisher": {',
            '        "@type": "Organization",',
            '        "name": "eLife Sciences Publications, Ltd",',
            '        "logo": {',
            '            "@type": "ImageObject",',
            '            "url": "https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png"',
            '        }',
            '    },',
            '    "about": [',
            '        "Subject 1 name"',
            '    ],',
            '    "description": "Impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new Digest(
            'digest-id',
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
        )]));
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_will_generate_metadata_from_article()
    {
        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/articles/article-id');
        $this->urlGenerator->expects($this->once())->method('getContext')->willReturn(new RequestContext(null, 'GET', 'journal', 'https'));
        $this->packages->expects($this->once())->method('getUrl')->willReturn('/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $thumbnail = $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "ScholarlyArticle",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/articles/article-id"',
            '    },',
            '    "headline": "Article title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "datePublished": "2008-09-28",',
            '    "author": [',
            '        {',
            '            "@type": "Person",',
            '            "name": "Author name 1"',
            '        },',
            '        {',
            '            "@type": "Person",',
            '            "name": "Author name 2"',
            '        },',
            '        {',
            '            "@type": "Organization",',
            '            "name": "Group author name 1"',
            '        },',
            '        "On behalf author name 1"',
            '    ],',
            '    "publisher": {',
            '        "@type": "Organization",',
            '        "name": "eLife Sciences Publications, Ltd",',
            '        "logo": {',
            '            "@type": "ImageObject",',
            '            "url": "https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png"',
            '        }',
            '    },',
            '    "keywords": [',
            '        "Keyword 1",',
            '        "Keyword 2"',
            '    ],',
            '    "about": [',
            '        "Subject 1 name"',
            '    ],',
            '    "description": "Article impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new ArticleVoR(
            'article-id',
            'published',
            1,
            'research-article',
            'doi',
            null,
            null,
            'Article title',
            new DateTimeImmutable('2008-09-28 01:23:45'),
            null,
            null,
            1,
            'eLocationId',
            $thumbnail,
            null,
            null,
            null,
            promise_for(null),
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($subjectBanner), promise_for($subjectThumbnail)),
            ]),
            [],
            null,
            promise_for(null),
            promise_for(new Copyright('copyright licence', 'copyright statement')),
            new ArraySequence([
                new PersonAuthor(new PersonDetails('Author name 1', 'Author name 1, index')),
                new PersonAuthor(new PersonDetails('Author name 2', 'Author name 2, index')),
                new GroupAuthor('Group author name 1', new EmptySequence()),
                new OnBehalfOfAuthor('On behalf author name 1')
            ]),
            new EmptySequence(),
            'Article impact statement',
            new ArraySequence([
                'Keyword 1',
                'Keyword <i>2</i>',
            ]),
            promise_for(null),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            promise_for(null),
            promise_for(null),
            promise_for(null),
            promise_for(null),
            new EmptySequence(),
            promise_for(null)
        )]));
    }
}