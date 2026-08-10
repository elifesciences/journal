<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ArticleModalConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use Traversable;

final class ArticleModalConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\ModalWindow::class];
    protected $context = ['type' => 'social'];

    #[Before]
    public function setUpConverter(): void
    {
        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->method('render')
            ->willReturn('foo');

        $this->converter = new ArticleModalConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer,
            $this->stubUrlGenerator()
        );
    }

    /**
     * @param ArticleVersion $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getAuthors()->notEmpty()) {
            yield $model;
        }
    }
}
