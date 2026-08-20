<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class ServiceMonitorStatus
 *
 * @property int $taskId
 * @property int $serviceId
 * @property int $timeToStale
 * @property string $taskNote
 * @property string $taskIdent
 * @property string $stateStatus
 * @property string $lastUpdate
 * @property int $lastDataId
 * @property string $createdOn
 * @property string $moduleName
 * @property int $serviceItemId
 * @property string $lastScanTime
 * @property bool $isManagedTask
 * @property string $transitionTime
 * @property int $applianceId
 * @property string $applianceName
 */
class ServiceMonitorStatus extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'taskId' => 'int',
        'serviceId' => 'int',
        'timeToStale' => 'int',
        'lastDataId' => 'int',
        'serviceItemId' => 'int',
        'isManagedTask' => 'bool',
        'applianceId' => 'int',
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
    protected string $path = '/service-monitor-status';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
