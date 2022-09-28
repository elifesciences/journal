<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ArticleTeaserConverter implements ViewModelConverter
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
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object instanceof ArticleVoR && $object->getThumbnail()) {
            $image = $this->smallTeaserImage($object);
        } else {
            $image = null;
        }

        $formats = [];

        if ($object instanceof ArticleVoR) {
            if (isset($this->eraArticles[$object->getId()]['display'])) {
                $formats[] = 'Executable';
            } else {
                $formats[] = 'HTML';
            }
        }

        if (null !== $object->getPdf()) {
            $formats[] = 'PDF';
        }

        return ViewModel\Teaser::main(
            $object->getFullTitle(),
            $this->urlGenerator->generate('article', [$object]),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $image,
            ViewModel\TeaserFooter::forArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular($object->getType()),
                        $this->urlGenerator->generate('article-type', ['type' => $object->getType()])
                    ),
                    $this->simpleDate($object, $context)
                ),
                $formats
            ),
            ['teaser--main']
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
