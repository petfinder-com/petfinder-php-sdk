<?php

declare(strict_types=1);

namespace Petfinder\Http;

use Petfinder\Exception\JsonException;
use Psr\Http\Message\ResponseInterface;

abstract class Json
{
    public static function decode(ResponseInterface $response): array
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException(sprintf('json_decode error: %s', json_last_error_msg()));
        }

        return $data;
    }

    public static function encode($data, int $options = 0): string
    {
        $json = json_encode($data, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException(sprintf('json_encode error: %s', json_last_error_msg()));
        }

        return (string) $json;
    }
}
