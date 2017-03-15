<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';

$kernel = new AppKernel('demo', false);

Request::enableHttpMethodParameterOverride();

$kernel->run(Request::createFromGlobals());
