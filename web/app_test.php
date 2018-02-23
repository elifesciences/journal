<?php

use eLife\Journal\AppKernel;
use Liuggio\Fastest\Process\EnvCommandCreator;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';
Debug::enable();

if (!empty($_GET['JOURNAL_INSTANCE'])) {
    setcookie('JOURNAL_INSTANCE', $_GET['JOURNAL_INSTANCE']);
    die;
}

if (!empty($_COOKIE['JOURNAL_INSTANCE'])) {
    putenv('JOURNAL_INSTANCE'.'='.$_COOKIE['JOURNAL_INSTANCE']);
    unset($_COOKIE['JOURNAL_INSTANCE']);
}

$kernel = new AppKernel('test', true);

$kernel->run(Request::createFromGlobals());
