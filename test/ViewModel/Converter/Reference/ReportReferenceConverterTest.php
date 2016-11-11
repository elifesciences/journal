<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ReportReference;
use eLife\Journal\ViewModel\Converter\Reference\ReportReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class ReportReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = ReportReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ReportReferenceConverter();
    }

    public function references()
    {
        return [
            'minimum' => [
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
            'complete' => [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [],
                    'title' => '...',
                    'publisher' => [
                        'name' => [''],
                    ],
                    'pmid' => 42,
                    'isbn' => '978-3-16-148410-0',
                ],
            ],
        ];
    }
}
