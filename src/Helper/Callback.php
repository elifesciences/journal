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
            if (empty($object) || ((is_array($object) || $object instanceof Countable) && 0 === count($object))) {
                throw $exception;
            }

            return $object;
        });
    }

    public static function method(string $method, ...$values) : Callback
    {
        return new self(function ($object) use ($method, $values) {
            return call_user_func([$object, $method], ...$values);
        });
    }

    public static function emptyOr(callable $callback) : Callback
    {
        return new self(function ($object) use ($callback) {
            if (empty($object) || ((is_array($object) || $object instanceof Countable) && 0 === count($object))) {
                return null;
            }

            return call_user_func($callback, $object);
        });
    }

    public static function methodEmptyOr(string $method, callable $callback) : Callback
    {
        return new self(function ($object) use ($method, $callback) {
            $test = call_user_func([$object, $method]);

            if (empty($test) || ((is_array($test) || $test instanceof Countable) && 0 === count($test))) {
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
