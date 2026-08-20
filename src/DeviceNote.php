<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class DeviceNote
 *
 * @property int $deviceNoteId
 * @property int $deviceId
 * @property int $userId
 * @property string $note
 * @property string $email
 * @property string $lastUpdated
 */
class DeviceNote extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deviceNoteId' => 'int',
        'deviceId' => 'int',
        'userId' => 'int',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'deviceNoteId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/notes';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
