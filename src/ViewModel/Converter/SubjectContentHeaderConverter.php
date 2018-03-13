<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SubjectContentHeaderConverter implements ViewModelConverter
{
    private $contentHeaderImageFactory;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, ContentHeaderImageFactory $contentHeaderImageFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->contentHeaderImageFactory = $contentHeaderImageFactory;
    }

    /**
     * @param Subject $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object->getAimsAndScope()->notEmpty()) {
            $impactStatement = 'Find out more about <a href="'.$this->urlGenerator->generate('about-aims-scope', ['_fragment' => $object->getId()]).'">submitting work to this area</a>';
        }

        return new ViewModel\ContentHeader(
            $object->getName(),
            $this->contentHeaderImageFactory->forImage($object->getBanner()),
            $impactStatement ?? $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Subject && ViewModel\ContentHeader::class === $viewModel;
    }
}
