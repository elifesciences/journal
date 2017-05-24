<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Footnote;
use eLife\Journal\Helper\CanConvert;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class TableConverter implements ViewModelConverter
{
    use CanConvert;
    use CreatesCaptionedAsset;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Table $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $table = new ViewModel\Table(
            $object->getTables(),
            array_map(function (Footnote $footnote) {
                return new ViewModel\TableFootnote(
                    $this->patternRenderer->render(...$footnote->getText()->map($this->willConvertTo())),
                    $footnote->getId(),
                    $footnote->getLabel()
                );
            }, $object->getFootnotes())
        );

        if (!$object->getTitle() && $object->getAttribution()->isEmpty() && $object->getCaption()->isEmpty()) {
            return $table;
        }

        return $this->createCaptionedAsset($table, $object);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Table;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
