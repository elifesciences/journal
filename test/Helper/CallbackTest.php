<?php

namespace test\eLife\Journal\Helper;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use InvalidArgumentException;
use LogicException;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;
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
    public function it_creates_is_not_empty_with_not_empty($item)
    {
        $callback = Callback::isNotEmpty();

        $this->assertTrue($callback($item));
    }

    /**
     * @test
     * @dataProvider emptyProvider
     */
    public function it_creates_is_not_empty_with_empty($item)
    {
        $callback = Callback::isNotEmpty();

        $this->assertFalse($callback($item));
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
     * @dataProvider notEmptyProvider
     */
    public function it_creates_method_must_not_be_empty_with_not_empty($item)
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

        $callback = Callback::methodMustNotBeEmpty('get');

        $this->assertSame($object, $callback($object));
    }

    /**
     * @test
     * @dataProvider emptyProvider
     */
    public function it_creates_method_must_not_be_empty_with_empty($item)
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

        $callback = Callback::methodMustNotBeEmpty('get');

        $this->expectException(InvalidArgumentException::class);

        $callback($object);
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
     */
    public function it_creates_call()
    {
        $callback = Callback::call('range', 2, 5);

        $this->assertSame(range(2, 5), $callback('foo'));
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
        yield 'empty paginator' => [new Paginator('title', new Pagerfanta(new NullAdapter()), function () {
            return 'foo';
        })];
        yield '0' => [0];
        yield 'null' => [null];
    }
}
