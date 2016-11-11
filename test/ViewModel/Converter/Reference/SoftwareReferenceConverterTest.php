<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\SoftwareReference;
use eLife\Journal\ViewModel\Converter\Reference\SoftwareReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class SoftwareReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = SoftwareReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SoftwareReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [],
                    'title' => '...',
                    'publisher' => [
                        'name' => [''],
                    ],
                ],
            ],
        ];
    }
}
