<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';

$kernel = new AppKernel('prod', false);

Request::enableHttpMethodParameterOverride();
Request::setTrustedProxies([$_SERVER['REMOTE_ADDR']]); // ELB

$kernel->run(Request::createFromGlobals());
