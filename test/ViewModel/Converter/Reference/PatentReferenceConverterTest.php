<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\Journal\ViewModel\Converter\Reference\PatentReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class PatentReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = PatentReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PatentReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'inventors' => [],
                    'title' => 'Light bulb',
                    'patentType' => '',
                    'country' => 'US',
                ],
            ],
        ];
    }
}
