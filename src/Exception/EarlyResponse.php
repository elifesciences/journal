<?php

namespace eLife\Journal\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class EarlyResponse extends UnexpectedValueException
{
    private $response;

    public function __construct(Response $response, Exception $previous = null)
    {
        parent::__construct('Early response', 0, $previous);

        $this->response = $response;
    }

    final public function getResponse() : Response
    {
        return $this->response;
    }
}
