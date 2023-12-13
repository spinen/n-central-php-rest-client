<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class DetailedScheduledTask
 *
 * @property int $deviceId
 * @property int $taskId
 * @property string $deviceName
 * @property string $message
 * @property string $output
 * @property string $outputFileName
 * @property string $status
 * @property string $taskName
 */
class DetailedScheduledTask extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deviceId' => 'int',
        'taskId' => 'int',
    ];

    /**
     * Path to API endpoint.
     */
    protected string $extra = '/status/details';

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
