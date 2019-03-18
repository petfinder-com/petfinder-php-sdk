<?php

declare(strict_types=1);

namespace Petfinder\Api;

use Http\Promise\Promise;
use Petfinder\Result;
use Petfinder\Http\Json;
use Psr\Http\Message\ResponseInterface;

/**
 * @method Result search(array $parameters = [])
 * @method Result show(int $id)
 */
class Animal extends AbstractApi
{
    public function searchAsync(array $parameters = []): Promise
    {
        return $this->get('/animals', $parameters)->then(function (ResponseInterface $response) {
            return new Result($response, Json::decode($response), 'animals');
        });
    }

    public function showAsync(int $id): Promise
    {
        return $this->get(sprintf('/animals/%d', $id))->then(function (ResponseInterface $response) {
            return new Result($response, Json::decode($response), 'animal');
        });
    }
}
