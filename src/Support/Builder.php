<?php

namespace Spinen\Ncentral\Support;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Spinen\Ncentral\Concerns\HasClient;
use Spinen\Ncentral\Customer;
use Spinen\Ncentral\Device;
use Spinen\Ncentral\DeviceTask;
use Spinen\Ncentral\Exceptions\InvalidRelationshipException;
use Spinen\Ncentral\Exceptions\ModelNotFoundException;
use Spinen\Ncentral\Exceptions\NoClientException;
use Spinen\Ncentral\Exceptions\TokenException;
use Spinen\Ncentral\Health;
use Spinen\Ncentral\ServerInfo;

/**
 * Class Builder
 *
 * @property Collection $devices
 *
 * @method self devices()
 */
class Builder
{
    use Conditionable;
    use HasClient;

    /**
     * Class to cast the response
     */
    protected string $class;

    /**
     * Debug Guzzle calls
     */
    protected bool $debug = false;

    /**
     * Model instance
     */
    protected Model $model;

    /**
     * Parent model instance
     */
    protected ?Model $parentModel = null;

    /**
     * Map of potential parents with class name
     *
     * @var array
     */
    protected $rootModels = [
        'customers' => Customer::class,
        'devices' => Device::class,
        'deviceTasks' => DeviceTask::class,
        'health' => Health::class,
        'serverInfo' => ServerInfo::class,
    ];

    /**
     * Properties to filter the response
     */
    protected array $wheres = [];

