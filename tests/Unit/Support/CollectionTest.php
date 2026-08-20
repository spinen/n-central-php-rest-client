<?php

namespace Spinen\Ncentral\Support;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    #[Test]
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Collection::class, new Collection);
    }
}
