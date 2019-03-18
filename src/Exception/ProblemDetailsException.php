<?php

declare(strict_types=1);

namespace Petfinder\Exception;

use Http\Client\Exception\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @property string     $type
 * @property int        $status
 * @property string     $title
 * @property string     $detail
 * @property array|null $invalidParams
 */
class ProblemDetailsException extends HttpException
{
    /**
     * @var array
     */
    private $data;

    public function __construct(
        array $data,
        RequestInterface $request,
        ResponseInterface $response,
        ?\Exception $previous = null
    ) {
        parent::__construct(sprintf('%s: %s', $data['title'], $data['detail']), $request, $response, $previous);

        $this->data = $data;
    }

    public function __get(string $name)
    {
        if (!ctype_lower($name)) {
            $kebab = (string) preg_replace('/([A-Z])/', '-$1', lcfirst($name));
            $name = strtolower($kebab);
        }

        return $this->data[$name] ?? null;
    }
}
