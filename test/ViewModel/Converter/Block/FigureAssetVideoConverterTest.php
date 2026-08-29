<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\Block\FigureAssetVideoConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\HttpFoundation\UriSigner;
use Traversable;

final class FigureAssetVideoConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Figure::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new FigureAssetVideoConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            new DownloadLinkUriGenerator($this->stubUrlGenerator(), new UriSigner('secret'))
        );

        $viewModelConverter
            ->method('convert')
            ->willReturnCallback(function ($input) {
                if ($input instanceof AssetFile) {
                    return ViewModel\AdditionalAsset::withoutDoi('id', ViewModel\CaptionText::withHeading('heading'), null, 'http://google.com/');
                }

                return new ViewModel\Picture(
                    [],
                    new ViewModel\Image('/image.jpg')
                );
            });
    }

    protected function explodeBlock(Block $block) : Traversable
    {
        yield from $block->getAssets()->filter(Callback::methodIsInstanceOf('getAsset', Block\Video::class));
    }
}
