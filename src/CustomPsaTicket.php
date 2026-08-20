<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class CustomPsaTicket
 *
 * @property int $ticketId
 * @property int $deviceId
 * @property string $ticketNumber
 * @property string $status
 * @property string $summary
 * @property string $createdAt
 * @property string $updatedAt
 */
class CustomPsaTicket extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ticketId' => 'int',
        'deviceId' => 'int',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'ticketId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/custom-psa/tickets';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
