<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Exceptions\NoClientException;
use Spinen\Ncentral\Support\Builder;
use Spinen\Ncentral\Support\Model;

/**
 * Class ScheduledTask
 *
 * @property ?array $deviceIds
 * @property ?bool $isEnabled
 * @property ?bool $isReactive
 * @property ?int $applianceId
 * @property ?int $customerId
 * @property ?int $deviceId
 * @property ?int $itemId
 * @property ?int $parentId
 * @property ?string $name
 * @property ?string $type
 * @property int $taskId
 */
class ScheduledTask extends Model
{
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        // TODO: Should we set these defaults?
        'credential' => [
            'type' => 'LocalSystem',
            'username' => null,
            'password' => null,
        ],
        'taskType' => 'AutomationPolicy',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'applianceId' => 'int',
        'customerId' => 'int',
        'deviceId' => 'int',
        'isEnabled' => 'bool',
        'isReactive' => 'bool',
        'itemId' => 'int',
        'parentId' => 'int',
        'taskId' => 'int',
    ];

    /**
     * Path to API endpoint.
     */
    protected string $path = '/scheduled-tasks';

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'taskId';

    /**
     * Accessor to get the details
     *
     * @throws NoClientException
     */
    public function getDetailsAttribute(): DetailedScheduledTask
    {
        return (new Builder())->setClient($this->getClient())
            ->detailedScheduledTasks()
            ->find($this->taskId);
    }

    /**
     * Any thing to add to the end of the path
     */
    public function getExtra(): ?string
    {
        // N-able has create route different than get route
        return $this->taskId ? null : '/direct';
    }

    /**
     * Does the model allow updates?
     */
    public function getReadonlyModel(): bool
    {
        // Toggle readonly for existing as you cannot update
        return $this->exists;
    }
}
