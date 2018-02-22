<?php

use eLife\Journal\AppKernel;
use Liuggio\Fastest\Process\EnvCommandCreator;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';
Debug::enable();

if (!empty($_GET[EnvCommandCreator::ENV_TEST_CHANNEL])) {
    setcookie(EnvCommandCreator::ENV_TEST_CHANNEL, $_GET[EnvCommandCreator::ENV_TEST_CHANNEL]);
    die;
}

if (!empty($_COOKIE[EnvCommandCreator::ENV_TEST_CHANNEL])) {
    putenv(EnvCommandCreator::ENV_TEST_CHANNEL.'='.$_COOKIE[EnvCommandCreator::ENV_TEST_CHANNEL]);
    unset($_COOKIE[EnvCommandCreator::ENV_TEST_CHANNEL]);
}

$kernel = new AppKernel('test', true);

$kernel->run(Request::createFromGlobals());
