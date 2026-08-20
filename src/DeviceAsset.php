<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class DeviceAsset
 *
 * Contains asset information about a device including OS, applications,
 * computer system, network adapters, and processor details.
 *
 * @property array $os
 * @property array $application
 * @property array $computersystem
 * @property array $networkadapter
 * @property array $device
 * @property array $processor
 */
class DeviceAsset extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'os' => 'array',
        'application' => 'array',
        'computersystem' => 'array',
        'networkadapter' => 'array',
        'device' => 'array',
        'processor' => 'array',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = false;

    /**
     * Path to API endpoint.
     */
    protected string $path = '/assets';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
