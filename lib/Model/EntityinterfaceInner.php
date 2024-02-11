<?php
/**
 * EntityinterfaceInner
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
 * The version of the OpenAPI document: 1.0.0-beta.172
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 7.2.0
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
 * EntityinterfaceInner Class Doc Comment
 *
 * @category Class
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class EntityinterfaceInner implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'entityinterface_inner';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'entity_unique_id' => 'string',
        'entity_title' => 'string',
        'entity_teaser' => 'string',
        'entity_slug' => 'string',
        'entity_time_start' => 'float',
        'entity_type' => 'string',
        'entity_type_id' => 'float',
        'entity_image' => 'string',
        'routes' => 'array<string,string>'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'entity_unique_id' => null,
        'entity_title' => null,
        'entity_teaser' => null,
        'entity_slug' => null,
        'entity_time_start' => null,
        'entity_type' => null,
        'entity_type_id' => null,
        'entity_image' => null,
        'routes' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'entity_unique_id' => false,
        'entity_title' => false,
        'entity_teaser' => false,
        'entity_slug' => false,
        'entity_time_start' => false,
        'entity_type' => false,
        'entity_type_id' => false,
        'entity_image' => false,
        'routes' => false
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
        'entity_unique_id' => 'entity_unique_id',
        'entity_title' => 'entity_title',
        'entity_teaser' => 'entity_teaser',
        'entity_slug' => 'entity_slug',
        'entity_time_start' => 'entity_time_start',
        'entity_type' => 'entity_type',
        'entity_type_id' => 'entity_type_id',
        'entity_image' => 'entity_image',
        'routes' => 'routes'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'entity_unique_id' => 'setEntityUniqueId',
        'entity_title' => 'setEntityTitle',
        'entity_teaser' => 'setEntityTeaser',
        'entity_slug' => 'setEntitySlug',
        'entity_time_start' => 'setEntityTimeStart',
        'entity_type' => 'setEntityType',
        'entity_type_id' => 'setEntityTypeId',
        'entity_image' => 'setEntityImage',
        'routes' => 'setRoutes'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'entity_unique_id' => 'getEntityUniqueId',
        'entity_title' => 'getEntityTitle',
        'entity_teaser' => 'getEntityTeaser',
        'entity_slug' => 'getEntitySlug',
        'entity_time_start' => 'getEntityTimeStart',
        'entity_type' => 'getEntityType',
        'entity_type_id' => 'getEntityTypeId',
        'entity_image' => 'getEntityImage',
        'routes' => 'getRoutes'
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
        $this->setIfExists('entity_unique_id', $data ?? [], null);
        $this->setIfExists('entity_title', $data ?? [], null);
        $this->setIfExists('entity_teaser', $data ?? [], null);
        $this->setIfExists('entity_slug', $data ?? [], null);
        $this->setIfExists('entity_time_start', $data ?? [], null);
        $this->setIfExists('entity_type', $data ?? [], null);
        $this->setIfExists('entity_type_id', $data ?? [], null);
        $this->setIfExists('entity_image', $data ?? [], null);
        $this->setIfExists('routes', $data ?? [], null);
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
     * Gets entity_unique_id
     *
     * @return string|null
     */
    public function getEntityUniqueId()
    {
        return $this->container['entity_unique_id'];
    }

    /**
     * Sets entity_unique_id
     *
     * @param string|null $entity_unique_id Unique ID
     *
     * @return self
     */
    public function setEntityUniqueId($entity_unique_id)
    {
        if (is_null($entity_unique_id)) {
            throw new \InvalidArgumentException('non-nullable entity_unique_id cannot be null');
        }
        $this->container['entity_unique_id'] = $entity_unique_id;

        return $this;
    }

    /**
     * Gets entity_title
     *
     * @return string|null
     */
    public function getEntityTitle()
    {
        return $this->container['entity_title'];
    }

    /**
     * Sets entity_title
     *
     * @param string|null $entity_title The standard interface title resolved for the current entity
     *
     * @return self
     */
    public function setEntityTitle($entity_title)
    {
        if (is_null($entity_title)) {
            throw new \InvalidArgumentException('non-nullable entity_title cannot be null');
        }
        $this->container['entity_title'] = $entity_title;

        return $this;
    }

    /**
     * Gets entity_teaser
     *
     * @return string|null
     */
    public function getEntityTeaser()
    {
        return $this->container['entity_teaser'];
    }

    /**
     * Sets entity_teaser
     *
     * @param string|null $entity_teaser The standard interface teaser resolved for the current entity
     *
     * @return self
     */
    public function setEntityTeaser($entity_teaser)
    {
        if (is_null($entity_teaser)) {
            throw new \InvalidArgumentException('non-nullable entity_teaser cannot be null');
        }
        $this->container['entity_teaser'] = $entity_teaser;

        return $this;
    }

    /**
     * Gets entity_slug
     *
     * @return string|null
     */
    public function getEntitySlug()
    {
        return $this->container['entity_slug'];
    }

    /**
     * Sets entity_slug
     *
     * @param string|null $entity_slug The slug for the given item, this can be either unique or not, depending on the configuration of the entity definition schema.
     *
     * @return self
     */
    public function setEntitySlug($entity_slug)
    {
        if (is_null($entity_slug)) {
            throw new \InvalidArgumentException('non-nullable entity_slug cannot be null');
        }
        $this->container['entity_slug'] = $entity_slug;

        return $this;
    }

    /**
     * Gets entity_time_start
     *
     * @return float|null
     */
    public function getEntityTimeStart()
    {
        return $this->container['entity_time_start'];
    }

    /**
     * Sets entity_time_start
     *
     * @param float|null $entity_time_start The shared entity interface time start attribute. If not defined, null or 0 is returned
     *
     * @return self
     */
    public function setEntityTimeStart($entity_time_start)
    {
        if (is_null($entity_time_start)) {
            throw new \InvalidArgumentException('non-nullable entity_time_start cannot be null');
        }
        $this->container['entity_time_start'] = $entity_time_start;

        return $this;
    }

    /**
     * Gets entity_type
     *
     * @return string|null
     */
    public function getEntityType()
    {
        return $this->container['entity_type'];
    }

    /**
     * Sets entity_type
     *
     * @param string|null $entity_type 
     *
     * @return self
     */
    public function setEntityType($entity_type)
    {
        if (is_null($entity_type)) {
            throw new \InvalidArgumentException('non-nullable entity_type cannot be null');
        }
        $this->container['entity_type'] = $entity_type;

        return $this;
    }

    /**
     * Gets entity_type_id
     *
     * @return float|null
     */
    public function getEntityTypeId()
    {
        return $this->container['entity_type_id'];
    }

    /**
     * Sets entity_type_id
     *
     * @param float|null $entity_type_id The Type-ID, alternatively referred to as the Entity-Definition-Schema ID, serves as a crucial identifier within the system. It uniquely distinguishes and categorizes the Entity-Definition-Schema.
     *
     * @return self
     */
    public function setEntityTypeId($entity_type_id)
    {
        if (is_null($entity_type_id)) {
            throw new \InvalidArgumentException('non-nullable entity_type_id cannot be null');
        }
        $this->container['entity_type_id'] = $entity_type_id;

        return $this;
    }

    /**
     * Gets entity_image
     *
     * @return string|null
     */
    public function getEntityImage()
    {
        return $this->container['entity_image'];
    }

    /**
     * Sets entity_image
     *
     * @param string|null $entity_image For image manipulation please see https://dev.flyo.cloud/dev/infos/images.html
     *
     * @return self
     */
    public function setEntityImage($entity_image)
    {
        if (is_null($entity_image)) {
            throw new \InvalidArgumentException('non-nullable entity_image cannot be null');
        }
        $this->container['entity_image'] = $entity_image;

        return $this;
    }

    /**
     * Gets routes
     *
     * @return array<string,string>|null
     */
    public function getRoutes()
    {
        return $this->container['routes'];
    }

    /**
     * Sets routes
     *
     * @param array<string,string>|null $routes routes
     *
     * @return self
     */
    public function setRoutes($routes)
    {
        if (is_null($routes)) {
            throw new \InvalidArgumentException('non-nullable routes cannot be null');
        }
        $this->container['routes'] = $routes;

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


