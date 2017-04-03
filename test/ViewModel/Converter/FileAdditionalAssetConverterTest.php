<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\FileAdditionalAssetConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\AdditionalAsset;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class FileAdditionalAssetConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [AdditionalAsset::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new FileAdditionalAssetConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class),
            new DownloadLinkUriGenerator($this->createMock(UrlGeneratorInterface::class), new UriSigner('secret'))
        );
    }

    /**
     * @param ArticleVersion $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getAdditionalFiles();
    }
}
