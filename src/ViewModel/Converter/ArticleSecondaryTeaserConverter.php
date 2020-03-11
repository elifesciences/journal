<?php

namespace eLife\Journal\ViewModel\Converter;

use DateTimeImmutable;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ArticleSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $viewModelConverter;
    private $urlGenerator;
    private $authorizationChecker;
    private $rdsArticles;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator, AuthorizationCheckerInterface $authorizationChecker, array $rdsArticles)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
        $this->rdsArticles = $rdsArticles;
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

        if ($object instanceof ArticleVoR && isset($this->rdsArticles[$object->getId()]['date']) && $this->authorizationChecker->isGranted('FEATURE_RDS')) {
            $date = ViewModel\Date::simple(new DateTimeImmutable($this->rdsArticles[$object->getId()]['date']), true);
        } else {
            $date = $this->simpleDate($object, $context);
        }

        return Teaser::secondary(
            $object->getFullTitle(),
            $this->urlGenerator->generate('article', [$object]),
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $image,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular($object->getType()),
                        $this->urlGenerator->generate('article-type', ['type' => $object->getType()])
                    ),
                    $date
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
