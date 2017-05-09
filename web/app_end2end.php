<?php

use eLife\Journal\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';

$kernel = new AppKernel('end2end', false);

Request::enableHttpMethodParameterOverride();

// ELB
Request::setTrustedProxies([$_SERVER['REMOTE_ADDR']]);
Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
Request::setTrustedHeaderName(Request::HEADER_CLIENT_IP, 'X-Forwarded-For');
Request::setTrustedHeaderName(Request::HEADER_CLIENT_HOST, null);
Request::setTrustedHeaderName(Request::HEADER_CLIENT_PORT, 'X-Forwarded-Port');
Request::setTrustedHeaderName(Request::HEADER_CLIENT_PROTO, 'X-Forwarded-Proto');

$kernel->run(Request::createFromGlobals());
