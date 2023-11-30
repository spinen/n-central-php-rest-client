<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class ScheduledTask
 *
 * @property
 */
class ScheduledTask extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Path to API endpoint.
     */
    protected string $path = '/scheduled-tasks';
}
