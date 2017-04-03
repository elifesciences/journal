<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\Block\CaptionedVideoConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CaptionedVideoConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Video::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class, ViewModel\CaptionedAsset::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedVideoConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            new DownloadLinkUriGenerator($this->createMock(UrlGeneratorInterface::class), new UriSigner('secret'))
        );
    }

    /**
     * @param Block\Video $block
     */
    protected function includeBlock(Block $block) : bool
    {
        return !empty($block->getTitle());
    }
}
