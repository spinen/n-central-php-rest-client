<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class ActiveIssue
 *
 * @property int $orgUnitId
 * @property int $deviceId
 * @property int $notificationState
 * @property int $serviceId
 * @property string $serviceName
 * @property string $serviceType
 * @property int $taskId
 * @property int $serviceItemId
 */
class ActiveIssue extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'orgUnitId' => 'int',
        'deviceId' => 'int',
        'notificationState' => 'int',
        'serviceId' => 'int',
        'taskId' => 'int',
        'serviceItemId' => 'int',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'taskId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/active-issues';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
