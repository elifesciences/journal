<?php

namespace eLife\Journal\Twig;

use DateInterval;
use DateTimeImmutable;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\JobAdvert;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\PromotionalCollection;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CreatesIiifUri;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SchemaOrgMetadataExtension extends AbstractExtension
{
    const MAX_IMAGE_SIZE = 2000;

    private $urlGenerator;
    private $packages;

    use CreatesIiifUri;

    public function __construct(UrlGeneratorInterface $urlGenerator, Packages $packages)
    {
        $this->urlGenerator = $urlGenerator;
        $this->packages = $packages;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'schema_org_metadata',
                [$this, 'generate'],
                ['is_safe' => ['all']]
            ),
        ];
    }

    public function generate(Model $object) : string
    {
        return implode(PHP_EOL, [
            '<script type="application/ld+json">',
            json_encode(array_filter([
                '@context' => 'https://schema.org',
                '@type' => $this->getType($object),
                'mainEntityOfPage' => $this->getMainEntityOfPage($object),
                'episodeNumber' => $this->getEpisodeNumber($object),
                'duration' => $this->getDuration($object),
                'headline' => $this->getHeadline($object),
                'name' => $this->getName($object),
                'title' => $this->getTitle($object),
                'image' => $this->getImage($object),
                'datePublished' => $this->getDatePublished($object),
                'startDate' => $this->getStartDate($object),
                'endDate' => $this->getEndDate($object),
                'location' => $this->getLocation($object),
                'datePosted' => $this->getDatePosted($object),
                'author' => $this->getAuthor($object),
                'contributor' => $this->getContributor($object),
                'editor' => $this->getEditor($object),
                'publisher' => $this->getPublisher($object),
                'keywords' => $this->getKeywords($object),
                'about' => $this->getAbout($object),
                'description' => $this->getDescription($object),
                'associatedMedia' => $this->getAssociatedMedia($object),
                'partOfSeries' => $this->getPartOfSeries($object),
                'isPartOf' => $this->getIsPartOf($object),
                'hiringOrganization' => $this->getHiringOrganization($object),
                'jobLocation' => $this->getJobLocation($object),
            ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            '</script>',
        ]);
    }

    /**
     * @return string|null
     */
    private function getType(Model $object)
    {
        switch (true) {
            case $object instanceof ArticleVersion:
                return 'ScholarlyArticle';
            case $object instanceof BlogArticle:
            case $object instanceof LabsPost:
            case $object instanceof PressPackage:
                return 'Blog';
            case $object instanceof Digest:
                return 'NewsArticle';
            case $object instanceof Collection:
            case $object instanceof PromotionalCollection:
                return 'Collection';
            case $object instanceof Event:
                return 'Event';
            case $object instanceof Interview:
                return 'Conversation';
            case $object instanceof JobAdvert:
                return 'JobPosting';
            case $object instanceof PodcastEpisode:
                return 'PodcastEpisode';
            default:
                return null;
        }
    }

    private function getMainEntityOfPage(Model $object) : array
    {
        switch (true) {
            case $object instanceof ArticleVersion:
            case $object instanceof Digest:
                $id = 'article';
                break;
            case $object instanceof BlogArticle:
                $id = 'inside-elife-article';
                break;
            case $object instanceof Collection:
                $id = 'collection';
                break;
            case $object instanceof Event:
                $id = 'event';
                break;
            case $object instanceof Interview:
                $id = 'interview';
                break;
            case $object instanceof JobAdvert:
                $id = 'job-advert';
                break;
            case $object instanceof LabsPost:
                $id = 'labs-post';
                break;
            case $object instanceof PodcastEpisode:
                $id = 'podcast-episode';
                break;
            case $object instanceof PressPackage:
                $id = 'press-packs';
                break;
            case $object instanceof PromotionalCollection:
                $id = 'promotional-collection';
                break;
            default:
                $id = null;
        }

        return !is_null($id) ? array_filter([
            '@type' => 'WebPage',
            '@id' => $this->urlGenerator->generate($id, [$object], UrlGeneratorInterface::ABSOLUTE_URL),
        ]) : $id;
    }

    /**
     * @return int|null
     */
    private function getEpisodeNumber(Model $object)
    {
        if ($object instanceof PodcastEpisode) {
            return $object->getNumber();
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getDuration(Model $object)
    {
        if ($object instanceof PodcastEpisode) {
            $start = new DateTimeImmutable();
            $end = $start->add(new DateInterval('PT'.(array_sum(
                array_map(
                    function (PodcastEpisodeChapter $chapter) {
                        return $chapter->getTime();
                    },
                    $object->getChapters()->toArray()
                )
            ) ?? 0).'S'));

            $duration = $start->diff($end);

            $format = implode('', iterator_to_array((function ($time) {
                foreach ($time as $unit => $length) {
                    yield "${length}{$unit}";
                }
            })(array_filter([
                'H' => $duration->h,
                'M' => $duration->i,
                'S' => $duration->s,
            ]))));

            if (!empty($format)) {
                return 'PT'.$format;
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getHeadline(Model $object)
    {
        switch (true) {
            case $object instanceof ArticleVersion:
                $title = $object->getFullTitle();
                break;
            case $object instanceof BlogArticle:
            case $object instanceof Collection:
            case $object instanceof Digest:
            case $object instanceof Interview:
            case $object instanceof LabsPost:
            case $object instanceof PodcastEpisode:
            case $object instanceof PressPackage:
            case $object instanceof PromotionalCollection:
                $title = $object->getTitle();
                break;
            default:
                $title = null;
        }

        return $title ? strip_tags($title) : $title;
    }

    /**
     * @return string|null
     */
    private function getName(Model $object)
    {
        switch (true) {
            case $object instanceof Event:
            case $object instanceof JobAdvert:
                $title = $object->getTitle();
                break;
            default:
                $title = null;
        }

        return $title ? strip_tags($title) : $title;
    }

    /**
     * @return string|null
     */
    private function getTitle(Model $object)
    {
        switch (true) {
            case $object instanceof JobAdvert:
                $title = $object->getTitle();
                break;
            default:
                $title = null;
        }

        return $title ? strip_tags($title) : $title;
    }

    /**
     * @return string|null
     */
    private function getImage(Model $object)
    {
        if ($object instanceof HasThumbnail && $thumbnail = $object->getThumbnail()) {
            list($width, $height) = $this->determineSizes($thumbnail->getWidth(), $thumbnail->getHeight(), self::MAX_IMAGE_SIZE);
            return $this->iiifUri($thumbnail, $width, $height);
        }
    }

    /**
     * @return string|null
     */
    private function getDatePublished(Model $object)
    {
        if ($object instanceof HasPublishedDate && !$object instanceof Event && !$object instanceof JobAdvert) {
            return $object->getPublishedDate() ? $object->getPublishedDate()->format('Y-m-d') : null;
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getStartDate(Model $object)
    {
        if ($object instanceof Event) {
            return $object->getStarts() ? $object->getStarts()->format('Y-m-d\TH:i:s\Z') : null;
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getEndDate(Model $object)
    {
        if ($object instanceof Event) {
            return $object->getEnds() ? $object->getEnds()->format('Y-m-d\TH:i:s\Z') : null;
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getLocation(Model $object)
    {
        if ($object instanceof Event) {
            return [
                '@type' => 'VirtualLocation',
                'url' => $this->urlGenerator->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getDatePosted(Model $object)
    {
        if ($object instanceof JobAdvert) {
            return $object->getPublishedDate()->format('Y-m-d');
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getAuthor(Model $object)
    {
        if ($object instanceof ArticleVersion) {
            return array_map(function (AuthorEntry $author) {
                if ($author instanceof PersonAuthor) {
                    return [
                        '@type' => 'Person',
                        'name' => $author->getPreferredName(),
                    ];
                } elseif ($author instanceof GroupAuthor) {
                    return [
                        '@type' => 'Organization',
                        'name' => $author->toString(),
                    ];
                }

                return $author->toString();
            }, $object->getAuthors()->toArray());
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getContributor(Model $object)
    {
        if ($object instanceof Interview) {
            return [
                [
                    '@type' => 'Person',
                    'name' => $object->getInterviewee()->getPerson()->getPreferredName(),
                ],
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getEditor(Model $object)
    {
        if ($object instanceof Collection || $object instanceof PromotionalCollection) {
            return array_map(function (Person $editor) {
                return [
                    '@type' => 'Person',
                    'name' => $editor->getDetails()->getPreferredName(),
                ];
            }, ($object instanceof Collection ? $object->getCurators() : $object->getEditors())->toArray());
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getPublisher(Model $object)
    {
        if (!$object instanceof Event && !$object instanceof JobAdvert) {
            $context = $this->urlGenerator->getContext();
            $port = 'http' === $context->getScheme() ? $context->getHttpPort() : $context->getHttpsPort();

            return [
                '@type' => 'Organization',
                'name' => 'eLife Sciences Publications, Ltd',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => implode('', [
                        $context->getScheme(),
                        '://',
                        $context->getHost(),
                        (80 !== $port && 'http' === $context->getScheme()) || (443 !== $port && 'http' === $context->getScheme()) ? ':'.$port : '',
                        $context->getBaseUrl(),
                        $this->packages->getUrl('assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png'),
                    ]),
                ],
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getKeywords(Model $object)
    {
        if ($object instanceof ArticleVoR) {
            return array_map(function ($keyword) {
                return strip_tags($keyword);
            }, $object->getKeywords()->toArray());
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getAbout(Model $object)
    {
        if ($object instanceof HasSubjects) {
            return array_map(function (Subject $subject) {
                return strip_tags($subject->getName());
            }, $object->getSubjects()->toArray());
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getDescription(Model $object)
    {
        if ($object instanceof HasImpactStatement) {
            return strip_tags($object->getImpactStatement());
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getAssociatedMedia(Model $object)
    {
        if ($object instanceof PodcastEpisode) {
            if ($sources = $object->getSources()) {
                return [
                    '@type' => 'MediaObject',
                    'contentUrl' => $sources[0]->getUri(),
                ];
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getPartOfSeries(Model $object)
    {
        if ($object instanceof PodcastEpisode) {
            return [
                '@type' => 'PodcastSeries',
                'name' => 'eLife podcast',
                'url' => $this->urlGenerator->generate('podcast'),
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getIsPartOf(Model $object)
    {
        if (!$object instanceof Event && !$object instanceof JobAdvert) {
            return [
                '@type' => 'Periodical',
                'name' => 'eLife',
                'issn' => '2050-084X',
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getHiringOrganization(Model $object)
    {
        if ($object instanceof JobAdvert) {

            return [
                '@type' => 'Organization',
                'name' => 'eLife Sciences Publications, Ltd',
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getJobLocation(Model $object)
    {
        if ($object instanceof JobAdvert) {

            return [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Cambridge',
                    'addressCountry' => 'UK',
                ],
            ];
        }

        return null;
    }
}
