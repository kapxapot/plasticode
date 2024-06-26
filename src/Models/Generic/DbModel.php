<?php

namespace Plasticode\Models\Generic;

use BadMethodCallException;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\Interfaces\DbModelInterface;
use Plasticode\Models\Interfaces\EquatableInterface;
use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\ObjectProxy;
use Plasticode\Util\Classes;
use Plasticode\Util\Pluralizer;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

abstract class DbModel extends Model implements DbModelInterface, SerializableInterface
{
    private const NOT_HYDRATED = 1;
    private const BEING_HYDRATED = 2;
    private const HYDRATED = 3;

    protected static string $idField = 'id';

    protected int $hydratedState = self::NOT_HYDRATED;

    /**
     * If a db model is frozen, all with properties are frozed by default.
     */
    protected bool $frozen = false;

    /**
     * Container for hydrated properties.
     * E.g., withUser() <=> user().
     *
     * @var array<string, mixed>
     */
    private array $with = [];

    public static function idField(): string
    {
        return static::$idField;
    }

    /**
     * Static alias for new().
     *
     * @return static
     */
    public static function create(?array $data = null): self
    {
        return new static($data);
    }

    /**
     * Updates the data.
     *
     * @return $this
     */
    public function update(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the id of the model.
     *
     * - Use `getId()` instead of just `id` when `$idField` is custom.
     * - It's recommended to use `getId()` always for safety.
     */
    public function getId(): ?int
    {
        $idField = self::idField();
        return $this->{$idField};
    }

    /**
     * Returns `true` if the entity is saved to DB.
     *
     * Alias for `hasId()`.
     */
    public function isPersisted(): bool
    {
        return $this->hasId();
    }

    public function hasId(): bool
    {
        return $this->getId() > 0;
    }

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     * @return $this
     */
    public function hydrate($hydrator, bool $forced = false): self
    {
        if (!$forced && !$this->isNotHydrated()) {
            return $this;
        }

        if ($hydrator) {
            $this->hydratedState = self::BEING_HYDRATED;
            $hydrator->hydrate($this);
        }

        $this->hydratedState = self::HYDRATED;

        return $this;
    }

    public function isNotHydrated(): bool
    {
        return $this->hydratedState === self::NOT_HYDRATED;
    }

    /**
     * Add required `x()` as 'x' method names that must be initialized
     * with `withX()` before calling.
     *
     * @return string[]
     */
    protected function requiredWiths(): array
    {
        return [];
    }

    public function __call(string $name, array $args)
    {
        $name = Strings::toCamelCase($name);

        if (preg_match('/^with[A-Z]/', $name)) {
            Assert::count($args, 1);

            $propName = lcfirst(
                Strings::trimStart($name, 'with')
            );

            return $this->setWithProperty($propName, $args[0]);
        }

        return $this->getWithProperty($name);
    }

    /**
     * Sets a value for the property `x()` (use it in `withX($x)` methods).
     *
     * @param mixed $value
     * @return $this
     */
    protected function setWithProperty(string $name, $value): self
    {
        $this->with[$name] = $value;
        return $this;
    }

    /**
     * Returns the property `x()` previously initialized by `withX($x)`.
     *
     * @return mixed
     */
    protected function getWithProperty(string $name, bool $required = false)
    {
        if (array_key_exists($name, $this->with)) {
            $withValue = $this->with[$name];

            if (isCallable($withValue)) {
                $resolvedValue = $withValue();

                if ($this->frozen) {
                    $this->setWithProperty($name, $resolvedValue);
                }

                return $resolvedValue;
            }

            return $withValue;
        }

        $required = $required || in_array($name, $this->requiredWiths());

        if ($required) {
            throw new BadMethodCallException("Method is not initialized: {$name}.");
        }

        return null;
    }

    /**
     * Returns plural entity type (in snake case) based on the class name.
     *
     * ArticleCategory -> article_categories.
     */
    public static function pluralAlias(): string
    {
        // \App\Models\ArticleCategory
        // -> ArticleCategory
        $class = Classes::shortName(static::class);

        // ArticleCategory -> ArticleCategories
        $entityPlural = Pluralizer::plural($class);

        // ArticleCategories -> article_categories
        $alias = Strings::toSnakeCase($entityPlural);

        return $alias;
    }

    /**
     * Checks if two objects are equal:
     *
     * - Same class.
     * - Same id.
     */
    public function equals(?EquatableInterface $obj): bool
    {
        return ($obj instanceof self)
            && $this->getClass() === $obj->getClass()
            && $this->getId() === $obj->getId();
    }

    public function serialize(): array
    {
        return $this->toArray();
    }

    public function toString(): string
    {
        return sprintf(
            '[%s] %s',
            $this->getId(),
            parent::toString()
        );
    }
}
