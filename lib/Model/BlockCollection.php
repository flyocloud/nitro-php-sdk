<?php

namespace Flyo\Model;

use \ArrayAccess;
use \Flyo\ObjectSerializer;

/**
 * BlockCollection Class Doc Comment
 *
 * @category Class
 * @description How the items of the block are delivered. A collection pages, sorts and filters them on the collection endpoint; the page JSON carries only the first page.
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class BlockCollection implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'BlockCollection';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'enabled' => 'bool',
        'key' => 'string',
        'per_page' => 'int',
        'inline_first_page' => 'bool',
        'total' => 'int',
        'page_count' => 'int',
        'current_page' => 'int',
        'sort' => 'string',
        'sortable' => 'string[]',
        'filterable' => 'string[]',
        'links' => '\Flyo\Model\BlockCollectionLinks',
        'facets' => 'array<string,\Flyo\Model\BlockCollectionFacetsValue[]>'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'enabled' => null,
        'key' => null,
        'per_page' => null,
        'inline_first_page' => null,
        'total' => null,
        'page_count' => null,
        'current_page' => null,
        'sort' => null,
        'sortable' => null,
        'filterable' => null,
        'links' => null,
        'facets' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'enabled' => false,
        'key' => false,
        'per_page' => false,
        'inline_first_page' => false,
        'total' => false,
        'page_count' => false,
        'current_page' => false,
        'sort' => true,
        'sortable' => false,
        'filterable' => false,
        'links' => true,
        'facets' => false
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
        'enabled' => 'enabled',
        'key' => 'key',
        'per_page' => 'per_page',
        'inline_first_page' => 'inline_first_page',
        'total' => 'total',
        'page_count' => 'page_count',
        'current_page' => 'current_page',
        'sort' => 'sort',
        'sortable' => 'sortable',
        'filterable' => 'filterable',
        'links' => 'links',
        'facets' => 'facets'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'enabled' => 'setEnabled',
        'key' => 'setKey',
        'per_page' => 'setPerPage',
        'inline_first_page' => 'setInlineFirstPage',
        'total' => 'setTotal',
        'page_count' => 'setPageCount',
        'current_page' => 'setCurrentPage',
        'sort' => 'setSort',
        'sortable' => 'setSortable',
        'filterable' => 'setFilterable',
        'links' => 'setLinks',
        'facets' => 'setFacets'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'enabled' => 'getEnabled',
        'key' => 'getKey',
        'per_page' => 'getPerPage',
        'inline_first_page' => 'getInlineFirstPage',
        'total' => 'getTotal',
        'page_count' => 'getPageCount',
        'current_page' => 'getCurrentPage',
        'sort' => 'getSort',
        'sortable' => 'getSortable',
        'filterable' => 'getFilterable',
        'links' => 'getLinks',
        'facets' => 'getFacets'
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
        $this->setIfExists('enabled', $data ?? [], null);
        $this->setIfExists('key', $data ?? [], null);
        $this->setIfExists('per_page', $data ?? [], null);
        $this->setIfExists('inline_first_page', $data ?? [], null);
        $this->setIfExists('total', $data ?? [], null);
        $this->setIfExists('page_count', $data ?? [], null);
        $this->setIfExists('current_page', $data ?? [], null);
        $this->setIfExists('sort', $data ?? [], null);
        $this->setIfExists('sortable', $data ?? [], null);
        $this->setIfExists('filterable', $data ?? [], null);
        $this->setIfExists('links', $data ?? [], null);
        $this->setIfExists('facets', $data ?? [], null);
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

        if ($this->container['enabled'] === null) {
            $invalidProperties[] = "'enabled' can't be null";
        }
        if ($this->container['key'] === null) {
            $invalidProperties[] = "'key' can't be null";
        }
        if ($this->container['per_page'] === null) {
            $invalidProperties[] = "'per_page' can't be null";
        }
        if ($this->container['inline_first_page'] === null) {
            $invalidProperties[] = "'inline_first_page' can't be null";
        }
        if ($this->container['total'] === null) {
            $invalidProperties[] = "'total' can't be null";
        }
        if ($this->container['page_count'] === null) {
            $invalidProperties[] = "'page_count' can't be null";
        }
        if ($this->container['current_page'] === null) {
            $invalidProperties[] = "'current_page' can't be null";
        }
        if ($this->container['sort'] === null && !$this->isNullableSetToNull('sort')) {
            $invalidProperties[] = "'sort' can't be null";
        }
        if ($this->container['sortable'] === null) {
            $invalidProperties[] = "'sortable' can't be null";
        }
        if ($this->container['filterable'] === null) {
            $invalidProperties[] = "'filterable' can't be null";
        }
        if ($this->container['links'] === null && !$this->isNullableSetToNull('links')) {
            $invalidProperties[] = "'links' can't be null";
        }
        if ($this->container['facets'] === null) {
            $invalidProperties[] = "'facets' can't be null";
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
     * Gets enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->container['enabled'];
    }

    /**
     * Sets enabled
     *
     * @param bool $enabled Whether the items of this block are a collection the frontend can page, sort and filter through `links`. When false, `items` holds every item and the counters describe them.
     *
     * @return self
     */
    public function setEnabled($enabled)
    {
        if (is_null($enabled)) {
            throw new \InvalidArgumentException('non-nullable enabled cannot be null');
        }
        $this->container['enabled'] = $enabled;

        return $this;
    }

    /**
     * Gets key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->container['key'];
    }

    /**
     * Sets key
     *
     * @param string $key The key of the collection, the last segment of its url (`/blocks/collection/{key}`): the uid of the placement, unless the editor named it.
     *
     * @return self
     */
    public function setKey($key)
    {
        if (is_null($key)) {
            throw new \InvalidArgumentException('non-nullable key cannot be null');
        }
        $this->container['key'] = $key;

        return $this;
    }

    /**
     * Gets per_page
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->container['per_page'];
    }

    /**
     * Sets per_page
     *
     * @param int $per_page The items per page, set in the block definition. A request can not ask for another page size.
     *
     * @return self
     */
    public function setPerPage($per_page)
    {
        if (is_null($per_page)) {
            throw new \InvalidArgumentException('non-nullable per_page cannot be null');
        }
        $this->container['per_page'] = $per_page;

        return $this;
    }

    /**
     * Gets inline_first_page
     *
     * @return bool
     */
    public function getInlineFirstPage()
    {
        return $this->container['inline_first_page'];
    }

    /**
     * Sets inline_first_page
     *
     * @param bool $inline_first_page Whether the page JSON carries the first page in `items`. When false, `items` is empty in the page JSON and the first page is loaded through `links.first`.
     *
     * @return self
     */
    public function setInlineFirstPage($inline_first_page)
    {
        if (is_null($inline_first_page)) {
            throw new \InvalidArgumentException('non-nullable inline_first_page cannot be null');
        }
        $this->container['inline_first_page'] = $inline_first_page;

        return $this;
    }

    /**
     * Gets total
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->container['total'];
    }

    /**
     * Sets total
     *
     * @param int $total The number of items, filtered when the request has a filter. Never more than the limit the editor set.
     *
     * @return self
     */
    public function setTotal($total)
    {
        if (is_null($total)) {
            throw new \InvalidArgumentException('non-nullable total cannot be null');
        }
        $this->container['total'] = $total;

        return $this;
    }

    /**
     * Gets page_count
     *
     * @return int
     */
    public function getPageCount()
    {
        return $this->container['page_count'];
    }

    /**
     * Sets page_count
     *
     * @param int $page_count The number of pages, at least 1.
     *
     * @return self
     */
    public function setPageCount($page_count)
    {
        if (is_null($page_count)) {
            throw new \InvalidArgumentException('non-nullable page_count cannot be null');
        }
        $this->container['page_count'] = $page_count;

        return $this;
    }

    /**
     * Gets current_page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->container['current_page'];
    }

    /**
     * Sets current_page
     *
     * @param int $current_page The page `items` holds, starting at 1.
     *
     * @return self
     */
    public function setCurrentPage($current_page)
    {
        if (is_null($current_page)) {
            throw new \InvalidArgumentException('non-nullable current_page cannot be null');
        }
        $this->container['current_page'] = $current_page;

        return $this;
    }

    /**
     * Gets sort
     *
     * @return string|null
     */
    public function getSort()
    {
        return $this->container['sort'];
    }

    /**
     * Sets sort
     *
     * @param string|null $sort The sort of this page: one or more of `sortable`, comma separated, `-` for descending. `position` is the order the editor set, and entries the requested fields do not tell apart keep that order. Null when the block is no collection.
     *
     * @return self
     */
    public function setSort($sort)
    {
        if (is_null($sort)) {
            array_push($this->openAPINullablesSetToNull, 'sort');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('sort', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['sort'] = $sort;

        return $this;
    }

    /**
     * Gets sortable
     *
     * @return string[]
     */
    public function getSortable()
    {
        return $this->container['sortable'];
    }

    /**
     * Sets sortable
     *
     * @param string[] $sortable The fields `sort` accepts. `position` stands for the order the editor set.
     *
     * @return self
     */
    public function setSortable($sortable)
    {
        if (is_null($sortable)) {
            throw new \InvalidArgumentException('non-nullable sortable cannot be null');
        }
        $this->container['sortable'] = $sortable;

        return $this;
    }

    /**
     * Gets filterable
     *
     * @return string[]
     */
    public function getFilterable()
    {
        return $this->container['filterable'];
    }

    /**
     * Sets filterable
     *
     * @param string[] $filterable The fields `filter` accepts. A field of an object is addressed with `:` (`category:value`), a field of the entries of a list with `.` (`authors.name`).
     *
     * @return self
     */
    public function setFilterable($filterable)
    {
        if (is_null($filterable)) {
            throw new \InvalidArgumentException('non-nullable filterable cannot be null');
        }
        $this->container['filterable'] = $filterable;

        return $this;
    }

    /**
     * Gets links
     *
     * @return \Flyo\Model\BlockCollectionLinks|null
     */
    public function getLinks()
    {
        return $this->container['links'];
    }

    /**
     * Sets links
     *
     * @param \Flyo\Model\BlockCollectionLinks|null $links links
     *
     * @return self
     */
    public function setLinks($links)
    {
        if (is_null($links)) {
            array_push($this->openAPINullablesSetToNull, 'links');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('links', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['links'] = $links;

        return $this;
    }

    /**
     * Gets facets
     *
     * @return array<string,\Flyo\Model\BlockCollectionFacetsValue[]>
     */
    public function getFacets()
    {
        return $this->container['facets'];
    }

    /**
     * Sets facets
     *
     * @param array<string,\Flyo\Model\BlockCollectionFacetsValue[]> $facets The distinct values of every mapping item the block aggregates, each with the number of items that carry it, to build a filter from. They describe all items of the block, whatever the request filters by. Text and dropdown labels are sorted alphabetically, numbers and dates by value; empty values are left out, and a field has at most 100 values. An empty object when the block aggregates no mapping item.
     *
     * @return self
     */
    public function setFacets($facets)
    {
        if (is_null($facets)) {
            throw new \InvalidArgumentException('non-nullable facets cannot be null');
        }
        $this->container['facets'] = $facets;

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


