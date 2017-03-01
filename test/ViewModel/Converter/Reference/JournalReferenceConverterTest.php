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

    public function references() : array
    {
        return [
            'minimum' => [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => $this->minimumAuthorsData(),
                    'articleTitle' => 'MNIST data set',
                    'journal' => 'eLife',
                    'pages' => '100',
                ],
            ],
            'complete' => [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Qianglin Fang',
                                'index' => 'Fang, Qianglin',
                            ],
                        ],
                    ],
                    'articleTitle' => 'MNIST data set',
                    'journal' => 'eLife',
                    'pages' => '100',
                    'volume' => '5',
                    'pmid' => 42,
                ],
            ],
        ];
    }
}
