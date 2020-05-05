<?php

namespace Plasticode\Models;

use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\Interfaces\DbModelInterface;
use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\ObjectProxy;
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
     * Container for hydrated properties
     * 
     * E.g., withUser(), user()
     */
    private array $with = [];

    public static function idField() : string
    {
        return static::$idField;
    }

    /**
     * Static alias for new().
     * 
     * @param array|\ORM|null $obj
     */
    public static function create($obj = null) : self
    {
        return new static($obj);
    }

    /**
     * Returns the id of the model.
     * 
     * Use getId() instead of id when $idField is custom.
     * It is recommended to use getId() always for safer code.
     */
    public function getId() : ?int
    {
        $idField = self::idField();
        return $this->{$idField};
    }

    public function hasId() : bool
    {
        return $this->getId() > 0;
    }

    /**
     * Was model saved or not.
     */
    public function isPersisted() : bool
    {
        return $this->hasId();
    }

    public function isNotHydrated() : bool
    {
        return $this->hydratedState == self::NOT_HYDRATED;
    }

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function hydrate($hydrator, bool $forced = false) : self
    {
        if (!$forced && !$this->isNotHydrated()) {
            return $this;
        }

        $this->hydratedState = self::BEING_HYDRATED;

        if ($hydrator) {
            $hydrator->hydrate($this);
        }

        $this->hydratedState = self::HYDRATED;

        return $this;
    }

    /**
     * Add required x() as 'x' method names that must be initialized
     * with withX() before calling.
     *
     * @return string[]
     */
    protected function requiredWiths() : array
    {
        return [];
    }

    public function __call(string $name, array $args)
    {
        $name = Strings::toCamelCase($name);

        if (preg_match('/^with[A-Z]/', $name)) {
            Assert::count($args, 1);

            $propName = lcfirst(Strings::trimStart($name, 'with'));

            return $this->setWithProperty($propName, $args[0]);
        }

        return $this->getWithProperty($name);
    }

    /**
     * Sets value for property 'x()' (use it in 'withX($x)' methods).
     *
     * @param mixed $value
     * @return static
     */
    protected function setWithProperty(string $name, $value) : self
    {
        $this->with[$name] = $value;

        return $this;
    }

    /**
     * Returns property 'x()' set up as 'withX($x)'.
     *
     * @return mixed
     */
    protected function getWithProperty(string $name, bool $required = false)
    {
        if (array_key_exists($name, $this->with)) {
            $withName = $this->with[$name];

            return isCallable($withName)
                ? $withName()
                : $withName;
        }

        $required = $required
            || in_array($name, $this->requiredWiths());

        if ($required) {
            throw new \BadMethodCallException(
                'Method is not initialized: ' . $name . '.'
            );
        }
    }

    public function serialize() : array
    {
        return $this->toArray();
    }

    /**
     * Checks if two objects are equal.
     * 
     * Equal means:
     *  - Same class.
     *  - Same id.
     */
    public function equals(?self $model) : bool
    {
        return !is_null($model)
            && ($model->getId() === $this->getId())
            && (get_class($model) == static::class);
    }

    public function toString() : string
    {
        return '[' . $this->getId() . '] ' . static::class;
    }
}
