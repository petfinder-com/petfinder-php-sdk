<?php

declare(strict_types=1);

namespace Petfinder\Tests\Integration;

use Petfinder\Exception\ProblemDetailsException;
use Petfinder\Result;

/**
 * @group integration
 * @coversNothing
 */
class AnimalTest extends TestCase
{
    public function testSearch(): void
    {
        $result = $this->client->animal->search();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(1, count($result));
        $this->assertArrayHasKey('name', $result->search('animals[0]'));
    }

    public function testSearchWithParameters(): void
    {
        $result = $this->client->animal->search(['type' => 'Dog']);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(1, count($result));
        $this->assertEquals(['Dog'], array_unique($result->search('animals[].type')));
    }

    public function testSearchInvalidParameters(): void
    {
        $this->expectException(ProblemDetailsException::class);

        $this->client->animal->search(['type' => 'Foobar']);
    }

    public function testShow(): void
    {
        $id = $this->client->animal->search()->search('animals[0].id');
        $result = $this->client->animal->show($id);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($id, $result->search('animal.id'));
        $this->assertArrayHasKey('type', $result['animal']);
    }

    public function testShowNotFound(): void
    {
        $this->expectException(ProblemDetailsException::class);

        $this->client->animal->show(1);
    }
}
