<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PreprintReference;
use eLife\Journal\ViewModel\Converter\Reference\PreprintReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class PreprintReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = PreprintReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PreprintReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [],
                    'articleTitle' => '...',
                    'source' => '',
                    'uri' => '',
                ],
            ],
        ];
    }
}
