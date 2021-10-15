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
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Twig\SchemaOrgMetadataExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig_ExtensionInterface;
use TypeError;
use function GuzzleHttp\Promise\promise_for;

final class SchemaOrgMetadataExtensionTest extends TestCase
{
    private $urlGenerator;
    private $packages;
    /** @var SchemaOrgMetadataExtension */
    private $extension;

    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->packages = $this->createMock(Packages::class);

        $this->extension = new SchemaOrgMetadataExtension($this->urlGenerator, $this->packages);
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
    public function it_will_generate_metadata_from_digest()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/articles/digest-id');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $thumbnail = $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);
        $json = $this->extension->generateJson(new Digest(
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
        ), false);

        $this->assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => 'https://journal/articles/digest-id',
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

    /**
     * @test
     */
    public function it_will_generate_metadata_from_article()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/articles/article-id');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $thumbnail = $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);
        $json = $this->extension->generateJson(new ArticleVoR(
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
        ), false);

        $this->assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'ScholarlyArticle',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => 'https://journal/articles/article-id',
            ],
            'headline' => 'Article title',
            'image' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
            'datePublished' => '2008-09-28',
            'author' => [
                [
                    '@type' => 'Person',
                    'name' => 'Author name 1',
                ],
                [
                    '@type' => 'Person',
                    'name' => 'Author name 2',
                ],
                [
                    '@type' => 'Organization',
                    'name' => 'Group author name 1',
                ],
                'On behalf author name 1',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'eLife Sciences Publications, Ltd',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png',
                ],
            ],
            'keywords' => [
                'Keyword 1',
                'Keyword 2',
            ],
            'about' => [
                'Subject 1 name',
            ],
            'description' => 'Article impact statement',
            'isPartOf' => [
                '@type' => 'Periodical',
                'name' => 'eLife',
                'issn' => '2050-084X',
            ],
        ], $json);
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_collection()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/collections/collection-id');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $banner = $thumbnail = $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);

        $json = $this->extension->generateJson(new Collection(
            'collection-id',
            'Collection title',
            'Collection impact statement',
            new DateTimeImmutable('2008-09-29 01:23:45'),
            null,
            promise_for($banner),
            $thumbnail,
            promise_for(null),
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($subjectBanner), promise_for($subjectThumbnail)),
            ]),
            new Person(
                'id',
                new PersonDetails('preferred', 'index'),
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
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence(),
            new EmptySequence()
        ), false);

        $this->assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'Collection',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => 'https://journal/collections/collection-id',
            ],
            'headline' => 'Collection title',
            'image' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
            'datePublished' => '2008-09-29',
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
            'description' => 'Collection impact statement',
            'isPartOf' => [
                '@type' => 'Periodical',
                'name' => 'eLife',
                'issn' => '2050-084X',
            ],
        ], $json);
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_event()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/events/event-id');

        $json = $this->extension->generateJson(new Event(
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
        ), false);

        $this->assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => 'https://journal/events/event-id',
            ],
            'headline' => 'Event title',
            'startDate' => '2008-10-20T09:00:00Z',
            'endDate' => '2008-10-22T17:35:00Z',
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'eLife Sciences Publications, Ltd',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://journal/assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png',
                ],
            ],
            'description' => 'Event impact statement',
            'isPartOf' => [
                '@type' => 'Periodical',
                'name' => 'eLife',
                'issn' => '2050-084X',
            ],
        ], $json);
    }

    /**
     * @test
     */
    public function it_will_generate_metadata_from_blog_article()
    {
        $this->defaultExpectations();

        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('https://journal/blog-articles/blog-article-id');

        $file = new File('image/jpeg', 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg', 'example.jpg');
        $subjectBanner = $subjectThumbnail = new Image('', 'https://iiif.elifesciences.org/example.jpg', new EmptySequence(), $file, 1000, 500, 50, 50);

        $json = $this->extension->generateJson(new BlogArticle(
            'blog-article-id',
            'Blog article title',
            new DateTimeImmutable('2008-10-01 01:23:45'),
            null,
            'Blog article impact statement',
            promise_for(null),
            new EmptySequence(),
            new ArraySequence([
                new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
                    new EmptySequence(), promise_for($subjectBanner), promise_for($subjectThumbnail)),
            ])
        ), false);

        $this->assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => 'https://journal/blog-articles/blog-article-id',
            ],
            'headline' => 'Blog article title',
            'datePublished' => '2008-10-01',
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
            'description' => 'Blog article impact statement',
            'isPartOf' => [
                '@type' => 'Periodical',
                'name' => 'eLife',
                'issn' => '2050-084X',
            ],
        ], $json);
    }
}
