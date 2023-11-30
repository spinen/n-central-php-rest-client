<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class DeviceTask
 *
 * @property ?array $credential
 * @property ?array $parameters
 * @property int $customerId
 * @property int $deviceId
 * @property int $itemId
 * @property int $taskId
 * @property string $name
 * @property string $taskType
 */
class DeviceTask extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'taskId' => 'int',
        'itemId' => 'int',
        'customerId' => 'int',
        'deviceId' => 'int',
        'credential' => 'array',
        'parameters' => 'array',
    ];

    /**
     * Is resource nested behind parentModel
     */
    protected bool $nested = true;

    /**
     * Optional parentModel instance
     */
    public Device $parentModel;

    /**
     * Path to API endpoint.
     */
    protected string $path = '/scheduled-tasks';

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'taskId';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
