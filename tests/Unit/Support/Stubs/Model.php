<?php

namespace Tests\Unit\Support\Stubs;

use Spinen\Ncentral\Support\Model as BaseModel;

class Model extends BaseModel
{
    /**
     * Path to API endpoint.
     */
    protected string $path = '/test';
}
