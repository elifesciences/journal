<?php

namespace test\eLife\Journal;

use Csa\Bundle\GuzzleBundle\Cache\StorageAdapterInterface;
use eLife\ApiValidator\MessageValidator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ValidatingStorageAdapter implements StorageAdapterInterface
{
    private $storageAdapter;
    private $validator;

    public function __construct(StorageAdapterInterface $storageAdapter, MessageValidator $validator)
    {
        $this->storageAdapter = $storageAdapter;
        $this->validator = $validator;
    }

    public function fetch(RequestInterface $request)
    {
        return $this->storageAdapter->fetch($request);
    }

    public function save(RequestInterface $request, ResponseInterface $response)
    {
        $this->validator->validate($request);
        $this->validator->validate($response);

        $this->storageAdapter->save($request, $response);
    }
}
