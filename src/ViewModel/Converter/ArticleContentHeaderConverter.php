<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesIiifUri;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $subjects = $object->getSubjects()->map(function (Subject $subject) {
            return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', ['id' => $subject->getId()]));
        });

        $authors = $object->getAuthors()->map(function (AuthorEntry $author) {
            return ViewModel\Author::asText($author->toString());
        })->toArray();

        $institutions = array_map(function (string $institution) {
            return new ViewModel\Institution($institution);
        }, array_values(array_unique($object->getAuthors()->reduce(function (array $institutions, AuthorEntry $author) {
            if ($author instanceof Author) {
                foreach ($author->getAffiliations() as $affiliation) {
                    $name = $affiliation->getName();
                    $name = end($name);
                    if ($affiliation->getAddress() && $affiliation->getAddress()->getCountry()) {
                        $name .= ', '.$affiliation->getAddress()->getCountry();
                    }
                    $institutions[] = $name;
                }
            }

            return $institutions;
        }, []))));

        $authors = !empty($authors) ? ViewModel\AuthorList::asList($authors) : null;
        $institutions = !empty($institutions) ? new ViewModel\InstitutionList($institutions) : null;

        $meta = ViewModel\Meta::withLink(
            new ViewModel\Link(
                ModelName::singular($object->getType()),
                $this->urlGenerator->generate('article-type', ['type' => $object->getType()])
            ),
            $this->simpleDate($object, ['date' => 'published'] + $context)
        );

        switch ($object->getType()) {
            case 'research-advance':
            case 'research-article':
            case 'research-exchange':
            case 'replication-study':
            case 'short-report':
            case 'tools-resources':
                return ViewModel\ContentHeaderArticle::research(
                    $object->getFullTitle(),
                    $meta,
                    new ViewModel\SubjectList(...$subjects),
                    $authors,
                    $institutions,
                    '#downloads'
                );
        }

        if ($object instanceof ArticleVoR && $object->getBanner()) {
            $image = new ViewModel\BackgroundImage(
                $this->iiifUri($object->getBanner(), 900, 450),
                $this->iiifUri($object->getBanner(), 1800, 900)
            );
        } else {
            $image = null;
        }

        return ViewModel\ContentHeaderArticle::magazine(
            $object->getFullTitle(),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $authors,
            '#downloads',
            new ViewModel\SubjectList(...$subjects),
            $meta,
            $institutions,
            false,
            $image
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\ContentHeaderArticle::class === $viewModel;
    }
}
