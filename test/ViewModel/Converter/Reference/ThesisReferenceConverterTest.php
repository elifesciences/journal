<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ThesisReference;
use eLife\Journal\ViewModel\Converter\Reference\ThesisReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class ThesisReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = ThesisReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ThesisReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'author' => [
                        'name' => [
                            'preferred' => 'Emmett Lathrop "Doc" Brown',
                            'index' => 'Brown, Emmett',
                        ],
                    ],
                    'title' => '...',
                    'publisher' => [
                        'name' => [''],
                    ],
                ],
            ],
        ];
    }
}
