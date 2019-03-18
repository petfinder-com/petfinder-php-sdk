<?php

declare(strict_types=1);

namespace Petfinder\Http\Plugin;

use Http\Client\Common\Plugin;
use Http\Client\Exception\HttpException;
use Http\Promise\Promise;
use Petfinder\Exception\ProblemDetailsException;
use Petfinder\Http\Json;
use Psr\Http\Message\RequestInterface;

class ProblemDetailsPlugin implements Plugin
{
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        /** @var Promise $promise */
        $promise = $next($request);

        return $promise->then(null, function (HttpException $exception) {
            $response = $exception->getResponse();

            if (false === strpos($response->getHeaderLine('Content-Type'), 'application/problem+json')) {
                throw $exception;
            }

            throw new ProblemDetailsException(
                Json::decode($response),
                $exception->getRequest(),
                $response,
                $exception
            );
        });
    }
}
