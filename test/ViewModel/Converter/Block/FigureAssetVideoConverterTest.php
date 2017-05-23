<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\Block\FigureAssetVideoConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class FigureAssetVideoConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Figure::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new FigureAssetVideoConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            new DownloadLinkUriGenerator($this->createMock(UrlGeneratorInterface::class), new UriSigner('secret'))
        );
    }

    protected function explodeBlock(Block $block) : Traversable
    {
        yield from $block->getAssets()->filter(Callback::methodIsInstanceOf('getAsset', Block\Video::class));
    }
}
