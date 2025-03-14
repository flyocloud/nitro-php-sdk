<?php
/**
 * ConfigResponse
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
 * The version of the OpenAPI document: 1.8
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 7.7.0
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
 * ConfigResponse Class Doc Comment
 *
 * @category Class
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class ConfigResponse implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Config_Response';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'nitro' => '\Flyo\Model\ConfigResponseNitro',
        'pages' => 'string[]',
        'containers' => 'array<string,\Flyo\Model\ConfigResponseContainersValue>',
        'globals' => 'object'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'nitro' => null,
        'pages' => null,
        'containers' => null,
        'globals' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'nitro' => false,
        'pages' => false,
        'containers' => false,
        'globals' => false
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
        'nitro' => 'nitro',
        'pages' => 'pages',
        'containers' => 'containers',
        'globals' => 'globals'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'nitro' => 'setNitro',
        'pages' => 'setPages',
        'containers' => 'setContainers',
        'globals' => 'setGlobals'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'nitro' => 'getNitro',
        'pages' => 'getPages',
        'containers' => 'getContainers',
        'globals' => 'getGlobals'
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
        $this->setIfExists('nitro', $data ?? [], null);
        $this->setIfExists('pages', $data ?? [], null);
        $this->setIfExists('containers', $data ?? [], null);
        $this->setIfExists('globals', $data ?? [], null);
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
     * Gets nitro
     *
     * @return \Flyo\Model\ConfigResponseNitro|null
     */
    public function getNitro()
    {
        return $this->container['nitro'];
    }

    /**
     * Sets nitro
     *
     * @param \Flyo\Model\ConfigResponseNitro|null $nitro nitro
     *
     * @return self
     */
    public function setNitro($nitro)
    {
        if (is_null($nitro)) {
            throw new \InvalidArgumentException('non-nullable nitro cannot be null');
        }
        $this->container['nitro'] = $nitro;

        return $this;
    }

    /**
     * Gets pages
     *
     * @return string[]|null
     */
    public function getPages()
    {
        return $this->container['pages'];
    }

    /**
     * Sets pages
     *
     * @param string[]|null $pages A unique array of slugs is available for the entire site configuration, providing you with the flexibility to register specific routes for your application or compare a slug against this array. This comparison enables you to determine whether to return a \"404 Not Found\" exception or handle the request differently. By utilizing this array, you can easily manage and control the slugs used in your application, ensuring that only valid and registered routes are accessible. This approach helps maintain the integrity and security of your site's navigation, preventing users from accessing undefined or unauthorized pages.
     *
     * @return self
     */
    public function setPages($pages)
    {
        if (is_null($pages)) {
            throw new \InvalidArgumentException('non-nullable pages cannot be null');
        }
        $this->container['pages'] = $pages;

        return $this;
    }

    /**
     * Gets containers
     *
     * @return array<string,\Flyo\Model\ConfigResponseContainersValue>|null
     */
    public function getContainers()
    {
        return $this->container['containers'];
    }

    /**
     * Sets containers
     *
     * @param array<string,\Flyo\Model\ConfigResponseContainersValue>|null $containers This endpoint manages containers, which are essential for constructing navigation menus. Each navigation menu is housed within a distinct container. These containers are identified by two key attributes: a label and an identifier. The label describes the menu's purpose or position (e.g., \"Footer Nav\" or \"Main Nav\"), while the identifier is a slug-formatted text for easy reference (e.g., \"footer\"). Within each container, there is an \"items\" object. This object contains the actual page items that make up the menu's structure.
     *
     * @return self
     */
    public function setContainers($containers)
    {
        if (is_null($containers)) {
            throw new \InvalidArgumentException('non-nullable containers cannot be null');
        }
        $this->container['containers'] = $containers;

        return $this;
    }

    /**
     * Gets globals
     *
     * @return object|null
     */
    public function getGlobals()
    {
        return $this->container['globals'];
    }

    /**
     * Sets globals
     *
     * @param object|null $globals The globals section serves as a crucial component in the overall structure of the code. It consists of an associative array that allows users to define their own unique keys, each of which contains an array of items representing data sourced from a Content Pool. This data is essential as it needs to be accessible throughout the entire scope of the website, ensuring its availability whenever required. By leveraging this globals section, developers can efficiently manage and access these globally significant data sets.
     *
     * @return self
     */
    public function setGlobals($globals)
    {
        if (is_null($globals)) {
            throw new \InvalidArgumentException('non-nullable globals cannot be null');
        }
        $this->container['globals'] = $globals;

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


