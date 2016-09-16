<?php

use eLife\Journal\AppKernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';
Debug::enable();

$kernel = new AppKernel('test', true);
$kernel->loadClassCache();

$kernel->run(Request::createFromGlobals());
