<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class Customer
 *
 * @property bool $isServiceOrg
 * @property bool $isSystem
 * @property int $customerId
 * @property int $parentId
 * @property string $city
 * @property string $contactEmail
 * @property string $county
 * @property string $customerName
 * @property string $postalCode
 * @property string $stateProv
 */
class Customer extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'customerId' => 'int',
        'isSystem' => 'bool',
        'isServiceOrg' => 'bool',
        'parentId' => 'int',
    ];

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'customerId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/customers';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
