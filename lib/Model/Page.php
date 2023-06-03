<?php
/**
 * Page
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Flyo Nitro
 *
 * This document provides a comprehensive overview of all the endpoints available for Flyo Nitro, a powerful platform designed to facilitate the development of websites. Flyo Nitro is built upon three strategic pillars that play a central role in website development:  + Config: The config component is responsible for loading all the necessary elements required for seamless navigation within the website layout. This includes crucial elements like the navigation menu or global content, such as the \"Locations\" section of an entity, which can be utilized in the footer across all pages. + Pages: Pages are evaluated based on their unique slug (path) and encompass all the content needed to populate a specific page. This includes various content elements, known as blocks, as well as meta information like \"og-descriptions.\" Additionally, pages can dynamically incorporate content from entities by employing mapping techniques. + Entity: Entities can be retrieved by utilizing a unique identifier, which can be configured within Flyo Nitro. Each entity provides comprehensive details in the form of fields, offering specific content tailored to a particular context.  Furthermore, it is important to distinguish between the **development** and **production** environments in Flyo Nitro. In the development environment, any changes made to data within the Flyo User Interface are immediately accessible, even without saving. This feature enables users to have a live preview of the changes during data entry. On the other hand, the production environment exclusively utilizes saved data, ensuring that only finalized content is displayed.  For more detailed documentation in German, please visit: dev.flyo.cloud
 *
 * The version of the OpenAPI document: 1.0.0-beta.142
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.2.1
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Flyo\Model;

use \ArrayAccess;
use \Flyo\ObjectSerializer;

/**
 * Page Class Doc Comment
 *
 * @category Class
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class Page implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'page';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'id' => 'int',
        'title' => 'string',
        'slug' => 'string',
        'json' => '\Flyo\Model\Block[]',
        'depth' => 'int',
        'is_home' => 'int',
        'created_at' => 'int',
        'updated_at' => 'int',
        'is_visible' => 'int',
        'meta_json' => '\Flyo\Model\Meta',
        'properties' => 'array<string,\Flyo\Model\PageProperty>',
        'uid' => 'string',
        'type' => 'string',
        'target' => 'string',
        'container' => 'string',
        'breadcrumb' => '\Flyo\Model\PageBreadcrumbInner[]'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'id' => null,
        'title' => null,
        'slug' => null,
        'json' => null,
        'depth' => null,
        'is_home' => null,
        'created_at' => null,
        'updated_at' => null,
        'is_visible' => null,
        'meta_json' => null,
        'properties' => null,
        'uid' => null,
        'type' => null,
        'target' => null,
        'container' => null,
        'breadcrumb' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'id' => false,
		'title' => false,
		'slug' => false,
		'json' => false,
		'depth' => false,
		'is_home' => false,
		'created_at' => false,
		'updated_at' => false,
		'is_visible' => false,
		'meta_json' => false,
		'properties' => false,
		'uid' => false,
		'type' => false,
		'target' => false,
		'container' => false,
		'breadcrumb' => false
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
        'id' => 'id',
        'title' => 'title',
        'slug' => 'slug',
        'json' => 'json',
        'depth' => 'depth',
        'is_home' => 'is_home',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'is_visible' => 'is_visible',
        'meta_json' => 'meta_json',
        'properties' => 'properties',
        'uid' => 'uid',
        'type' => 'type',
        'target' => 'target',
        'container' => 'container',
        'breadcrumb' => 'breadcrumb'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'title' => 'setTitle',
        'slug' => 'setSlug',
        'json' => 'setJson',
        'depth' => 'setDepth',
        'is_home' => 'setIsHome',
        'created_at' => 'setCreatedAt',
        'updated_at' => 'setUpdatedAt',
        'is_visible' => 'setIsVisible',
        'meta_json' => 'setMetaJson',
        'properties' => 'setProperties',
        'uid' => 'setUid',
        'type' => 'setType',
        'target' => 'setTarget',
        'container' => 'setContainer',
        'breadcrumb' => 'setBreadcrumb'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'title' => 'getTitle',
        'slug' => 'getSlug',
        'json' => 'getJson',
        'depth' => 'getDepth',
        'is_home' => 'getIsHome',
        'created_at' => 'getCreatedAt',
        'updated_at' => 'getUpdatedAt',
        'is_visible' => 'getIsVisible',
        'meta_json' => 'getMetaJson',
        'properties' => 'getProperties',
        'uid' => 'getUid',
        'type' => 'getType',
        'target' => 'getTarget',
        'container' => 'getContainer',
        'breadcrumb' => 'getBreadcrumb'
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
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->setIfExists('id', $data ?? [], null);
        $this->setIfExists('title', $data ?? [], null);
        $this->setIfExists('slug', $data ?? [], null);
        $this->setIfExists('json', $data ?? [], null);
        $this->setIfExists('depth', $data ?? [], null);
        $this->setIfExists('is_home', $data ?? [], null);
        $this->setIfExists('created_at', $data ?? [], null);
        $this->setIfExists('updated_at', $data ?? [], null);
        $this->setIfExists('is_visible', $data ?? [], null);
        $this->setIfExists('meta_json', $data ?? [], null);
        $this->setIfExists('properties', $data ?? [], null);
        $this->setIfExists('uid', $data ?? [], null);
        $this->setIfExists('type', $data ?? [], null);
        $this->setIfExists('target', $data ?? [], null);
        $this->setIfExists('container', $data ?? [], null);
        $this->setIfExists('breadcrumb', $data ?? [], null);
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
     * Gets id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->container['id'];
    }

    /**
     * Sets id
     *
     * @param int|null $id id
     *
     * @return self
     */
    public function setId($id)
    {

        if (is_null($id)) {
            throw new \InvalidArgumentException('non-nullable id cannot be null');
        }

        $this->container['id'] = $id;

        return $this;
    }

    /**
     * Gets title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->container['title'];
    }

    /**
     * Sets title
     *
     * @param string|null $title title
     *
     * @return self
     */
    public function setTitle($title)
    {

        if (is_null($title)) {
            throw new \InvalidArgumentException('non-nullable title cannot be null');
        }

        $this->container['title'] = $title;

        return $this;
    }

    /**
     * Gets slug
     *
     * @return string|null
     */
    public function getSlug()
    {
        return $this->container['slug'];
    }

    /**
     * Sets slug
     *
     * @param string|null $slug slug
     *
     * @return self
     */
    public function setSlug($slug)
    {

        if (is_null($slug)) {
            throw new \InvalidArgumentException('non-nullable slug cannot be null');
        }

        $this->container['slug'] = $slug;

        return $this;
    }

    /**
     * Gets json
     *
     * @return \Flyo\Model\Block[]|null
     */
    public function getJson()
    {
        return $this->container['json'];
    }

    /**
     * Sets json
     *
     * @param \Flyo\Model\Block[]|null $json json
     *
     * @return self
     */
    public function setJson($json)
    {

        if (is_null($json)) {
            throw new \InvalidArgumentException('non-nullable json cannot be null');
        }

        $this->container['json'] = $json;

        return $this;
    }

    /**
     * Gets depth
     *
     * @return int|null
     */
    public function getDepth()
    {
        return $this->container['depth'];
    }

    /**
     * Sets depth
     *
     * @param int|null $depth depth
     *
     * @return self
     */
    public function setDepth($depth)
    {

        if (is_null($depth)) {
            throw new \InvalidArgumentException('non-nullable depth cannot be null');
        }

        $this->container['depth'] = $depth;

        return $this;
    }

    /**
     * Gets is_home
     *
     * @return int|null
     */
    public function getIsHome()
    {
        return $this->container['is_home'];
    }

    /**
     * Sets is_home
     *
     * @param int|null $is_home is_home
     *
     * @return self
     */
    public function setIsHome($is_home)
    {

        if (is_null($is_home)) {
            throw new \InvalidArgumentException('non-nullable is_home cannot be null');
        }

        $this->container['is_home'] = $is_home;

        return $this;
    }

    /**
     * Gets created_at
     *
     * @return int|null
     */
    public function getCreatedAt()
    {
        return $this->container['created_at'];
    }

    /**
     * Sets created_at
     *
     * @param int|null $created_at created_at
     *
     * @return self
     */
    public function setCreatedAt($created_at)
    {

        if (is_null($created_at)) {
            throw new \InvalidArgumentException('non-nullable created_at cannot be null');
        }

        $this->container['created_at'] = $created_at;

        return $this;
    }

    /**
     * Gets updated_at
     *
     * @return int|null
     */
    public function getUpdatedAt()
    {
        return $this->container['updated_at'];
    }

    /**
     * Sets updated_at
     *
     * @param int|null $updated_at updated_at
     *
     * @return self
     */
    public function setUpdatedAt($updated_at)
    {

        if (is_null($updated_at)) {
            throw new \InvalidArgumentException('non-nullable updated_at cannot be null');
        }

        $this->container['updated_at'] = $updated_at;

        return $this;
    }

    /**
     * Gets is_visible
     *
     * @return int|null
     */
    public function getIsVisible()
    {
        return $this->container['is_visible'];
    }

    /**
     * Sets is_visible
     *
     * @param int|null $is_visible is_visible
     *
     * @return self
     */
    public function setIsVisible($is_visible)
    {

        if (is_null($is_visible)) {
            throw new \InvalidArgumentException('non-nullable is_visible cannot be null');
        }

        $this->container['is_visible'] = $is_visible;

        return $this;
    }

    /**
     * Gets meta_json
     *
     * @return \Flyo\Model\Meta|null
     */
    public function getMetaJson()
    {
        return $this->container['meta_json'];
    }

    /**
     * Sets meta_json
     *
     * @param \Flyo\Model\Meta|null $meta_json meta_json
     *
     * @return self
     */
    public function setMetaJson($meta_json)
    {

        if (is_null($meta_json)) {
            throw new \InvalidArgumentException('non-nullable meta_json cannot be null');
        }

        $this->container['meta_json'] = $meta_json;

        return $this;
    }

    /**
     * Gets properties
     *
     * @return array<string,\Flyo\Model\PageProperty>|null
     */
    public function getProperties()
    {
        return $this->container['properties'];
    }

    /**
     * Sets properties
     *
     * @param array<string,\Flyo\Model\PageProperty>|null $properties properties
     *
     * @return self
     */
    public function setProperties($properties)
    {

        if (is_null($properties)) {
            throw new \InvalidArgumentException('non-nullable properties cannot be null');
        }

        $this->container['properties'] = $properties;

        return $this;
    }

    /**
     * Gets uid
     *
     * @return string|null
     */
    public function getUid()
    {
        return $this->container['uid'];
    }

    /**
     * Sets uid
     *
     * @param string|null $uid A unique identifier for the page
     *
     * @return self
     */
    public function setUid($uid)
    {

        if (is_null($uid)) {
            throw new \InvalidArgumentException('non-nullable uid cannot be null');
        }

        $this->container['uid'] = $uid;

        return $this;
    }

    /**
     * Gets type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->container['type'];
    }

    /**
     * Sets type
     *
     * @param string|null $type Can be either a page with content (which is default behavior), email, file, url, tel
     *
     * @return self
     */
    public function setType($type)
    {

        if (is_null($type)) {
            throw new \InvalidArgumentException('non-nullable type cannot be null');
        }

        $this->container['type'] = $type;

        return $this;
    }

    /**
     * Gets target
     *
     * @return string|null
     */
    public function getTarget()
    {
        return $this->container['target'];
    }

    /**
     * Sets target
     *
     * @param string|null $target can be either _self (which is default) or _blank
     *
     * @return self
     */
    public function setTarget($target)
    {

        if (is_null($target)) {
            throw new \InvalidArgumentException('non-nullable target cannot be null');
        }

        $this->container['target'] = $target;

        return $this;
    }

    /**
     * Gets container
     *
     * @return string|null
     */
    public function getContainer()
    {
        return $this->container['container'];
    }

    /**
     * Sets container
     *
     * @param string|null $container The container this page belongs, by default all pages belong to the default container which is the main nav.
     *
     * @return self
     */
    public function setContainer($container)
    {

        if (is_null($container)) {
            throw new \InvalidArgumentException('non-nullable container cannot be null');
        }

        $this->container['container'] = $container;

        return $this;
    }

    /**
     * Gets breadcrumb
     *
     * @return \Flyo\Model\PageBreadcrumbInner[]|null
     */
    public function getBreadcrumb()
    {
        return $this->container['breadcrumb'];
    }

    /**
     * Sets breadcrumb
     *
     * @param \Flyo\Model\PageBreadcrumbInner[]|null $breadcrumb The breadcrumb of the current site is represented by an array of pages, forming a navigational path. It provides a hierarchical representation of the user's current location within the website. The array is ordered from the innermost page, closest to the current page, to the outermost page, with the current page itself residing at the last position.
     *
     * @return self
     */
    public function setBreadcrumb($breadcrumb)
    {

        if (is_null($breadcrumb)) {
            throw new \InvalidArgumentException('non-nullable breadcrumb cannot be null');
        }

        $this->container['breadcrumb'] = $breadcrumb;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
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
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
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


