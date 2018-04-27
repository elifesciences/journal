<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

$kernel = new AppKernel('continuumtestpreview', false);

Request::enableHttpMethodParameterOverride();

$kernel->run(Request::createFromGlobals());
