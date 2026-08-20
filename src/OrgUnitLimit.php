<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class OrgUnitLimit
 *
 * @property string $limitName
 * @property string $value
 * @property string $maxValue
 */
class OrgUnitLimit extends Model
{
    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * Path to API endpoint.
     */
    protected string $path = '/limits';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
