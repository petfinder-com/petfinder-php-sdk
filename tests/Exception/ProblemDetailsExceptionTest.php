<?php

declare(strict_types=1);

namespace Petfinder\Tests\Exception;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Petfinder\Exception\ProblemDetailsException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Exception\ProblemDetailsException
 */
class ProblemDetailsExceptionTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testDefaultMessage(): void
    {
        $e = new ProblemDetailsException($this->getProblemDetailsData(), new Request('GET', '/'), new Response());

        $this->assertEquals('Unknown Error: An unknown error occurred', $e->getMessage());
    }

    /**
     * @covers ::__get
     */
    public function testFetchData(): void
    {
        $e = new ProblemDetailsException($this->getProblemDetailsData(), new Request('GET', '/'), new Response());

        $this->assertEquals('https://httpstatuses.com/401', $e->type);
        $this->assertNull($e->invalidParams);
    }

    public function testFetchInvalidParams(): void
    {
        $e = new ProblemDetailsException($this->getProblemDetailsData(true), new Request('GET', '/'), new Response());

        $this->assertEquals([['in' => 'query', 'path' => 'test', 'message' => 'Invalid param']], $e->invalidParams);
    }

    private function getProblemDetailsData(bool $invalidParams = false)
    {
        $data = [
            'type' => 'https://httpstatuses.com/401',
            'status' => 401,
            'title' => 'Unknown Error',
            'detail' => 'An unknown error occurred',
        ];

        if ($invalidParams) {
            $data['invalid-params'] = [[
                'in' => 'query',
                'path' => 'test',
                'message' => 'Invalid param',
            ]];
        }

        return $data;
    }
}
