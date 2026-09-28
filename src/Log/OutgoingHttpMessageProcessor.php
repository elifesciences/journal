<?php

namespace eLife\Journal\Log;

use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\Exception\HttpProblem;
use GuzzleHttp\Psr7\Message;
use Psr\Http\Message\MessageInterface;
use Throwable;

/**
 * eLife\ApiClient\Log\HttpMessageProcessor only inspects the top-level logged
 * exception. When a HttpProblem (e.g. ApiTimeout) has been wrapped by another
 * exception before being logged, such as ApiTimeoutSubscriber wrapping it in
 * a HttpException, it's left unseen and no outgoing request/response is
 * attached to the log record. This walks the previous-exception chain to
 * find it regardless of wrapping.
 */
final class OutgoingHttpMessageProcessor
{
    public function __invoke(array $record) : array
    {
        if (!array_key_exists('exception', $record['context'])) {
            return $record;
        }

        $httpProblem = $this->findHttpProblem($record['context']['exception']);

        if (null === $httpProblem) {
            return $record;
        }

        if (!isset($record['extra']['request'])) {
            $record['extra']['request'] = $this->dumpHttpMessage($httpProblem->getRequest());
        }

        if ($httpProblem instanceof BadResponse && !isset($record['extra']['response'])) {
            $record['extra']['response'] = $this->dumpHttpMessage($httpProblem->getResponse());
        }

        return $record;
    }

    private function findHttpProblem(Throwable $exception = null)
    {
        while (null !== $exception) {
            if ($exception instanceof HttpProblem) {
                return $exception;
            }

            $exception = $exception->getPrevious();
        }

        return null;
    }

    private function dumpHttpMessage(MessageInterface $message) : string
    {
        return str_replace("\r", '', Message::toString($message));
    }
}
