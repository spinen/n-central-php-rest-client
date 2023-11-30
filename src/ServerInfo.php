<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class ServerInfo
 *
 * @property ?string $ncentral
 * @property string $jvmVersion
 * @property string $version
 */
class ServerInfo extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = false;

    /**
     * Path to API endpoint.
     */
    protected string $path = '/server-info';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
