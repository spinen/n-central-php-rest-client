<?php

namespace Tests\Unit\Concerns\Stubs;

use Mockery;
use Mockery\Mock;
use Spinen\Ncentral\Api\Client as Ncentral;
use Spinen\Ncentral\Concerns\HasClient;

class ItemNeedingClient
{
    use HasClient;

    /**
     * @var Mock
     */
    public $parent_client_mock;

    /**
     * @var Mock
     */
    protected $parentModel;

    public function __construct()
    {
        $this->parent_client_mock = Mockery::mock(Ncentral::class);

        $this->parentModel = Mockery::mock(Ncentral::class);
        $this->parentModel->shouldReceive('getClient')
            ->andReturn($this->parent_client_mock);
    }

    public function unsetParentModel()
    {
        $this->parentModel = null;

        $this->parent_client_mock = null;

        return $this;
    }
}
