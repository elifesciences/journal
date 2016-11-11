<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\Journal\ViewModel\Converter\Reference\BookChapterReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class BookChapterReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $class = BookChapterReference::class;
    protected $viewModelClass = Reference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BookChapterReferenceConverter();
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
                    'chapterTitle' => 'Chapter 1',
                    'publisher' => [
                        'name' => ['Cambridge University Press'],
                    ],
                    'pages' => '10000',
                ],
            ],
        ];
    }
}
