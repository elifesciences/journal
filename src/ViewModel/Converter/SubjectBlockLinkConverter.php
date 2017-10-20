<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Builder\PictureBuilder;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SubjectBlockLinkConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Subject $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $builder = new PictureBuilder(function (string $format = null, int $width = null, int $height = null) use ($object) {
            $extension = MediaTypes::toExtension($format ?? 'image/jpeg');

            if (null === $width && null === $height) {
                return 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
            }

            return $this->iiifUri($object->getThumbnail(), $width, $height, $extension);
        });

        $builder = $builder
            ->addType('image/jpeg')
            ->addSize(263, 148, '(min-width: 600px)');

        return new ViewModel\BlockLink(
            new ViewModel\Link(
                $object->getName(),
                $this->urlGenerator->generate('subject', [$object])
            ),
            $builder->build()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Subject && ViewModel\BlockLink::class === $viewModel;
    }
}
