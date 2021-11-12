<?php

namespace test\eLife\Journal\Twig;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\JobAdvert;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\PromotionalCollection;
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

    private function defaultExpectations()
    {
        $this->urlGenerator->expects($this->once())->method('getContext')->willReturn(new RequestContext(null, 'GET', 'journal', 'https'));
        $this->packages->expects($this->once())->method('getUrl')->willReturn('/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png');
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
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/articles/digest-id');

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
            $this->defaultImage(),
            null,
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($this->defaultImage()), promise_for($this->defaultImage())),
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
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/articles/article-id');

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
            $this->defaultImage(),
            null,
            null,
            null,
            promise_for(null),
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($this->defaultImage()), promise_for($this->defaultImage())),
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

    /**
     * @test
     */
    public function it_will_generate_metadata_from_collection()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/collections/collection-id');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "Collection",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/collections/collection-id"',
            '    },',
            '    "headline": "Collection title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "datePublished": "2008-09-29",',
            '    "editor": [',
            '        {',
            '            "@type": "Person",',
            '            "name": "Curator name 1"',
            '        },',
            '        {',
            '            "@type": "Person",',
            '            "name": "Curator name 2"',
            '        }',
            '    ],',
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
            '    "description": "Collection impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new Collection(
            'collection-id',
            'Collection title',
            'Collection impact statement',
            new DateTimeImmutable('2008-09-29 01:23:45'),
            null,
            promise_for($this->defaultImage()),
            $this->defaultImage(),
            promise_for(null),
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($this->defaultImage()), promise_for($this->defaultImage())),
            ]),
            new Person(
                'id',
                new PersonDetails('Curator name 1', 'Curator name 1, index'),
                promise_for(null),
                promise_for(null),
                'Type',
                'Type label',
                null,
                new EmptySequence(),
                promise_for(null),
                new EmptySequence(),
                promise_for(null),
                new EmptySequence()
            ),
            false,
            new ArraySequence([
                new Person(
                    'id',
                    new PersonDetails('Curator name 1', 'Curator name 1, index'),
                    promise_for(null),
                    promise_for(null),
                    'Type',
                    'Type label',
                    null,
                    new EmptySequence(),
                    promise_for(null),
                    new EmptySequence(),
                    promise_for(null),
                    new EmptySequence()
                ),
                new Person(
                    'id',
                    new PersonDetails('Curator name 2', 'Curator name 2, index'),
                    promise_for(null),
                    promise_for(null),
                    'Type',
                    'Type label',
                    null,
                    new EmptySequence(),
                    promise_for(null),
                    new EmptySequence(),
                    promise_for(null),
                    new EmptySequence()
                ),
            ]),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence()
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_promotional_collection()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/highlights/highlight-id');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "Collection",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/highlights/highlight-id"',
            '    },',
            '    "headline": "Highlight title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "datePublished": "2008-09-29",',
            '    "editor": [',
            '        {',
            '            "@type": "Person",',
            '            "name": "Editor name 1"',
            '        }',
            '    ],',
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
            '    "description": "Highlight impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new PromotionalCollection(
            'highlight-id',
            'Highlight title',
            'Highlight impact statement',
            new DateTimeImmutable('2008-09-29 01:23:45'),
            null,
            promise_for($this->defaultImage()),
            $this->defaultImage(),
            promise_for(null),
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($this->defaultImage()), promise_for($this->defaultImage())),
            ]),
            new ArraySequence([
                new Person(
                    'id',
                    new PersonDetails('Editor name 1', 'Editor name 1, index'),
                    promise_for(null),
                    promise_for(null),
                    'Type',
                    'Type label',
                    null,
                    new EmptySequence(),
                    promise_for(null),
                    new EmptySequence(),
                    promise_for(null),
                    new EmptySequence()
                ),
            ]),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence()
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_event()
    {
        $this->urlGenerator->expects($this->exactly(2))->method('generate')->will(
            $this->onConsecutiveCalls(
                'https://journal/events/event-id',
                'https://journal'
            )
        );

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "Event",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/events/event-id"',
            '    },',
            '    "name": "Event title",',
            '    "startDate": "2008-10-20T09:00:00Z",',
            '    "endDate": "2008-10-22T17:35:00Z",',
            '    "location": {',
            '        "@type": "VirtualLocation",',
            '        "url": "https://journal"',
            '    },',
            '    "description": "Event impact statement"',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new Event(
            'event-id',
            'Event title',
            'Event impact statement',
            new DateTimeImmutable('2008-09-30 01:23:45'),
            null,
            new DateTimeImmutable('2008-10-20 09:00:00'),
            new DateTimeImmutable('2008-10-22 17:35:00'),
            new DateTimeZone('Z'),
            null,
            promise_for(null),
            new EmptySequence()
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_blog_article()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/blog-articles/blog-article-id');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "Blog",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/blog-articles/blog-article-id"',
            '    },',
            '    "headline": "Blog article title",',
            '    "datePublished": "2008-10-01",',
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
            '    "description": "Blog article impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new BlogArticle(
            'blog-article-id',
            'Blog article title',
            new DateTimeImmutable('2008-10-01 01:23:45'),
            null,
            'Blog article impact statement',
            promise_for(null),
            new EmptySequence(),
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($this->defaultImage()), promise_for($this->defaultImage())),
            ])
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_labs_post()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/labs/labs-post-id');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "Blog",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/labs/labs-post-id"',
            '    },',
            '    "headline": "Labs post title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "datePublished": "2008-10-02",',
            '    "publisher": {',
            '        "@type": "Organization",',
            '        "name": "eLife Sciences Publications, Ltd",',
            '        "logo": {',
            '            "@type": "ImageObject",',
            '            "url": "https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png"',
            '        }',
            '    },',
            '    "description": "Labs post impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new LabsPost(
            'labs-post-id',
            'Labs post title',
            new DateTimeImmutable('2008-10-02'),
            null,
            'Labs post impact statement',
            $this->defaultImage(),
            promise_for(null),
            new EmptySequence()
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_press_packs()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/for-the-press/press-pack-id');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "Blog",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/for-the-press/press-pack-id"',
            '    },',
            '    "headline": "Press package title",',
            '    "datePublished": "2008-10-02",',
            '    "publisher": {',
            '        "@type": "Organization",',
            '        "name": "eLife Sciences Publications, Ltd",',
            '        "logo": {',
            '            "@type": "ImageObject",',
            '            "url": "https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png"',
            '        }',
            '    },',
            '    "description": "Press pack impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new PressPackage(
            'press-pack-id',
            'Press package title',
            new DateTimeImmutable('2008-10-02'),
            null,
            'Press pack impact statement',
            promise_for(null),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence()
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_podcast_episode()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->exactly(2))->method('generate')->will(
            $this->onConsecutiveCalls(
                'https://journal/podcast-episode/1',
                'https://journal/podcast'
            )
        );

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "PodcastEpisode",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/podcast-episode/1"',
            '    },',
            '    "episodeNumber": 1,',
            '    "duration": "PT1H16M40S",',
            '    "headline": "Podcast episode title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "datePublished": "2008-10-09",',
            '    "publisher": {',
            '        "@type": "Organization",',
            '        "name": "eLife Sciences Publications, Ltd",',
            '        "logo": {',
            '            "@type": "ImageObject",',
            '            "url": "https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png"',
            '        }',
            '    },',
            '    "description": "Podcast episode impact statement",',
            '    "associatedMedia": {',
            '        "@type": "MediaObject",',
            '        "contentUrl": "https://www.example.com/episode.mp3"',
            '    },',
            '    "partOfSeries": {',
            '        "@type": "PodcastSeries",',
            '        "name": "eLife podcast",',
            '        "url": "https://journal/podcast"',
            '    },',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new PodcastEpisode(
            1,
            'Podcast episode title',
            'Podcast episode impact statement',
            new DateTimeImmutable('2008-10-09'),
            null,
            promise_for(null),
            $this->defaultImage(),
            promise_for(null),
            [
                new PodcastEpisodeSource('audio/mpeg', 'https://www.example.com/episode.mp3'),
            ],
            new ArraySequence(
                [
                    new PodcastEpisodeChapter(
                        1,
                        'Chapter 1',
                        null,
                        400,
                        null,
                        new EmptySequence()
                    ),
                    new PodcastEpisodeChapter(
                        2,
                        'Chapter 2',
                        null,
                        350,
                        null,
                        new EmptySequence()
                    ),
                    new PodcastEpisodeChapter(
                        3,
                        'Chapter 4',
                        null,
                        250,
                        null,
                        new EmptySequence()
                    ),
                    new PodcastEpisodeChapter(
                        4,
                        'Chapter 5',
                        null,
                        3600,
                        null,
                        new EmptySequence()
                    ),
                ]
            )
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_job_advert()
    {
        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/jobs/job-advert-id');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "JobPosting",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/jobs/job-advert-id"',
            '    },',
            '    "name": "Job advert title",',
            '    "datePosted": "2008-10-30",',
            '    "description": "Job advert impact statement"',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new JobAdvert(
            'job-advert-id',
            'Job advert title',
            'Job advert impact statement',
            promise_for(null),
            new DateTimeImmutable('2008-10-30 01:23:45'),
            new DateTimeImmutable('2008-11-30 01:23:45'),
            null,
            new EmptySequence()
        )]));
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_interview()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/interviews/interview-id');

        $this->assertSame(implode(PHP_EOL, [
            '<script type="application/ld+json">',
            '{',
            '    "@context": "https://schema.org",',
            '    "@type": "Conversation",',
            '    "mainEntityOfPage": {',
            '        "@type": "WebPage",',
            '        "@id": "https://journal/interviews/interview-id"',
            '    },',
            '    "headline": "Interview title",',
            '    "image": "https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg",',
            '    "datePublished": "2008-10-30",',
            '    "contributor": [',
            '        {',
            '            "@type": "Person",',
            '            "name": "Interviewee name 1"',
            '        }',
            '    ],',
            '    "publisher": {',
            '        "@type": "Organization",',
            '        "name": "eLife Sciences Publications, Ltd",',
            '        "logo": {',
            '            "@type": "ImageObject",',
            '            "url": "https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png"',
            '        }',
            '    },',
            '    "description": "Interview impact statement",',
            '    "isPartOf": {',
            '        "@type": "Periodical",',
            '        "name": "eLife",',
            '        "issn": "2050-084X"',
            '    }',
            '}',
            '</script>',
        ]), $this->twig->render('foo', ['item' => new Interview(
            'interview-id',
            new Interviewee(
                new PersonDetails('Interviewee name 1', 'Interviewee name 1, index'),
                new EmptySequence()
            ),
            'Interview title',
            new DateTimeImmutable('2008-10-30 01:23:45'),
            null,
            'Interview impact statement',
            $this->defaultImage(),
            promise_for(null),
            new EmptySequence()
        )]));
    }

    private function defaultImage()
    {
        return new Image(
            '',
            'https://iiif.elifesciences.org/example.jpg',
            new EmptySequence(),
            new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg'),
            1000,
            500,
            50,
            50
        );
    }
}
