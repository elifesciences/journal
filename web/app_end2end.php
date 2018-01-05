<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';

$kernel = new AppKernel('end2end', false);

Request::enableHttpMethodParameterOverride();

Request::setTrustedProxies([$_SERVER['REMOTE_ADDR']], Request::HEADER_X_FORWARDED_AWS_ELB);

$kernel->run(Request::createFromGlobals());
