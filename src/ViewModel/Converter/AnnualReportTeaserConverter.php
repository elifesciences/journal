<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\Teaser;

final class AnnualReportTeaserConverter implements ViewModelConverter
{
    private $patternRenderer;
    private $downloadLinkUriGenerator;

    public function __construct(PatternRenderer $patternRenderer, DownloadLinkUriGenerator $downloadLinkUriGenerator)
    {
        $this->patternRenderer = $patternRenderer;
        $this->downloadLinkUriGenerator = $downloadLinkUriGenerator;
    }

    /**
     * @param AnnualReport $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $content = [];
        if ($object->getImpactStatement()) {
            $content[] = new Paragraph($object->getImpactStatement());
        }

        $links = [
            "<a href=\"{$object->getUri()}\">View report</a>",
        ];
        if ($object->getPdf()) {
            $download = $this->downloadLinkUriGenerator->generate(DownloadLink::fromUri($object->getPdf()));
            $links[] = "<a href=\"{$download}\">Download PDF</a>";
        }
        $content[] = ViewModel\Listing::forTeaser($links);

        return Teaser::main(
            $object->getTitle(),
            null,
            $this->patternRenderer->render(...$content)
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof AnnualReport && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
