<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\DataReference;
use eLife\Journal\ViewModel\Converter\Reference\DataReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class DataReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = DataReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new DataReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [],
                    'title' => 'MNIST data set',
                    'source' => '',
                ],
            ],
        ];
    }
}
