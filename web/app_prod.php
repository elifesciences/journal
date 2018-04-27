<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

$kernel = new AppKernel('prod', false);

Request::enableHttpMethodParameterOverride();

Request::setTrustedProxies([$_SERVER['REMOTE_ADDR']], Request::HEADER_X_FORWARDED_AWS_ELB);

$kernel->run(Request::createFromGlobals());
