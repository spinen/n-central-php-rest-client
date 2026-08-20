<?php

namespace Spinen\Ncentral\Support\Relations;

use Spinen\Ncentral\Exceptions\InvalidRelationshipException;
use Spinen\Ncentral\Support\Builder;
use Spinen\Ncentral\Support\Model;

/**
 * Class ChildOf
 *
 * @deprecated Use BelongsTo with returnChildDirectly() instead
 */
class ChildOf extends BelongsTo
{
    /**
     * @throws InvalidRelationshipException
     */
    public function __construct(Builder $builder, Model $child, $foreignKey)
    {
        parent::__construct($builder, $child, $foreignKey);
        $this->returnChildDirectly();
    }
}
