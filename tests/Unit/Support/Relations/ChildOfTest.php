<?php

namespace Tests\Unit\Support\Relations;

use PHPUnit\Framework\Attributes\Test;
use Spinen\Ncentral\Support\Relations\ChildOf;

class ChildOfTest extends RelationCase
{
    protected ChildOf $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder_mock->shouldReceive('whereId')
            ->withArgs(
                [
                    1,
                ]
            )
            ->andReturnSelf();

        $this->model_mock->shouldReceive('getAttribute')
            ->withArgs(
                [
                    'id',
                ]
            )
            ->andReturn(1);

        $this->relation = new ChildOf($this->builder_mock, $this->model_mock, 'id');
    }

    #[Test]
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(ChildOf::class, $this->relation);
    }

    #[Test]
    public function it_gets_the_child_as_the_result()
    {
        $this->assertEquals($this->relation->getChild(), $this->relation->getResults());
    }
}
