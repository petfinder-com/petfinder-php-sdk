<?php

declare(strict_types=1);

namespace Petfinder\Tests\Http\Plugin;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Http\Client\Common\Exception\ServerErrorException;
use Http\Client\Promise\HttpFulfilledPromise;
use Http\Client\Promise\HttpRejectedPromise;
use Petfinder\Exception\ProblemDetailsException;
use Petfinder\Http\Plugin\ProblemDetailsPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Http\Plugin\ProblemDetailsPlugin
 */
class ProblemDetailsPluginTest extends TestCase
{
    /**
     * @covers ::handleRequest
     */
    public function testHandleRequestSuccess(): void
    {
        $request = new Request('GET', '/test');
        $response = new Response(204);

        $next = function () use ($response) {
            return new HttpFulfilledPromise($response);
        };

        $promise = (new ProblemDetailsPlugin())->handleRequest($request, $next, $next);
        $result = $promise->wait();

        $this->assertSame($result, $response);
    }

    /**
     * @covers ::handleRequest
     */
    public function testHandleRequestErrorResponseNotProblemDetails(): void
    {
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('Test Error');

        $request = new Request('GET', '/test');
        $exception = new ServerErrorException('Test Error', $request, new Response());
        $next = function () use ($exception) {
            return new HttpRejectedPromise($exception);
        };

        $promise = (new ProblemDetailsPlugin())->handleRequest($request, $next, $next);
        $promise->wait();
    }

    /**
     * @covers ::handleRequest
     */
    public function testHandleRequestProblemDetailsResponse(): void
    {
        $request = new Request('GET', '/test');
        $response = (new Response(400))
            ->withHeader('Content-Type', 'application/problem+json')
            ->withBody(stream_for('{"type":"test","status":400,"title":"Invalid Request","detail":"Request contains invalid parameters","invalid-params":[{"in":"query","path":"test","message":"Invalid"}]}'));
        $exception = new ServerErrorException('Test Error', $request, $response);
        $next = function () use ($exception) {
            return new HttpRejectedPromise($exception);
        };

        $promise = (new ProblemDetailsPlugin())->handleRequest($request, $next, $next);

        try {
            $promise->wait();
        } catch (ProblemDetailsException $exception) {
            $this->assertSame($request, $exception->getRequest());
            $this->assertSame($response, $exception->getResponse());
            $this->assertEquals('Invalid Request: Request contains invalid parameters', $exception->getMessage());
            $this->assertEquals('test', $exception->type);
            $this->assertEquals(400, $exception->status);
            $this->assertEquals('Invalid Request', $exception->title);
            $this->assertEquals('Request contains invalid parameters', $exception->detail);
            $this->assertEquals([['in' => 'query', 'path' => 'test', 'message' => 'Invalid']], $exception->invalidParams);
        }
    }
}
