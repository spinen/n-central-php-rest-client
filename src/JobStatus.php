<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class JobStatus
 *
 * @property int $taskId
 * @property string $taskName
 * @property string $status
 */
class JobStatus extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'taskId' => 'int',
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
    protected string $path = '/job-statuses';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
