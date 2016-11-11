<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\Journal\ViewModel\Converter\Reference\JournalReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class JournalReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = JournalReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new JournalReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [],
                    'articleTitle' => 'MNIST data set',
                    'journal' => [
                        'name' => ['eLife'],
                    ],
                    'pages' => '100',
                ],
            ],
        ];
    }
}
