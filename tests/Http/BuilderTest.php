<?php

declare(strict_types=1);

namespace Petfinder\Tests\Http;

use GuzzleHttp\Psr7\Request;
use Http\Client\Common\Plugin\QueryDefaultsPlugin;
use Http\Client\Common\Plugin\RedirectPlugin;
use Http\Client\HttpAsyncClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\RequestFactory;
use Http\Mock\Client;
use Petfinder\Http\Builder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Http\Builder
 */
class BuilderTest extends TestCase
{
    /**
     * @var Client
     */
    private $http;

    /**
     * @var Builder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->http = new Client();
        $this->builder = new Builder($this->http);
    }

    /**
     * @covers ::__construct
     * @covers ::getHttpClient
     */
    public function testAutomaticallyFindsHttpClient(): void
    {
        $builder = new Builder();

        $this->assertInstanceOf(HttpAsyncClient::class, $builder->getHttpClient());
    }

    /**
     * @covers ::__construct
     * @covers ::getRequestFactory
     */
    public function testRequestFactory(): void
    {
        $this->assertInstanceOf(RequestFactory::class, $this->builder->getRequestFactory());

        $factory = new GuzzleMessageFactory();
        $builder = new Builder(null, $factory);

        $this->assertSame($factory, $builder->getRequestFactory());
    }

    /**
     * @covers ::getHttpClient
     */
    public function testWillReuseClient(): void
    {
        $client = $this->builder->getHttpClient();

        $this->assertSame($client, $this->builder->getHttpClient());
    }

    /**
     * @covers ::addPlugin
     */
    public function testAddPlugin(): void
    {
        $client = $this->builder->getHttpClient();
        $this->builder->addPlugin(new QueryDefaultsPlugin(['foo' => 'bar']));
        $this->assertNotSame($client, $this->builder->getHttpClient());

        $this->builder->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));
        $this->assertEquals('foo=bar', $this->http->getLastRequest()->getUri()->getQuery());
    }

    /**
     * @covers ::removePlugin
     */
    public function testRemovePlugin(): void
    {
        $this->builder->addPlugin(new QueryDefaultsPlugin(['foo' => 'bar']));
        $client = $this->builder->getHttpClient();

        $this->builder->removePlugin(QueryDefaultsPlugin::class);
        $this->assertNotSame($client, $this->builder->getHttpClient());

        $this->builder->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));
        $this->assertEquals('', $this->http->getLastRequest()->getUri()->getQuery());
    }

    /**
     * @covers ::removePlugin
     */
    public function testRemovePluginNotExist(): void
    {
        $client = $this->builder->getHttpClient();
        $this->builder->removePlugin(RedirectPlugin::class);

        $this->assertSame($client, $this->builder->getHttpClient());
    }

    /**
     * @covers ::clearHeaders
     * @covers ::addHeaders
     */
    public function testHeaders(): void
    {
        $this->builder->addHeaders(['X-Foo' => 'bar', 'X-Bar' => 'foo']);

        $this->builder->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));
        $this->assertEquals('bar', $this->http->getLastRequest()->getHeaderLine('X-Foo'));
        $this->assertEquals('foo', $this->http->getLastRequest()->getHeaderLine('X-Bar'));
        $this->assertEquals('petfinder-php-sdk/v1.0 (https://github.com/petfinder-com/petfinder-php-sdk)', $this->http->getLastRequest()->getHeaderLine('X-Api-Sdk'));

        $this->builder->addHeaders(['X-Baz' => 'baz']);

        $this->builder->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));
        $this->assertEquals('baz', $this->http->getLastRequest()->getHeaderLine('X-Baz'));

        $this->builder->clearHeaders();

        $this->builder->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));
        $this->assertEmpty($this->http->getLastRequest()->getHeaders());
    }

    /**
     * @covers ::authenticate
     * @covers ::isAuthenticated
     */
    public function testAuthenticate(): void
    {
        $this->assertFalse($this->builder->isAuthenticated());

        $this->builder->authenticate('mytoken');
        $this->builder->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));

        $this->assertEquals('Bearer mytoken', $this->http->getLastRequest()->getHeaderLine('Authorization'));
        $this->assertTrue($this->builder->isAuthenticated());
    }
}
