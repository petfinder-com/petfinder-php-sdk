<?php

declare(strict_types=1);

namespace Petfinder\Api;

use Http\Promise\Promise;
use Petfinder\Result;
use Petfinder\Http\Json;
use Psr\Http\Message\ResponseInterface;

/**
 * @method Result types()
 * @method Result type(string $name)
 * @method Result breeds(string $type)
 */
class AnimalData extends AbstractApi
{
    public function typesAsync(): Promise
    {
        return $this->get('/types')->then(function (ResponseInterface $response) {
            return new Result($response, Json::decode($response), 'types');
        });
    }

    public function typeAsync(string $name): Promise
    {
        return $this->get(sprintf('/types/%s', $name))->then(function (ResponseInterface $response) {
            return new Result($response, Json::decode($response), 'type');
        });
    }

    public function breedsAsync(string $type): Promise
    {
        return $this->get(sprintf('/types/%s/breeds', $type))->then(function (ResponseInterface $response) {
            return new Result($response, Json::decode($response), 'breeds');
        });
    }
}