    /**
     * Magic method to make builders for root models
     *
     * @throws BadMethodCallException
     * @throws ModelNotFoundException
     * @throws NoClientException
     */
    public function __call(string $name, array $arguments)
    {
        if (! isset($this->parentModel) && array_key_exists($name, $this->rootModels)) {
            return $this->newInstanceForModel($this->rootModels[$name]);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method [%s]', $name));
    }

    /**
     * Magic method to make builders appears as properties
     *
     * @throws GuzzleException
     * @throws InvalidRelationshipException
     * @throws ModelNotFoundException
     * @throws NoClientException
     * @throws TokenException
     */
    public function __get(string $name): Collection|Model|null
    {
        return match (true) {
            ! $this->parentModel && array_key_exists($name, $this->rootModels) => $this->{$name}()
                ->get(),
            default => null,
        };
    }

    /**
     * Create instance of class and save via API
     *
     * @throws InvalidRelationshipException
     */
    public function create(array $attributes): Model
    {
        return tap(
            $this->make($attributes),
            fn (Model $model): bool => $model->save()
        );
    }

    /**
     * Set debug on the client
     *
     * This is reset to false after the request
     */
    public function debug(bool $debug = true): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Get Collection of class instances that match query
     *
     * @throws GuzzleException
     * @throws InvalidRelationshipException
     * @throws NoClientException
     * @throws TokenException
     */
    public function get(array|string $properties = ['*'], string $extra = null): Collection|Model
    {
        $properties = Arr::wrap($properties);

        // Call API to get the response
        $response = $this->getClient()
            ->setDebug($this->debug)
            ->request($this->getPath($extra));

        $count = $response['totalItems'] ?? null;
        $links = $response['_links'] ?? [];
        $page = $response['pageNumber'] ?? null;
        $pages = $response['totalPages'] ?? null;
        $pageSize = $response['pageSize'] ?? null;

        // Peel off the key if exist
        $response = $this->peelWrapperPropertyIfNeeded(Arr::wrap($response));

        // Convert to a collection of filtered objects casted to the class
        return (new Collection((array_values($response) === $response) ? $response : [$response]))
            // Cast to class with only the requested, properties
            ->map(fn ($items) => $this->getModel()
                ->newFromBuilder(
                    $properties === ['*']
                        ? (array) $items
                        : collect($items)
                            ->only($properties)
                            ->toArray()
                )
                ->setClient($this->getClient()->setDebug(false)))
            ->setLinks($links)
            ->setPagination(count: $count, page: $page, pages: $pages, pageSize: $pageSize)
            // If never a collection, only return the first
            ->unless($this->getModel()->collection, fn(Collection $c): Model => $c->first());
    }

    /**
     * Get the model instance being queried.
     *
     * @throws InvalidRelationshipException
     */
    public function getModel(): Model
    {
        if (! isset($this->class)) {
            throw new InvalidRelationshipException();
        }

        if (! isset($this->model)) {
            $this->model = (new $this->class([], $this->parentModel))->setClient($this->client);
        }

        return $this->model;
    }

    /**
     * Get the path for the resource with the where filters
     *
     * @throws InvalidRelationshipException
     */
    public function getPath(string $extra = null): ?string
    {
        $w = (array) $this->wheres;
        $id = Arr::pull($w, $this->getModel()->getKeyName());

        return $this->getModel()
            ->getPath($extra.(is_null($id) ? null : '/'.$id), $w);
    }

    /**
     * Find specific instance of class
     *
     * @throws GuzzleException
     * @throws InvalidRelationshipException
     * @throws NoClientException
     * @throws TokenException
     */
    public function find(int|string $id, array|string $properties = ['*']): Model
    {
        return $this->whereId($id)
            ->get($properties)
            ->first();
    }

    /**
     * Order newest to oldest
     */
    public function latest(string $column = null): self
    {
        $column ??= $this->getModel()->getCreatedAtColumn();

        return $column ? $this->orderByDesc($column) : $this;
    }

    /**
     * Shortcut to where count
     *
     * @throws InvalidRelationshipException
     */
    public function limit(int|string $count): self
    {
        return $this->where('count', (int) $count);
    }

    /**
     * New up a class instance, but not saved
     *
     * @throws InvalidRelationshipException
     */
    public function make(?array $attributes = []): Model
    {
        // TODO: Make sure that the model supports "creating"
        return $this->getModel()
            ->newInstance($attributes);
    }

    /**
     * Create new Builder instance
     *
     * @throws ModelNotFoundException
     * @throws NoClientException
     */
    public function newInstance(): self
    {
        return isset($this->class)
            ? (new static())
                ->setClass($this->class)
                ->setClient($this->getClient())
                ->setParent($this->parentModel)
            : (new static())
                ->setClient($this->getClient())
                ->setParent($this->parentModel);
    }

    /**
     * Create new Builder instance for a specific model
     *
     * @throws ModelNotFoundException
     * @throws NoClientException
     */
    public function newInstanceForModel(string $model): self
    {
        return $this->newInstance()
            ->setClass($model);
    }

    /**
     * Order oldest to newest
     */
    public function oldest(string $column = null): self
    {
        $column ??= $this->getModel()->getCreatedAtColumn();

        return $column ? $this->orderBy($column) : $this;
    }

    /**
     * Shortcut to where order & orderby with expected parameter
     *
     * @throws InvalidRelationshipException
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        return $this->where($this->getModel()->getOrderByParameter(), $column)
            ->where($this->getModel()->getOrderByDirectionParameter(), $direction !== 'asc');
    }

    /**
     * Shortcut to where order with direction set to desc
     *
     * @throws InvalidRelationshipException
     */
    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Shortcut to where pageNumber
     *
     * @throws InvalidRelationshipException
     */
    public function page(int|string $number, int|string $size = null): self
    {
        return $this->where('pageNumber', (int) $number)
            ->when($size, fn (self $b): self => $b->paginate($size));
    }

    /**
     * Shortcut to where paginate
     *
     * @throws InvalidRelationshipException
     */
    public function paginate(int|string $size = null): self
    {
        return $this->unless($size, fn (self $b): self => $b->where('paginate', false))
            ->when($size, fn (self $b): self => $b->where('paginate', true)->where('pageSize', (int) $size));
    }

    /**
     * Peel of the wrapping property if it exist.
     *
     * @throws InvalidRelationshipException
     */
    protected function peelWrapperPropertyIfNeeded(array $properties): array
    {
        return array_key_exists($this->getModel()->getResponseKey(), $properties)
            ? $properties[$this->getModel()->getResponseKey()]
            : $properties;
    }

    /**
     * Set the class to cast the response
     *
     * @throws ModelNotFoundException
     */
    public function setClass(string $class): self
    {
        if (! class_exists($class)) {
            throw new ModelNotFoundException(sprintf('The model [%s] not found.', $class));
        }

        $this->class = $class;

        return $this;
    }

    /**
     * Set the parent model
     */
    public function setParent(?Model $parent): self
    {
        $this->parentModel = $parent;

        return $this;
    }

    /**
     * Shortcut to limit
     *
     * @throws InvalidRelationshipException
     */
    public function take(int|string $count): self
    {
        return $this->limit($count);
    }

    /**
     * Add property to filter the collection
     *
     * @throws InvalidRelationshipException
     */
    public function where(string $property, $value = true): self
    {
        $this->wheres[$property] = is_a($value, LaravelCollection::class)
            ? $value->toArray()
            : $value;

        return $this;
    }

    /**
     * Shortcut to where property id
     *
     * @throws InvalidRelationshipException
     */
    public function whereId(int|string|null $id): self
    {
        return $this->where($this->getModel()->getKeyName(), $id);
    }

    /**
     * Shortcut to where property is false
     *
     * @throws InvalidRelationshipException
     */
    public function whereNot(string $property): self
    {
        return $this->where($property, false);
    }
}
