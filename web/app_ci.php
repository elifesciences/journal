<?php

use eLife\Journal\AppKernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';
Debug::enable();

if (!empty($_GET['JOURNAL_INSTANCE'])) {
    setcookie('JOURNAL_INSTANCE', $_GET['JOURNAL_INSTANCE']);
    die;
}

if (!empty($_COOKIE['JOURNAL_INSTANCE'])) {
    putenv('JOURNAL_INSTANCE'.'='.$_COOKIE['JOURNAL_INSTANCE']);
    unset($_COOKIE['JOURNAL_INSTANCE']);
}

$kernel = new AppKernel('ci', true);

$kernel->run(Request::createFromGlobals());
