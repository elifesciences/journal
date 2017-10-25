<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\JobAdvert;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class JobAdvertTeaserConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param JobAdvert $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return Teaser::main(
          $object->getTitle(),
          $this->urlGenerator->generate('job-advert', [$object]),
          $object->getImpactStatement(),
          null,
          null,
          null,
          TeaserFooter::forNonArticle(
            Meta::withLink(
              new Link('eLife Jobs', $this->urlGenerator->generate('job-adverts')),
              $this->simpleDate($object, $context)
            )
          )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof JobAdvert && ViewModel\Teaser::class === $viewModel;
    }
}
