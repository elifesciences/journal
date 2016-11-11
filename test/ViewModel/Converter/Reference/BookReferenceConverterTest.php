<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\Journal\ViewModel\Converter\Reference\BookReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class BookReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = BookReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BookReferenceConverter();
    }

    public function references()
    {
        return [
            [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [],
                    'editors' => [],
                    'bookTitle' => 'Encyclopedia Galactica',
                    'publisher' => [
                        'name' => ['Cambridge University Press'],
                    ],
                ],
            ],
        ];
    }
}
