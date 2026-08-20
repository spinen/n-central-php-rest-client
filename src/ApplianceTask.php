<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class ApplianceTask
 *
 * @property int $taskId
 * @property string $scanTime
 * @property string $state
 * @property string $errorMessage
 * @property array $serviceDetails
 */
class ApplianceTask extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'taskId' => 'int',
        'serviceDetails' => 'array',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = false;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'taskId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/appliance-tasks';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
