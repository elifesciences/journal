<?php

use eLife\Journal\AppKernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

umask(0002);

require_once __DIR__.'/../vendor/autoload.php';

$_SERVER['APP_ENV'] = $_SERVER['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] = in_array($_SERVER['APP_ENV'], ['ci', 'dev']);
$_SERVER['APP_ELB'] = $_SERVER['APP_ELB'] ?? false;

if ('ci' === $_SERVER['APP_ENV']) {
    if (!empty($_GET['JOURNAL_INSTANCE'])) {
        setcookie('JOURNAL_INSTANCE', $_GET['JOURNAL_INSTANCE']);
        die;
    }

    if (!empty($_COOKIE['JOURNAL_INSTANCE'])) {
        putenv("JOURNAL_INSTANCE={$_COOKIE['JOURNAL_INSTANCE']}");
        unset($_COOKIE['JOURNAL_INSTANCE']);
    }
}

if ($_SERVER['APP_DEBUG']) {
    Debug::enable();
}

$kernel = new AppKernel($_SERVER['APP_ENV'], $_SERVER['APP_DEBUG']);

Request::enableHttpMethodParameterOverride();

if ($_SERVER['APP_ELB']) {
    Request::setTrustedProxies([$_SERVER['REMOTE_ADDR']], Request::HEADER_X_FORWARDED_AWS_ELB);
}

$kernel->run(Request::createFromGlobals());
