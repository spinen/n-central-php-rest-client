<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class RemoteControl
 *
 * @property int $deviceId
 * @property bool $remoteControllable
 * @property string $remoteControlType
 * @property string $remoteControlState
 */
class RemoteControl extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deviceId' => 'int',
        'remoteControllable' => 'bool',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = false;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'deviceId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/remote-control';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
