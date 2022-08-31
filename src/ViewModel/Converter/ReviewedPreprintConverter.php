<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ReviewedPreprintConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $viewModelConverter;
    private $urlGenerator;
    private $authorizationChecker;
    private $eraArticles;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator, AuthorizationCheckerInterface $authorizationChecker, array $eraArticles)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
        $this->eraArticles = $eraArticles;
    }

    /**
     * @param ReviewedPreprint $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $formats = ['HTML'];

        return ViewModel\Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('article', [$object]),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $object->getAuthorLine(),
            null,
            null,
            ViewModel\TeaserFooter::forArticle(
                ViewModel\Meta::withStatusDate(
                    ModelName::singular($object->getType()),
                    ViewModel\Date::simple($object->getPublishedDate()),
                    $object->getStatusDate() ? ViewModel\Date::simple($object->getStatusDate(), $object->getStatusDate() != $object->getPublishedDate()) : null
                ),
                $formats
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ReviewedPreprint;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
