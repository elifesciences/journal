<?php

namespace eLife\Journal\Twig;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CreatesIiifUri;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Extension;
use Twig_Function;

final class JsonLdSchemaOrgExtension extends Twig_Extension
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
            new Twig_Function(
                'json_ld_schema_org',
                [$this, 'generate'],
                ['is_safe' => ['all']]
            ),
            new Twig_Function(
                'json_ld_schema_org_json',
                [$this, 'generateJson'],
                ['is_safe' => ['all']]
            ),
        ];
    }

    public function generate(Model $object) : string
    {
        return implode(PHP_EOL, [
            '<script type="application/ld+json">',
            $this->generateJson($object),
            '</script>',
        ]);
    }

    /**
     * @return array|string
     */
    public function generateJson(Model $object, $json = true)
    {
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => $this->getType($object),
            'mainEntityOfPage' => $this->getMainEntityOfPage($object),
            'headline' => $this->getHeadline($object),
            'image' => $this->getImage($object),
            'datePublished' => $this->getDatePublished($object),
            'author' => $this->getAuthor($object),
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'eLife Sciences Publications, Ltd',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->getPublisherLogoUrl(),
                ],
            ],
            'keywords' => $this->getKeywords($object),
            'about' => $this->getAbout($object),
            'description' => $this->getDescription($object),
            'isPartOf' => [
                '@type' => 'Periodical',
                'name' => 'eLife',
                'issn' => '2050-084X',
            ],
        ]);

        return $json ? json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $schema;
    }

    /**
     * @return string|null
     */
    private function getType(Model $object)
    {
        switch (true) {
            case $object instanceof ArticleVersion:
                return 'ScholarlyArticle';
            case $object instanceof Digest:
                return 'NewsArticle';
            default:
                return null;
        }
    }

    private function getMainEntityOfPage(Model $object) : array
    {
        switch (true) {
            case $object instanceof ArticleVersion:
            case $object instanceof Digest:
                $id = $this->urlGenerator->generate('article', ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                break;
            default:
                $id = null;
        }

        return array_filter([
            '@type' => 'WebPage',
            '@id' => $id,
        ]);
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
            case $object instanceof Digest:
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
        if ($object instanceof HasPublishedDate) {
            return $object->getPublishedDate() ? $object->getPublishedDate()->format('Y-m-d') : null;
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

    private function getPublisherLogoUrl() : string
    {
        $context = $this->urlGenerator->getContext();
        $port = 'http' === $context->getScheme() ? $context->getHttpPort() : $context->getHttpsPort();

        return implode('', [
            $context->getScheme(),
            '://',
            $context->getHost(),
            (80 !== $port && 'http' === $context->getScheme()) || (443 !== $port && 'http' === $context->getScheme()) ? ':'.$port : '',
            $context->getBaseUrl(),
            $this->packages->getUrl('assets/patterns/img/patterns/organisms/elife-logo-symbol@2x.png'),
        ]);
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
}
