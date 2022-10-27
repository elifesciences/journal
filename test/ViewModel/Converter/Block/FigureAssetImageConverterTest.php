<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\Block\FigureAssetImageConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class FigureAssetImageConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Figure::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new FigureAssetImageConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            new DownloadLinkUriGenerator($this->createMock(UrlGeneratorInterface::class), new UriSigner('secret'))
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnCallback(function ($input) {
                if ($input instanceof AssetFile) {
                    return ViewModel\AdditionalAsset::withoutDoi('id', ViewModel\CaptionText::withHeading('heading'), null, 'http://google.com/');
                }

                return new ViewModel\Picture(
                    [],
                    new ViewModel\Image('/image.jpg')
                );
            }));
    }

    protected function explodeBlock(Block $block) : Traversable
    {
        yield from $block->getAssets()->filter(Callback::methodIsInstanceOf('getAsset', Block\Image::class));
    }
}
