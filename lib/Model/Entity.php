<?php
/**
 * Entity
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
 * Entity Class Doc Comment
 *
 * @category Class
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class Entity implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'entity';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'entity' => '\Flyo\Model\EntityInterface',
        'model' => 'object',
        'language' => 'string',
        'jsonld' => 'object',
        'translation' => '\Flyo\Model\Translation[]',
        'breadcrumb' => '\Flyo\Model\Breadcrumb[]'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'entity' => null,
        'model' => null,
        'language' => null,
        'jsonld' => null,
        'translation' => null,
        'breadcrumb' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'entity' => false,
        'model' => false,
        'language' => false,
        'jsonld' => false,
        'translation' => false,
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
        'entity' => 'entity',
        'model' => 'model',
        'language' => 'language',
        'jsonld' => 'jsonld',
        'translation' => 'translation',
        'breadcrumb' => 'breadcrumb'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'entity' => 'setEntity',
        'model' => 'setModel',
        'language' => 'setLanguage',
        'jsonld' => 'setJsonld',
        'translation' => 'setTranslation',
        'breadcrumb' => 'setBreadcrumb'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'entity' => 'getEntity',
        'model' => 'getModel',
        'language' => 'getLanguage',
        'jsonld' => 'getJsonld',
        'translation' => 'getTranslation',
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
        $this->setIfExists('entity', $data ?? [], null);
        $this->setIfExists('model', $data ?? [], null);
        $this->setIfExists('language', $data ?? [], null);
        $this->setIfExists('jsonld', $data ?? [], null);
        $this->setIfExists('translation', $data ?? [], null);
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
     * Gets entity
     *
     * @return \Flyo\Model\EntityInterface|null
     */
    public function getEntity()
    {
        return $this->container['entity'];
    }

    /**
     * Sets entity
     *
     * @param \Flyo\Model\EntityInterface|null $entity entity
     *
     * @return self
     */
    public function setEntity($entity)
    {
        if (is_null($entity)) {
            throw new \InvalidArgumentException('non-nullable entity cannot be null');
        }
        $this->container['entity'] = $entity;

        return $this;
    }

    /**
     * Gets model
     *
     * @return object|null
     */
    public function getModel()
    {
        return $this->container['model'];
    }

    /**
     * Sets model
     *
     * @param object|null $model All values which are mappend from inside flyo trough the mapping system are stored inside this object.
     *
     * @return self
     */
    public function setModel($model)
    {
        if (is_null($model)) {
            throw new \InvalidArgumentException('non-nullable model cannot be null');
        }
        $this->container['model'] = $model;

        return $this;
    }

    /**
     * Gets language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->container['language'];
    }

    /**
     * Sets language
     *
     * @param string|null $language Current language context for entity model data
     *
     * @return self
     */
    public function setLanguage($language)
    {
        if (is_null($language)) {
            throw new \InvalidArgumentException('non-nullable language cannot be null');
        }
        $this->container['language'] = $language;

        return $this;
    }

    /**
     * Gets jsonld
     *
     * @return object|null
     */
    public function getJsonld()
    {
        return $this->container['jsonld'];
    }

    /**
     * Sets jsonld
     *
     * @param object|null $jsonld A Json LD based object with schema.org informations about the entity
     *
     * @return self
     */
    public function setJsonld($jsonld)
    {
        if (is_null($jsonld)) {
            throw new \InvalidArgumentException('non-nullable jsonld cannot be null');
        }
        $this->container['jsonld'] = $jsonld;

        return $this;
    }

    /**
     * Gets translation
     *
     * @return \Flyo\Model\Translation[]|null
     */
    public function getTranslation()
    {
        return $this->container['translation'];
    }

    /**
     * Sets translation
     *
     * @param \Flyo\Model\Translation[]|null $translation The translation contains information about further data in different languages. If the integration is not defined as multi lingual, the translations will be empty.
     *
     * @return self
     */
    public function setTranslation($translation)
    {
        if (is_null($translation)) {
            throw new \InvalidArgumentException('non-nullable translation cannot be null');
        }
        $this->container['translation'] = $translation;

        return $this;
    }

    /**
     * Gets breadcrumb
     *
     * @return \Flyo\Model\Breadcrumb[]|null
     */
    public function getBreadcrumb()
    {
        return $this->container['breadcrumb'];
    }

    /**
     * Sets breadcrumb
     *
     * @param \Flyo\Model\Breadcrumb[]|null $breadcrumb The breadcrumb of the current site is represented by an array of pages, forming a navigational path. It provides a hierarchical representation of the user's current location within the website. The array is ordered from the innermost page, closest to the current page, to the outermost page, with the current page itself residing at the last position.
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


