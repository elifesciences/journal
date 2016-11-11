<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\Journal\ViewModel\Converter\Reference\ConferenceProceedingReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class ConferenceProceedingReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = ConferenceProceedingReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ConferenceProceedingReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [],
                    'articleTitle' => 'Dependency Injection',
                    'conference' => [
                        'name' => ['phpDay'],
                    ],
                ],
            ],
        ];
    }
}
