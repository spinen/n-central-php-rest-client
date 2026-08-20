<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class User
 *
 * @property int $userId
 * @property int $orgUnitId
 * @property string $email
 * @property string $firstName
 * @property string $lastName
 * @property string $userName
 * @property bool $isEnabled
 * @property bool $isLocked
 */
class User extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'userId' => 'int',
        'orgUnitId' => 'int',
        'isEnabled' => 'bool',
        'isLocked' => 'bool',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'userId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/users';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
