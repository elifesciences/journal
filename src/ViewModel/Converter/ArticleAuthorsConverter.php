<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleAuthorsConverter implements ViewModelConverter
{
    use CreatesId;

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
        return new ViewModel\Authors(
            $object->getAuthors()->map(function (AuthorEntry $author) use ($object) {
                if ($author instanceof Author) {
                    return ViewModel\Author::asLink(
                        new ViewModel\Link(
                            $author->toString(),
                            $this->urlGenerator->generate('article', [$object, '_fragment' => $this->createId($author)])
                        ),
                        !empty($author->getEmailAddresses()) || !empty($author->getPhoneNumbers())
                    );
                }

                return ViewModel\Author::asText($author->toString());
            })->toArray(),
            array_map(function (string $institution) {
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
            }, []))))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Authors::class === $viewModel;
    }
}
