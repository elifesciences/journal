<?php

namespace eLife\Journal\Helper;

use Countable;
use Exception;
use InvalidArgumentException;

final class Callback
{
    private $callback;

    private function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public static function isInstanceOf(string $class) : Callback
    {
        return new self(function ($object) use ($class) {
            return $object instanceof $class;
        });
    }

    public static function methodIsInstanceOf(string $method, string $class) : Callback
    {
        return new self(function ($object) use ($method, $class) {
            return call_user_func([$object, $method]) instanceof $class;
        });
    }

    public static function isNotEmpty() : Callback
    {
        return new self(function ($test) {
            if (empty($test) || ((is_array($test) || $test instanceof Countable) && 0 === count($test)) || ($test instanceof Paginator && 0 === $test->getTotal())) {
                return false;
            }

            return true;
        });
    }

    public static function mustBeInstanceOf(string $class, Exception $exception = null) : Callback
    {
        if (null === $exception) {
            $exception = new InvalidArgumentException('Is not an instance of '.$class);
        }

        return new self(function ($object) use ($class, $exception) {
            if (false === $object instanceof $class) {
                throw $exception;
            }

            return $object;
        });
    }

    public static function mustNotBeEmpty(Exception $exception = null) : Callback
    {
        if (null === $exception) {
            $exception = new InvalidArgumentException('Is empty');
        }

        return new self(function ($object) use ($exception) {
            if (empty($object) || ((is_array($object) || $object instanceof Countable) && 0 === count($object)) || ($object instanceof Paginator && 0 === $object->getTotal())) {
                throw $exception;
            }

            return $object;
        });
    }

    public static function methodMustNotBeEmpty(string $method, Exception $exception = null) : Callback
    {
        if (null === $exception) {
            $exception = new InvalidArgumentException('Is empty');
        }

        return new self(function ($object) use ($method, $exception) {
            $test = call_user_func([$object, $method]);

            if (empty($test) || ((is_array($test) || $test instanceof Countable) && 0 === count($test)) || ($test instanceof Paginator && 0 === $test->getTotal())) {
                throw $exception;
            }

            return $object;
        });
    }

    public static function apply(string $function) : Callback
    {
        return new self(function ($object) use ($function) {
            return $function($object);
        });
    }

    public static function method(string $method, ...$values) : Callback
    {
        return new self(function ($object) use ($method, $values) {
            return call_user_func([$object, $method], ...$values);
        });
    }

    public static function call(callable $callable, ...$values) : Callback
    {
        return new self(function () use ($callable, $values) {
            return call_user_func($callable, ...$values);
        });
    }

    public static function emptyOr(callable $callback) : Callback
    {
        return new self(function ($object) use ($callback) {
            if (empty($object) || ((is_array($object) || $object instanceof Countable) && 0 === count($object)) || ($object instanceof Paginator && 0 === $object->getTotal())) {
                return null;
            }

            return call_user_func($callback, $object);
        });
    }

    public static function methodEmptyOr(string $method, callable $callback) : Callback
    {
        return new self(function ($object) use ($method, $callback) {
            $test = call_user_func([$object, $method]);

            if (empty($test) || ((is_array($test) || $test instanceof Countable) && 0 === count($test)) || ($test instanceof Paginator && 0 === $test->getTotal())) {
                return null;
            }

            return call_user_func($callback, $object);
        });
    }

    public function __invoke()
    {
        return call_user_func($this->callback, ...func_get_args());
    }
}
