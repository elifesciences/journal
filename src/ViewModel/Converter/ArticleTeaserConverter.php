<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticlePoA;
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

        $statusInfo = $this->getStatusInfo($object);
        $status = $statusInfo['status'];
        $statusColor = $statusInfo['statusColor'];

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
                    $this->simpleDate($object, $context),
                    $status,
                    $statusColor
                )
            )
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

    private function getStatusInfo(ArticleVersion $article) : array
    {
        if (in_array($article->getType(), [
            'correction',
            'retraction',
            'registered-report',
            'replication-study',
            'research-communication',
        ])) {
             return ['status' => null, 'statusColor' => null];
        }

        $status = null;
        $statusColor = null;

        if ($article instanceof ArticlePoA) {
            $status = 'Accepted Manuscript';
        } else if ($article instanceof ArticleVoR && $article->isReviewedPreprint()) {
            $status = 'Version of Record';
            $statusColor = 'vor';
        } else {
            $status = 'Version of Record';
        }

        return ['status' => $status, 'statusColor' => $statusColor];
    }
}
