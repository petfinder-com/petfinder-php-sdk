<?php

declare(strict_types=1);

namespace Petfinder\Api;

use Http\Client\HttpAsyncClient;
use Http\Message\RequestFactory;
use Http\Promise\Promise;
use Petfinder\Result;

abstract class AbstractApi
{
    /**
     * @var HttpAsyncClient
     */
    protected $client;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    public function __construct(HttpAsyncClient $client, RequestFactory $requestFactory)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    public function __call($name, $arguments): Result
    {
        $method = $name.'Async';

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_called_class(), $name));
        }

        /** @var Promise $promise */
        $promise = $this->$method(...$arguments);

        return $promise->wait();
    }

    protected function get(string $path, array $parameters = []): Promise
    {
        if ($parameters) {
            $path .= '?'.http_build_query($parameters);
        }

        $request = $this->requestFactory->createRequest('GET', $path);

        return $this->client->sendAsyncRequest($request);
    }
}
