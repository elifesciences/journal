<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\Journal\ViewModel\Converter\Reference\PeriodicalReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class PeriodicalReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = PeriodicalReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PeriodicalReferenceConverter();
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
                    'periodical' => [
                        'name' => ['The Onion'],
                    ],
                    // TODO: good example for pages
                    'pages' => '100',
                ],
            ],
        ];
    }
}
