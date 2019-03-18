<?php

declare(strict_types=1);

namespace Petfinder\Api;

use Http\Promise\Promise;
use Petfinder\Http\Json;
use Petfinder\Result;
use Psr\Http\Message\ResponseInterface;

/**
 * @method Result search(array $parameters = [])
 * @method Result show(string $id)
 */
class Organization extends AbstractApi
{
    public function searchAsync(array $parameters = []): Promise
    {
        return $this->get('/organizations', $parameters)->then(function (ResponseInterface $response) {
            return new Result($response, Json::decode($response), 'organizations');
        });
    }

    public function showAsync(string $id): Promise
    {
        return $this->get(sprintf('/organizations/%s', $id))->then(function (ResponseInterface $response) {
            return new Result($response, Json::decode($response), 'organization');
        });
    }
}
