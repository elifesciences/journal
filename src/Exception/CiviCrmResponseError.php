<?php

namespace eLife\Journal\Exception;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class CiviCrmResponseError extends \Exception
{
    private $response;

    public function __construct(string $message, ResponseInterface $response, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->response = $response;
    }

    final public function getResponse() : ResponseInterface
    {
        return $this->response;
    }
}
