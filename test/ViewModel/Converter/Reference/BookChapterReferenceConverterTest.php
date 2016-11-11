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
            'minimum' => [
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
            'complete' => [
                [
                    'id' => '',
                    'date' => '2016-01-01',
                    'authors' => [
                        [
                            'type' => 'group',
                            'name' => 'The differential calculus project',
                            'people' => [
                                [
                                    'name' => [
                                        'preferred' => 'Isaac Newton',
                                        'index' => 'Newton, Isaac',
                                    ],
                                ],
                                [
                                    'name' => [
                                        'preferred' => 'Gottfried Wilhelm (von) Leibniz ',
                                        'index' => 'Leibniz, Gottfried',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'editors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Isaac Newton',
                                'index' => 'Newton, Isaac',
                            ],
                        ],
                    ],
                    'editorsEtAl' => true,
                    'bookTitle' => 'Encyclopedia Galactica',
                    'chapterTitle' => 'Chapter 1',
                    'publisher' => [
                        'name' => ['Cambridge University Press'],
                    ],
                    'pages' => '10000',
                    'volume' => '1',
                    'edition' => '2',
                    'pmid' => 42,
                    'isbn' => '978-3-16-148410-0',
                ],
            ],
        ];
    }
}
