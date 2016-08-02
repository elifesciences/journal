<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

Request::enableHttpMethodParameterOverride();

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
