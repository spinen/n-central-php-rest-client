<?php

namespace Tests\Unit\Support\Relations;

use PHPUnit\Framework\Attributes\Test;
use Spinen\Ncentral\Support\Collection;
use Spinen\Ncentral\Support\Relations\HasMany;

class HasManyTest extends RelationCase
{
    protected HasMany $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->relation = new HasMany($this->builder_mock, $this->model_mock);
    }

    #[Test]
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(HasMany::class, $this->relation);
    }

    #[Test]
    public function it_gets_the_child_as_the_result()
    {
        $results = new Collection([]);

        $this->builder_mock->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn($results);

        $this->assertEquals($results, $this->relation->getResults());
    }
}
