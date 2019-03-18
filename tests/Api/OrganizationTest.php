<?php

declare(strict_types=1);

namespace Petfinder\Tests\Api;

use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Mock\Client;
use Http\Promise\Promise;
use Petfinder\Api\Organization;
use Petfinder\Result;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Api\Organization
 */
class OrganizationTest extends TestCase
{
    /**
     * @var Client
     */
    private $http;

    /**
     * @var Organization
     */
    private $api;

    protected function setUp(): void
    {
        $this->http = new Client();
        $this->api = new Organization($this->http, new GuzzleMessageFactory());
    }

    /**
     * @covers ::searchAsync
     */
    public function testSearch(): void
    {
        $this->http->addResponse((new Response())->withBody(stream_for('{"organizations":[{"name":"Test"},{"name":"FooTest"}]}')));

        $promise = $this->api->searchAsync(['name' => 'test']);
        $this->assertInstanceOf(Promise::class, $promise);

        $result = $promise->wait();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('Test', $result->search('organizations[0].name'));

        $this->assertEquals('/organizations', $this->http->getLastRequest()->getUri()->getPath());
        $this->assertEquals('name=test', $this->http->getLastRequest()->getUri()->getQuery());
    }

    /**
     * @covers ::showAsync
     */
    public function testShow(): void
    {
        $this->http->addResponse((new Response())->withBody(stream_for('{"organization":{"name":"Test","location":"Minneapolis, MN"}}')));

        $promise = $this->api->showAsync('MN1234');
        $this->assertInstanceOf(Promise::class, $promise);

        $result = $promise->wait();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('Minneapolis, MN', $result->search('organization.location'));

        $this->assertEquals('/organizations/MN1234', $this->http->getLastRequest()->getUri()->getPath());
    }
}
