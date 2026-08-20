<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class DeviceFilter
 *
 * @property int $filterId
 * @property string $filterName
 * @property string $description
 */
class DeviceFilter extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'filterId' => 'int',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'filterId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/device-filters';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
