<?php

declare(strict_types=1);

namespace Petfinder\Tests\Integration;

use Petfinder\Result;

/**
 * @group integration
 * @coversNothing
 */
class AnimalDataTest extends TestCase
{
    public function testTypes(): void
    {
        $result = $this->client->animalData->types();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertContains('Dog', $result->search('types[].name'));
    }

    public function testType(): void
    {
        $result = $this->client->animalData->type('Dog');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('Dog', $result->search('type.name'));
        $this->assertArrayHasKey('coats', $result['type']);
        $this->assertArrayHasKey('colors', $result['type']);
        $this->assertArrayHasKey('genders', $result['type']);
    }

    public function testBreeds(): void
    {
        $result = $this->client->animalData->breeds('Dog');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(1, count($result));
        $this->assertContains('Corgi', $result->search('breeds[].name'));
    }
}
