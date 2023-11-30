<?php

namespace Spinen\Ncentral\Support;

use Tests\TestCase;

/**
 * Class CollectionTestTest
 */
class CollectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Collection::class, new Collection());
    }
}
