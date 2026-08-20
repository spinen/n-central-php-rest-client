<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class MaintenanceWindow
 *
 * @property int $scheduleID
 * @property string $userName
 * @property string $lastUpdated
 * @property array $applicableAction
 * @property string $name
 * @property string $type
 * @property string $cron
 * @property int $duration
 * @property bool $enabled
 * @property int $maxDowntime
 * @property string $rebootMethod
 * @property int $rebootDelay
 * @property bool $downtimeOnAction
 * @property bool $userMessageEnabled
 * @property string $userMessage
 * @property bool $messageSenderEnabled
 * @property string $messageSender
 * @property bool $preserveStateEnabled
 * @property int $ruleID
 * @property string $ruleName
 */
class MaintenanceWindow extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'scheduleID' => 'int',
        'applicableAction' => 'array',
        'duration' => 'int',
        'enabled' => 'bool',
        'maxDowntime' => 'int',
        'rebootDelay' => 'int',
        'downtimeOnAction' => 'bool',
        'userMessageEnabled' => 'bool',
        'messageSenderEnabled' => 'bool',
        'preserveStateEnabled' => 'bool',
        'ruleID' => 'int',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'scheduleID';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/maintenance-windows';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
