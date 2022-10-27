<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ArticleModalConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Traversable;

final class ArticleModalConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\ModalWindow::class];
    protected $context = ['type' => 'social'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('foo'));

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
