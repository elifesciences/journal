<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\UnknownReference;
use eLife\Journal\ViewModel\Converter\Reference\UnknownReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class UnknownReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = UnknownReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new UnknownReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [
                    ],
                    'title' => '...',
                ],
            ],
        ];
    }
}
