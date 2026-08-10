<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\Block\FigureAssetTableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
use PHPUnit\Framework\Attributes\Before;
use Traversable;

final class FigureAssetTableConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Figure::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new FigureAssetTableConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(ViewModel\AdditionalAsset::withoutDoi(
                'id',
                ViewModel\CaptionText::withHeading('Without doi'),
                ViewModel\DownloadLink::fromLink(new Link('Download link', 'http://google.com/download'), 'File name'),
                'http://google.com/'
            ));

        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }

    protected function explodeBlock(Block $block) : Traversable
    {
        yield from $block->getAssets()->filter(Callback::methodIsInstanceOf('getAsset', Block\Table::class));
    }
}
