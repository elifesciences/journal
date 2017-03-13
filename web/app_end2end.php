<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('end2end', false);
$kernel->loadClassCache();

Request::enableHttpMethodParameterOverride();
Request::setTrustedProxies([$_SERVER['REMOTE_ADDR']]); // ELB

$kernel->run(Request::createFromGlobals());
