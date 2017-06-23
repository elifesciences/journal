<?php

namespace eLife\Journal\Router;

interface ParameterResolver
{
    public function resolve(string $route, array $parameters) : array;
}
