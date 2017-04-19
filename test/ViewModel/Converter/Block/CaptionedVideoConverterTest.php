<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\Block\CaptionedVideoConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AdditionalAsset;
use eLife\Patterns\ViewModel\CaptionText;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CaptionedVideoConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Video::class;
    protected $viewModelClasses = [ViewModel\AssetViewerInline::class, ViewModel\CaptionedAsset::class];
    protected $context = ['complete' => true];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedVideoConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            new DownloadLinkUriGenerator($this->createMock(UrlGeneratorInterface::class), new UriSigner('secret'))
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(AdditionalAsset::withoutDoi(
                'some-id',
                new CaptionText('Some asset'),
                null,
                'https://example.com/some-id'
            )));
    }

    /**
     * @param Block\Video $block
     */
    protected function includeBlock(Block $block) : bool
    {
        return !empty($block->getTitle());
    }
}
