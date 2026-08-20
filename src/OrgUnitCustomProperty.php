<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class OrgUnitCustomProperty
 *
 * @property int $propertyId
 * @property string $propertyName
 * @property string $propertyType
 * @property string $value
 * @property array $enumeratedValueList
 */
class OrgUnitCustomProperty extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'propertyId' => 'int',
        'enumeratedValueList' => 'array',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'propertyId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/custom-properties';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
