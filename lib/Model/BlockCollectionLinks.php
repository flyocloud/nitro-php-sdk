<?php

namespace Flyo\Model;

use \ArrayAccess;
use \Flyo\ObjectSerializer;

/**
 * BlockCollectionLinks Class Doc Comment
 *
 * @category Class
 * @description Absolute urls on the collection endpoint. They carry the token of the collection, which opens this collection and nothing else, so they can be followed from a browser. &#x60;base&#x60; has no page, sort or filter, the start for your own requests. Null when the block is no collection.
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class BlockCollectionLinks implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'BlockCollection_links';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'base' => 'string',
        'self' => 'string',
        'first' => 'string',
        'prev' => 'string',
        'next' => 'string',
        'last' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'base' => 'uri',
        'self' => 'uri',
        'first' => 'uri',
        'prev' => 'uri',
        'next' => 'uri',
        'last' => 'uri'
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'base' => false,
        'self' => false,
        'first' => false,
        'prev' => true,
        'next' => true,
        'last' => false
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'base' => 'base',
        'self' => 'self',
        'first' => 'first',
        'prev' => 'prev',
        'next' => 'next',
        'last' => 'last'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'base' => 'setBase',
        'self' => 'setSelf',
        'first' => 'setFirst',
        'prev' => 'setPrev',
        'next' => 'setNext',
        'last' => 'setLast'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'base' => 'getBase',
        'self' => 'getSelf',
        'first' => 'getFirst',
        'prev' => 'getPrev',
        'next' => 'getNext',
        'last' => 'getLast'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[]|null $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(?array $data = null)
    {
        $this->setIfExists('base', $data ?? [], null);
        $this->setIfExists('self', $data ?? [], null);
        $this->setIfExists('first', $data ?? [], null);
        $this->setIfExists('prev', $data ?? [], null);
        $this->setIfExists('next', $data ?? [], null);
        $this->setIfExists('last', $data ?? [], null);
    }

    /**
    * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
    * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
    * $this->openAPINullablesSetToNull array
    *
    * @param string $variableName
    * @param array  $fields
    * @param mixed  $defaultValue
    */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['base'] === null) {
            $invalidProperties[] = "'base' can't be null";
        }
        if ($this->container['self'] === null) {
            $invalidProperties[] = "'self' can't be null";
        }
        if ($this->container['first'] === null) {
            $invalidProperties[] = "'first' can't be null";
        }
        if ($this->container['prev'] === null && !$this->isNullableSetToNull('prev')) {
            $invalidProperties[] = "'prev' can't be null";
        }
        if ($this->container['next'] === null && !$this->isNullableSetToNull('next')) {
            $invalidProperties[] = "'next' can't be null";
        }
        if ($this->container['last'] === null) {
            $invalidProperties[] = "'last' can't be null";
        }
        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets base
     *
     * @return string
     */
    public function getBase()
    {
        return $this->container['base'];
    }

    /**
     * Sets base
     *
     * @param string $base The collection without page, sort or filter.
     *
     * @return self
     */
    public function setBase($base)
    {
        if (is_null($base)) {
            throw new \InvalidArgumentException('non-nullable base cannot be null');
        }
        $this->container['base'] = $base;

        return $this;
    }

    /**
     * Gets self
     *
     * @return string
     */
    public function getSelf()
    {
        return $this->container['self'];
    }

    /**
     * Sets self
     *
     * @param string $self This page.
     *
     * @return self
     */
    public function setSelf($self)
    {
        if (is_null($self)) {
            throw new \InvalidArgumentException('non-nullable self cannot be null');
        }
        $this->container['self'] = $self;

        return $this;
    }

    /**
     * Gets first
     *
     * @return string
     */
    public function getFirst()
    {
        return $this->container['first'];
    }

    /**
     * Sets first
     *
     * @param string $first The first page.
     *
     * @return self
     */
    public function setFirst($first)
    {
        if (is_null($first)) {
            throw new \InvalidArgumentException('non-nullable first cannot be null');
        }
        $this->container['first'] = $first;

        return $this;
    }

    /**
     * Gets prev
     *
     * @return string|null
     */
    public function getPrev()
    {
        return $this->container['prev'];
    }

    /**
     * Sets prev
     *
     * @param string|null $prev The previous page, null on the first.
     *
     * @return self
     */
    public function setPrev($prev)
    {
        if (is_null($prev)) {
            array_push($this->openAPINullablesSetToNull, 'prev');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('prev', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['prev'] = $prev;

        return $this;
    }

    /**
     * Gets next
     *
     * @return string|null
     */
    public function getNext()
    {
        return $this->container['next'];
    }

    /**
     * Sets next
     *
     * @param string|null $next The next page, null on the last.
     *
     * @return self
     */
    public function setNext($next)
    {
        if (is_null($next)) {
            array_push($this->openAPINullablesSetToNull, 'next');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('next', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['next'] = $next;

        return $this;
    }

    /**
     * Gets last
     *
     * @return string
     */
    public function getLast()
    {
        return $this->container['last'];
    }

    /**
     * Sets last
     *
     * @param string $last The last page.
     *
     * @return self
     */
    public function setLast($last)
    {
        if (is_null($last)) {
            throw new \InvalidArgumentException('non-nullable last cannot be null');
        }
        $this->container['last'] = $last;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer|string $offset Offset
     *
     * @return boolean
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer|string $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer|string $offset Offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


