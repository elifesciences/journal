<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\Block\FigureAssetTableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AdditionalAsset;
use eLife\Patterns\ViewModel\CaptionText;
use eLife\Patterns\ViewModel\DownloadLink;
use eLife\Patterns\ViewModel\Link;
use Traversable;

final class FigureAssetTableConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Figure::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new FigureAssetTableConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(ViewModel\AdditionalAsset::withoutDoi(
                'id',
                ViewModel\CaptionText::withHeading('Without doi'),
                ViewModel\DownloadLink::fromLink(new Link('Download link', 'http://google.com/download'), 'File name'),
                'http://google.com/'
            )));

        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }

    protected function explodeBlock(Block $block) : Traversable
    {
        yield from $block->getAssets()->filter(Callback::methodIsInstanceOf('getAsset', Block\Table::class));
    }
}
