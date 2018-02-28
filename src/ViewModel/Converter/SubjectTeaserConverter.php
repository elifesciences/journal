<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CanConvert;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SubjectTeaserConverter implements ViewModelConverter
{
    use CanConvert;

    private $viewModelConverter;
    private $urlGenerator;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Subject $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $aimsScopes = $object->getAimsAndScope()->map($this->willConvertTo());
        $seeMore = new ViewModel\SeeMoreLink(
            new ViewModel\Link(
                'See Editors',
                $this->urlGenerator->generate('about-people', ['type' => $object->getId()])
            )
        );

        return Teaser::main(
            $object->getName(),
            null,
            $this->patternRenderer->render(...$aimsScopes).$this->patternRenderer->render($seeMore)
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Subject && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
