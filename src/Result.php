<?php

declare(strict_types=1);

namespace Petfinder;

use JmesPath\Env;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Result implements ResponseInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string|null
     */
    private $key;

    public function __construct(ResponseInterface $response, array $data = [], ?string $key = null)
    {
        $this->response = $response;
        $this->data = $data;
        $this->key = $key;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function search(string $expression)
    {
        return Env::search($expression, $this->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        return new static($this->response->withProtocolVersion($version), $this->data, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        return new static($this->response->withHeader($name, $value), $this->data, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        return new static($this->response->withAddedHeader($name, $value), $this->data, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        return new static($this->response->withoutHeader($name), $this->data, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        return new static($this->response->withBody($body), $this->data, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return new static($this->response->withStatus($code, $reasonPhrase), $this->data, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->key ? $this->data[$this->key] : $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->key ? $this->data[$this->key] : $this->data);
    }
}
