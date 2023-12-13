<?php

namespace Spinen\Ncentral\Support;

use ArrayAccess;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use JsonSerializable;
use LogicException;
use RuntimeException;
use Spinen\Ncentral\Concerns\HasClient;
use Spinen\Ncentral\Exceptions\ApiException;
use Spinen\Ncentral\Exceptions\InvalidRelationshipException;
use Spinen\Ncentral\Exceptions\ModelNotFoundException;
use Spinen\Ncentral\Exceptions\ModelReadonlyException;
use Spinen\Ncentral\Exceptions\NoClientException;
use Spinen\Ncentral\Exceptions\UnableToSaveException;
use Spinen\Ncentral\Support\Relations\BelongsTo;
use Spinen\Ncentral\Support\Relations\ChildOf;
use Spinen\Ncentral\Support\Relations\HasMany;
use Spinen\Ncentral\Support\Relations\Relation;

/**
 * Class Model
 *
 * NOTE: Since we are trying to give a Laravel like feel when interacting
 * with the API, there are sections of this code that is very heavily
 * patterned/inspired directly from Laravel's Model class.
 */
abstract class Model implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    use Conditionable;
    use HasAttributes {
        asDateTime as originalAsDateTime;
    }
    use HasClient;
    use HasTimestamps;
    use HidesAttributes;

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * Default wheres to send.  They are overwrote by any matching where calls.
     */
    public array $defaultWheres = [];

    /**
     * Indicates if the model exists.
     */
    public bool $exists = false;

    /**
     * Extra path to add to end of API endpoint.
     */
    protected string $extra;

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public bool $incrementing = false;

    /**
     * The "type" of the primary key ID.
     */
    protected string $keyType = 'int';

    /**
     * Is resource nested behind parentModel
     *
     * Several of the endpoints are nested behind another model for relationship, but then to
     * interact with the specific model, then are not nested.  This property will know when to
     * keep the specific model nested.
     */
    protected bool $nested = false;

    /**
     * Parameter for order by direction
     *
     * Default is "$orderByParameter . 'desc'"
     */
    protected ?string $orderByDirectionParameter = null;

    /**
     * Parameter for order by column
     */
    protected string $orderByParameter = 'order';

    /**
     * Optional parentModel instance
     */
    public ?Model $parentModel;

    /**
     * Path to API endpoint.
     */
    protected string $path;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'id';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;

    /**
     * The loaded relationships for the model.
     */
    protected array $relations = [];

    /**
     * Some of the responses have the data under a property
     */
    protected ?string $responseKey = 'data';

    /**
     * Are timestamps in milliseconds?
     */
    protected bool $timestampsInMilliseconds = true;

    /**
     * Indicates if the model was inserted during the current request lifecycle.
     *
     * @var bool
     */
    public $wasRecentlyCreated = false;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * Model constructor.
     */
    public function __construct(?array $attributes = [], ?Model $parentModel = null)
    {
        // All dates from API comes as epoch with milliseconds
        $this->dateFormat = 'Uv';
        // None of these models will use timestamps, but need the date casting
        $this->timestamps = false;

        $this->syncOriginal();

        $this->fill($attributes);
        $this->parentModel = $parentModel;
    }

    /**
     * Dynamically retrieve attributes on the model.
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     */
    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @return void
     *
     * @throws ModelReadonlyException
     */
    public function __set($key, $value)
    {
        if ($this->getReadonlyModel()) {
            throw new ModelReadonlyException();
        }

        $this->setAttribute($key, $value);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @return Carbon
     */
    protected function asDateTime($value)
    {
        if (is_numeric($value) && $this->timestampsInMilliseconds) {
            return Date::createFromTimestampMs($value);
        }

        return $this->originalAsDateTime($value);
    }

    /**
     * Assume foreign key
     *
     * @param  string  $related
     */
    protected function assumeForeignKey($related): string
    {
        return Str::snake(new $related()).'Id';
    }

    /**
     * Relationship that makes the model belongs to another model
     *
     * @param  string  $related
     * @param  string|null  $foreignKey
     *
     * @throws InvalidRelationshipException
     * @throws ModelNotFoundException
     * @throws NoClientException
     */
    public function belongsTo($related, $foreignKey = null): BelongsTo
    {
        $foreignKey = $foreignKey ?? $this->assumeForeignKey($related);

        $builder = (new Builder())->setClass($related)
            ->setClient($this->getClient());

        return new BelongsTo($builder, $this, $foreignKey);
    }

    /**
     * Relationship that makes the model child to another model
     *
     * @param  string  $related
     * @param  string|null  $foreignKey
     *
     * @throws InvalidRelationshipException
     * @throws ModelNotFoundException
     * @throws NoClientException
     */
    public function childOf($related, $foreignKey = null): ChildOf
    {
        $foreignKey = $foreignKey ?? $this->assumeForeignKey($related);

        $builder = (new Builder())->setClass($related)
            ->setClient($this->getClient())
            ->setParent($this);

        return new ChildOf($builder, $this, $foreignKey);
    }

    /**
     * Convert boolean to a string as their API expects "true"/"false
     */
    protected function convertBoolToString(mixed $value): mixed
    {
        return match (true) {
            is_array($value) => array_map([$this, 'convertBoolToString'], $value),
            is_bool($value) => $value ? 'true' : 'false',
            default => $value,
        };
    }

    /**
     * Delete the model from Ncentral
     *
     * @throws InvalidCastException
     * @throws LogicException
     * @throws MissingAttributeException
     * @throws NoClientException
     */
    // TODO: Enable this once they add endpoints that support delete
    // public function delete(): bool
    // {
    //     // TODO: Make sure that the model supports being deleted
    //     if ($this->getReadonlyModel()) {
    //         return false;
    //     }

    //     try {
    //         $this->getClient()
    //             ->delete($this->getPath());

    //         return true;
    //     } catch (GuzzleException $e) {
    //         // TODO: Do something with the error

    //         return false;
    //     }
    // }

    /**
     * Fill the model with the supplied properties
     */
    public function fill(?array $attributes = []): self
    {
        foreach ((array) $attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }

        return $this;
    }

    /**
     * Merge any where in the defaultWheres property with any passed in.
     */
    public function getDefaultWheres(array $query = []): array
    {
        return [
            ...$this->defaultWheres,
            ...$query,
        ];
    }

    /**
     * Any thing to add to the end of the path
     */
    public function getExtra(): ?string
    {
        return $this->extra ?? null;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the primary key for the model.
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return $this->keyType;
    }

    /**
     * Get the parameter the endpoint uses to sort.
     */
    public function getOrderByDirectionParameter(): string
    {
        return $this->orderByDirectionParameter ?? $this->getOrderByParameter().'desc';
    }

    /**
     * Get the parameter the endpoint uses to sort.
     */
    public function getOrderByParameter(): string
    {
        return $this->orderByParameter;
    }

    /**
     * Build API path
     *
     * Put anything on the end of the URI that is passed in
     *
     * @param  string|null  $extra
     * @param  array|null  $query
     */
    public function getPath($extra = null, array $query = []): ?string
    {
        // Start with path to resource without "/" on end
        $path = rtrim($this->path, '/');

        // If have an id, then put it on the end
        // NOTE: Ncentral treats creates & updates the same, so only on existing
        if ($this->exist && $this->getKey()) {
            $path .= '/'.$this->getKey();
        }

        // Use the supplied extra or check if the model has an extra property
        $extra ??= $this->getExtra();

        // Stick any extra things on the end
        if (! is_null($extra)) {
            $path .= '/'.ltrim($extra, '/');
        }

        if (! empty($query = $this->getDefaultWheres($query))) {
            $path .= '?'.http_build_query($this->convertBoolToString($query));
        }

        // If there is a parentModel & not have an id (unless for nested), then prepend parentModel
        if (! is_null($this->parentModel) && (! $this->getKey() || $this->isNested())) {
            return $this->parentModel->getPath($path);
        }

        return $path;
    }

    /**
     * Does the model allow updates?
     */
    public function getReadonlyModel(): bool
    {
        return $this->readonlyModel ?? false;
    }

    /**
     * Get a relationship value from a method.
     *
     * @param  string  $method
     *
     * @throws LogicException
     */
    public function getRelationshipFromMethod($method)
    {
        $relation = $this->{$method}();

        if (! $relation instanceof Relation) {
            $exception_message = is_null($relation)
                ? '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?'
                : '%s::%s must return a relationship instance.';

            throw new LogicException(
                sprintf($exception_message, static::class, $method)
            );
        }

        return tap(
            $relation->getResults(),
            function ($results) use ($method) {
                $this->setRelation($method, $results);
            }
        );
    }

    /**
     * Name of the wrapping key of response
     *
     * If none provided, assume camelCase of class name
     */
    public function getResponseKey(): ?string
    {
        return $this->responseKey ?? Str::camel(class_basename(static::class));
    }

    /**
     * Many of the results include collection of related data, so cast it
     *
     * @param  string  $related
     * @param  array  $given
     * @param  bool  $reset Some of the values are nested under a property, so peel it off
     *
     * @throws NoClientException
     */
    public function givenMany($related, $given, $reset = false): Collection
    {
        /** @var Model $model */
        $model = (new $related([], $this->parentModel))->setClient($this->getClient());

        return (new Collection($given))->map(
            function ($attributes) use ($model, $reset) {
                return $model->newFromBuilder($reset ? reset($attributes) : $attributes);
            }
        );
    }

    /**
     * Many of the results include related data, so cast it to object
     *
     * @param  string  $related
     * @param  array  $attributes
     * @param  bool  $reset Some of the values are nested under a property, so peel it off
     *
     * @throws NoClientException
     */
    public function givenOne($related, $attributes, $reset = false): Model
    {
        return (new $related([], $this->parentModel))->setClient($this->getClient())
            ->newFromBuilder($reset ? reset($attributes) : $attributes);
    }

    /**
     * Relationship that makes the model have a collection of another model
     *
     * @param  string  $related
     *
     * @throws InvalidRelationshipException
     * @throws ModelNotFoundException
     * @throws NoClientException
     */
    public function hasMany($related): HasMany
    {
        $builder = (new Builder())->setClass($related)
            ->setClient($this->getClient())
            ->setParent($this);

        return new HasMany($builder, $this);
    }

    /**
     * Is endpoint nested behind another endpoint
     */
    public function isNested(): bool
    {
        return $this->nested ?? false;
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @return static
     */
    public function newFromBuilder($attributes = []): self
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        return $model;
    }

    /**
     * Create a new instance of the given model.
     *
     * Provides a convenient way for us to generate fresh model instances of this current model.
     * It is particularly useful during the hydration of new objects via the builder.
     *
     * @param  bool  $exists
     * @return static
     */
    public function newInstance(array $attributes = [], $exists = false): self
    {
        $model = (new static($attributes, $this->parentModel))->setClient($this->client);

        $model->exists = $exists;

        return $model;
    }

    /**
     * Determine if the given attribute exists.
     */
    public function offsetExists($offset): bool
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     *
     * @throws ModelReadonlyException
     */
    public function offsetSet($offset, $value): void
    {
        if ($this->getReadonlyModel()) {
            throw new ModelReadonlyException();
        }

        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    /**
     * Peel of the wrapping property if it exist.
     *
     * @throws InvalidRelationshipException
     */
    public function peelWrapperPropertyIfNeeded(array $properties): array
    {
        return array_key_exists($this->getResponseKey(), $properties)
            ? $properties[$this->getResponseKey()]
            : $properties;
    }

    /**
     * Laravel allows control of accessing missing attributes, so we just return false
     *
     * @return bool
     */
    public static function preventsAccessingMissingAttributes()
    {
        return false;
    }

    /**
     * Determine if the given relation is loaded.
     *
     * @param  string  $key
     */
    public function relationLoaded($key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Laravel allows the resolver to be set at runtime, so we just return null
     *
     * @param  string  $class
     * @param  string  $key
     * @return null
     */
    public function relationResolver($class, $key)
    {
        return null;
    }

    /**
     * Save the model in Ncentral
     *
     * @throws ApiException
     * @throws InvalidCastException
     * @throws LogicException
     * @throws MissingAttributeException
     * @throws NoClientException
     * @throws RuntimeException
     */
    public function save(): bool
    {
        if ($this->getReadonlyModel()) {
            return false;
        }

        try {
            if (! $this->isDirty()) {
                return true;
            }

            $response = $this->getClient()
                ->post($this->getPath(), $this->toArray());

            $this->exists = true;

            $this->wasRecentlyCreated = true;

            // Reset the model with the results as we get back the full model
            $this->setRawAttributes($response, true);

            return true;
        } catch (RuntimeException $e) {
            // TODO: Should we do something with the error?

            return false;
        }
    }

    /**
     * Save the model in Ncentral, but raise error if fail
     *
     * @throws ApiException
     * @throws InvalidCastException
     * @throws LogicException
     * @throws MissingAttributeException
     * @throws NoClientException
     * @throws RuntimeException
     * @throws UnableToSaveException
     */
    public function saveOrFail(): bool
    {
        if (! $this->save()) {
            throw new UnableToSaveException();
        }

        return true;
    }

    /**
     * Set the readonly
     *
     * @return $this
     */
    public function setExtra($extra = null): self
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Set the readonly
     *
     * @return $this
     */
    public function setReadonly($readonly = true): self
    {
        $this->readonlyModel = $readonly;

        return $this;
    }

    /**
     * Set the given relationship on the model.
     *
     * @param  string  $relation
     * @return $this
     */
    public function setRelation($relation, $value): self
    {
        $this->relations[$relation] = $value;

        return $this;
    }

    /**
     * Convert the model instance to an array.
     */
    public function toArray(): array
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     *
     * @throws JsonEncodingException
     */
    public function toJson($options = 0): string
    {
        $json = json_encode($this->jsonSerialize(), $options);

        // @codeCoverageIgnoreStart
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }
        // @codeCoverageIgnoreEnd

        return $json;
    }
}
