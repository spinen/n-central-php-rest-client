<?php

namespace Spinen\Ncentral;

use Carbon\CarbonImmutable;
use Spinen\Ncentral\Support\Model;

/**
 * Class Health
 *
 * @property CarbonImmutable $currentTime
 * @property CarbonImmutable $startTime
 */
class Health extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'currentTime' => 'immutable_datetime',
        'startTime' => 'immutable_datetime',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = false;

    /**
     * Path to API endpoint.
     */
    protected string $path = '/health';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
