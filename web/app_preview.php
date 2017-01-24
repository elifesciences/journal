<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('preview', false);
$kernel->loadClassCache();

Request::enableHttpMethodParameterOverride();

$kernel->run(Request::createFromGlobals());
