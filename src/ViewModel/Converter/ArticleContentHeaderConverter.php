<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleContentHeaderConverter implements ViewModelConverter
{
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
            switch (get_class($author)) {
                case PersonAuthor::class:
                    /* @var PersonAuthor $author */
                    return ViewModel\Author::asText($author->getPreferredName());
                    break;
                case GroupAuthor::class:
                    /* @var GroupAuthor $author */
                    return ViewModel\Author::asText($author->getName());
                    break;
                case OnBehalfOfAuthor::class:
                    /* @var OnBehalfOfAuthor $author */
                    return ViewModel\Author::asText($author->getOnBehalfOf());
                    break;
            }

            throw new \RuntimeException('Unknown type '.get_class($author));
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

        $authors = ViewModel\AuthorList::asList($authors);
        $institutions = !empty($institutions) ? new ViewModel\InstitutionList($institutions) : null;

        switch ($object->getType()) {
            case 'research-advance':
            case 'research-article':
            case 'research-exchange':
            case 'replication-study':
            case 'short-report':
            case 'tools-resources':
                return ViewModel\ContentHeaderArticle::research(
                    $object->getFullTitle(),
                    $authors,
                    ViewModel\Meta::withText(
                        ucfirst(str_replace('-', ' ', $object->getType())),
                        new ViewModel\Date($object->getPublishedDate())
                    ),
                    new ViewModel\SubjectList(...$subjects),
                    $institutions
                );
        }

        if ($object instanceof ArticleVoR && $object->getBanner()) {
            $image = new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            );
        } else {
            $image = null;
        }

        return ViewModel\ContentHeaderArticle::magazine(
            $object->getFullTitle(),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $authors,
            null,
            new ViewModel\SubjectList(...$subjects),
            ViewModel\Meta::withText(
                ucfirst(str_replace('-', ' ', $object->getType())),
                new ViewModel\Date($object->getPublishedDate())
            ),
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
