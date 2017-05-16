<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
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
    use CreatesId;
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
        })->toArray();

        $authors = $object->getAuthors()->map(function (AuthorEntry $author) use ($object) {
            if ($author instanceof Author) {
                return ViewModel\Author::asLink(
                    new ViewModel\Link(
                        $author->toString(),
                        $this->urlGenerator->generate('article', ['id' => $object->getId(), '_fragment' => $this->createId($author)])
                    ),
                    !empty($author->getEmailAddresses()) || !empty($author->getPhoneNumbers())
                );
            }

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

        $meta = ViewModel\Meta::withLink(
            new ViewModel\Link(
                ModelName::singular($object->getType()),
                $this->urlGenerator->generate('article-type', ['type' => $object->getType()])
            ),
            $this->simpleDate($object, ['date' => 'published'] + $context)
        );

        return new ViewModel\ContentHeader(
            $object->getFullTitle(),
            null,
            null,
            true,
            $subjects,
            null,
            $object->getAuthorLine(),
            $authors,
            $institutions,
            $this->urlGenerator->generate('article', ['id' => $object->getId(), '_fragment' => 'downloads']),
            null,
            null,
            $meta
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\ContentHeader::class === $viewModel;
    }
}
