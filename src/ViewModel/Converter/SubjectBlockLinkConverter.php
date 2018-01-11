<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\MediaTypes;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SubjectBlockLinkConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    private $urlGenerator;
    private $pictureBuilderFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, PictureBuilderFactory $pictureBuilderFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->pictureBuilderFactory = $pictureBuilderFactory;
    }

    /**
     * @param Subject $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $image = $object->getThumbnail();

        if ('image/png' === $image->getSource()->getMediaType()) {
            $type = 'image/png';
        } else {
            $type = 'image/jpeg';
        }

        $builder = $this->pictureBuilderFactory
            ->create(function (string $type, int $width = null, int $height = null) use ($image) {
                $extension = MediaTypes::toExtension($type);

                if (null === $width && null === $height) {
                    // No picture is displayed at smaller sizes, this prevents an image from being downloaded at all.
                    return 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
                }

                return $this->iiifUri($image, $width, $height, $extension);
            }, $type, null, null, $image->getAltText());

        $builder = $builder
            ->setOriginalSize($image->getWidth(), $image->getHeight())
            ->addSize(251, 145, '(min-width: 600px)');

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
