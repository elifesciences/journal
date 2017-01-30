<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ClientError;
use eLife\Patterns\ViewModel\NotFound;
use eLife\Patterns\ViewModel\ServerError;
use eLife\Patterns\ViewModel\ServiceUnavailable;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Throwable;
use function GuzzleHttp\Promise\promise_for;

final class ExceptionController extends Controller
{
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null) : Response
    {
        if ($request->attributes->get('showException', $this->get('kernel')->isDebug())) {
            return $this->get('twig.controller.exception')->showAction($request, $exception, $logger);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $arguments = $this->defaultPageArguments();

        foreach ($arguments as $key => $value) {
            $arguments[$key] = promise_for($value)
                ->otherwise(function () {
                    return null;
                });
        }

        $arguments['error'] = $this->createError($request, $exception->getStatusCode());

        return new Response($this->get('templating')->render('::exception.html.twig', $arguments));
    }

    private function createError(Request $request, int $statusCode) : ViewModel
    {
        $button = $this->createButton($request);

        switch ($statusCode) {
            case 404:
            case 410:
                return new NotFound($button);
            case 503:
                return new ServiceUnavailable($button);
        }

        if ($statusCode < 500) {
            return new ClientError($button);
        }

        return new ServerError($button);
    }

    private function createButton(Request $request)
    {
        if ('home' !== $request->attributes->get('_route')) {
            try {
                return Button::link('Back to homepage', $this->get('router')->generate('home'));
            } catch (Throwable $e) {
                // Do nothing.
            }
        }

        return null;
    }
}
