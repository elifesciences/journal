<?php

namespace eLife\Journal\HttpClient;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Symfony's response implementations throw from their own destructor if a response with an
 * error status is garbage collected without ever having been read (their way of catching
 * "silently ignored failed request" bugs). Since SymfonyHttpClient defers reading a response
 * to its promise's wait function, a promise nobody ever calls ->wait() on abandons its
 * response without reading it. Cancelling such a response tells Symfony its outcome was
 * deliberately never checked, which silences that destructor check.
 *
 * That cancellation can't be left to this object's own __destruct(): at request/script
 * shutdown, PHP tears down every still-alive object, but not in an order that respects who
 * holds a reference to whom, so the wrapped response can be (and in practice is) destructed,
 * and thus throw, before this guard's own destructor ever runs. Instead every guard registers
 * itself here and a single shutdown function — queued once, ahead of PHP's own object teardown
 * — cancels whatever is still unread at that point.
 */
final class UnreadResponseGuard
{
    private static $pending = [];
    private static $shutdownRegistered = false;

    private $response;
    private $read = false;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        self::$pending[spl_object_id($this)] = $this;
        self::registerShutdownFunction();
    }

    public function read(): ResponseInterface
    {
        $this->markRead();

        return $this->response;
    }

    private function markRead(): void
    {
        if ($this->read) {
            return;
        }

        $this->read = true;
        unset(self::$pending[spl_object_id($this)]);
    }

    private function cancelIfUnread(): void
    {
        if ($this->read) {
            return;
        }

        $this->markRead();
        $this->response->cancel();
    }

    private static function registerShutdownFunction(): void
    {
        if (self::$shutdownRegistered) {
            return;
        }

        self::$shutdownRegistered = true;

        register_shutdown_function(static function () {
            foreach (self::$pending as $guard) {
                $guard->cancelIfUnread();
            }
        });
    }
}
