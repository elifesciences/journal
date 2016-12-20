<?php

namespace test\eLife\Journal\Helper;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\Journal\Helper\Callback;
use InvalidArgumentException;
use LogicException;
use PHPUnit_Framework_TestCase;
use Traversable;

final class CallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_is_instance_of()
    {
        $callback = Callback::isInstanceOf(PHPUnit_Framework_TestCase::class);

        $this->assertTrue($callback($this));
        $this->assertFalse($callback('foo'));
    }

    /**
     * @test
     */
    public function it_creates_must_be_an_instance_of()
    {
        $callback = Callback::mustBeInstanceOf(PHPUnit_Framework_TestCase::class);

        $this->assertSame($this, $callback($this));

        $this->expectException(InvalidArgumentException::class);

        $callback('foo');
    }

    /**
     * @test
     * @dataProvider notEmptyProvider
     */
    public function it_creates_must_not_be_empty_with_not_empty($item)
    {
        $callback = Callback::mustNotBeEmpty();

        $this->assertSame($item, $callback($item));
    }

    /**
     * @test
     * @dataProvider emptyProvider
     */
    public function it_creates_must_not_be_empty_with_empty($item)
    {
        $callback = Callback::mustNotBeEmpty();

        $this->expectException(InvalidArgumentException::class);

        $callback($item);
    }

    /**
     * @test
     */
    public function it_creates_method()
    {
        $date = new DateTimeImmutable();

        $callback1 = Callback::method('getTimestamp');
        $callback2 = Callback::method('format', 'c');

        $this->assertSame($date->getTimestamp(), $callback1($date));
        $this->assertSame($date->format('c'), $callback2($date));
    }

    /**
     * @test
     * @dataProvider notEmptyProvider
     */
    public function it_creates_empty_or_with_not_empty($item)
    {
        $callback = Callback::emptyOr(function ($item) {
            return $item;
        });

        $this->assertSame($item, $callback($item));
    }

    /**
     * @test
     * @dataProvider emptyProvider
     */
    public function it_creates_empty_or_with_empty($item)
    {
        $callback = Callback::emptyOr(function () {
            throw new LogicException('Should not be called');
        });

        $this->assertNull($callback($item));
    }

    /**
     * @test
     * @dataProvider notEmptyProvider
     */
    public function it_creates_method_empty_or_with_not_empty($item)
    {
        $object = new class($item) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

            public function get()
            {
                return $this->item;
            }
        };

        $callback = Callback::methodEmptyOr('get', function ($item) {
            return $item;
        });

        $this->assertSame($object, $callback($object));
    }

    /**
     * @test
     * @dataProvider emptyProvider
     */
    public function it_creates_method_empty_or_with_empty($item)
    {
        $object = new class($item) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

            public function get()
            {
                return $this->item;
            }
        };

        $callback = Callback::methodEmptyOr('get', function () {
            throw new LogicException('Should not be called');
        });

        $this->assertNull($callback($object));
    }

    public function notEmptyProvider() : Traversable
    {
        yield 'sequence' => [new ArraySequence(['foo'])];
        yield 'array' => [['foo']];
        yield 'string' => ['foo'];
        yield 'int' => [1];
    }

    public function emptyProvider() : Traversable
    {
        yield 'empty sequence' => [new EmptySequence()];
        yield 'empty array' => [[]];
        yield 'empty string' => [''];
        yield '0' => [0];
        yield 'null' => [null];
    }

    /**
     * @test
     * @dataProvider splitProvider
     */
    public function it_creates_split(int $groups, $input, array $expected)
    {
        $callback = Callback::split($groups);

        $this->assertSame($expected, $callback($input));
    }

    public function splitProvider() : Traversable
    {
        yield '1-item array into 2' => [2, ['foo'], [['foo']]];
        yield '1-item sequence into 2' => [2, new ArraySequence(['foo']), [['foo']]];
        yield '2-item array into 2' => [2, ['foo', 'bar'], [['foo'], ['bar']]];
        yield '2-item sequence into 2' => [2, new ArraySequence(['foo', 'bar']), [['foo'], ['bar']]];
        yield '3-item array into 2' => [2, ['foo', 'bar', 'baz'], [['foo', 'bar'], ['baz']]];
        yield '3-item sequence into 2' => [2, new ArraySequence(['foo', 'bar', 'baz']), [['foo', 'bar'], ['baz']]];
    }
}
