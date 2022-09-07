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

        if ($object->getPdf() !== null) {
            $formats[] = "PDF";
        }

        $image = null;
        if ($object->getThumbnail() !== null) {
            $image = $this->smallTeaserImage($object);
        }

        return ViewModel\Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('article', [$object]),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $image,
            ViewModel\TeaserFooter::forArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular('reviewed-preprint'),
                        $this->urlGenerator->generate('article-type', ['type' => 'reviewed-preprint'])
                    ),
                    $this->simpleDate($object, $context)
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
