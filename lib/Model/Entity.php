<?php

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
        'is_draft' => 'bool',
        'draft_expires_at' => 'float',
        'jsonld' => 'object',
        'translation' => '\Flyo\Model\Translation[]',
        'breadcrumb' => '\Flyo\Model\Breadcrumb[]',
        'canonical' => 'string',
        'is_indexable' => 'bool'
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
        'is_draft' => null,
        'draft_expires_at' => null,
        'jsonld' => null,
        'translation' => null,
        'breadcrumb' => null,
        'canonical' => null,
        'is_indexable' => null
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
        'is_draft' => false,
        'draft_expires_at' => true,
        'jsonld' => false,
        'translation' => false,
        'breadcrumb' => false,
        'canonical' => false,
        'is_indexable' => false
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
        'is_draft' => 'is_draft',
        'draft_expires_at' => 'draft_expires_at',
        'jsonld' => 'jsonld',
        'translation' => 'translation',
        'breadcrumb' => 'breadcrumb',
        'canonical' => 'canonical',
        'is_indexable' => 'is_indexable'
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
        'is_draft' => 'setIsDraft',
        'draft_expires_at' => 'setDraftExpiresAt',
        'jsonld' => 'setJsonld',
        'translation' => 'setTranslation',
        'breadcrumb' => 'setBreadcrumb',
        'canonical' => 'setCanonical',
        'is_indexable' => 'setIsIndexable'
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
        'is_draft' => 'getIsDraft',
        'draft_expires_at' => 'getDraftExpiresAt',
        'jsonld' => 'getJsonld',
        'translation' => 'getTranslation',
        'breadcrumb' => 'getBreadcrumb',
        'canonical' => 'getCanonical',
        'is_indexable' => 'getIsIndexable'
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
        $this->setIfExists('entity', $data ?? [], null);
        $this->setIfExists('model', $data ?? [], null);
        $this->setIfExists('language', $data ?? [], null);
        $this->setIfExists('is_draft', $data ?? [], null);
        $this->setIfExists('draft_expires_at', $data ?? [], null);
        $this->setIfExists('jsonld', $data ?? [], null);
        $this->setIfExists('translation', $data ?? [], null);
        $this->setIfExists('breadcrumb', $data ?? [], null);
        $this->setIfExists('canonical', $data ?? [], null);
        $this->setIfExists('is_indexable', $data ?? [], null);
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
     * Gets is_draft
     *
     * @return bool|null
     */
    public function getIsDraft()
    {
        return $this->container['is_draft'];
    }

    /**
     * Sets is_draft
     *
     * @param bool|null $is_draft Whether this response was served from a **draft link** rather than from published content. Always present, and `false` for every regular request. A draft link is a shareable, expiring snapshot of an entity which is still offline in Flyo: because offline content is never delivered to an integration, this is the only way such an entity can be looked at on the website at all. The snapshot is addressed by a cryptic token which takes the place of the entity's unique id and slug, so a draft is requested through the very same endpoints as any other entity — `/entities/uniqueid/{uniqueid}` and `/entities/slug/{slug}` — with the token as the parameter. Use this flag to render a visible hint that the visitor is not looking at the live page. Note that the token does not look like a normal slug or unique id, so a router which validates those parameters against a pattern has to let it through for drafts to work.
     *
     * @return self
     */
    public function setIsDraft($is_draft)
    {
        if (is_null($is_draft)) {
            throw new \InvalidArgumentException('non-nullable is_draft cannot be null');
        }
        $this->container['is_draft'] = $is_draft;

        return $this;
    }

    /**
     * Gets draft_expires_at
     *
     * @return float|null
     */
    public function getDraftExpiresAt()
    {
        return $this->container['draft_expires_at'];
    }

    /**
     * Sets draft_expires_at
     *
     * @param float|null $draft_expires_at The Unix timestamp at which the draft link stops working, or `null` when `is_draft` is `false`. Every draft link expires; after that moment the same URL answers with a 404 like any unknown entity. Useful to tell a reviewer how long the link they were sent remains valid.
     *
     * @return self
     */
    public function setDraftExpiresAt($draft_expires_at)
    {
        if (is_null($draft_expires_at)) {
            array_push($this->openAPINullablesSetToNull, 'draft_expires_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('draft_expires_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['draft_expires_at'] = $draft_expires_at;

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
     * Gets canonical
     *
     * @return string|null
     */
    public function getCanonical()
    {
        return $this->container['canonical'];
    }

    /**
     * Sets canonical
     *
     * @param string|null $canonical The **canonical URL path** of this entity, ready to be rendered as `<link rel=\"canonical\">`. A collection can be configured with several routes - a detail route, a print view, an embed - but exactly one of them is flagged as the *canonical route* in the entity mapping, and that route is the address the entity itself lives at. Its resolved path is what this field carries, and it is the very same value the sitemap and the search results carry, so a search engine is never offered two competing addresses for the same content. The path is relative to the site root and already resolved for the requested language, exactly like every other path in this API - prefix it with your own domain. Empty when the collection has no route configured at all: such an entity has no public address and must not emit a canonical tag. For a **draft link** it resolves with the draft token in place of the unique id and slug, so it points at the draft itself and not at the not yet published entity.
     *
     * @return self
     */
    public function setCanonical($canonical)
    {
        if (is_null($canonical)) {
            throw new \InvalidArgumentException('non-nullable canonical cannot be null');
        }
        $this->container['canonical'] = $canonical;

        return $this;
    }

    /**
     * Gets is_indexable
     *
     * @return bool|null
     */
    public function getIsIndexable()
    {
        return $this->container['is_indexable'];
    }

    /**
     * Sets is_indexable
     *
     * @param bool|null $is_indexable Whether this entity may be indexed by search engines. `false` when every page placing the entity's content pool is marked non-indexable and the pool is not an indexed pool: the entity is then absent from the sitemap and the search endpoint, and the consumer should render `<meta name=\"robots\" content=\"noindex\">` for the detail page. Not access control - the entity resolves by id and slug like any other. Always `false` for a draft link.
     *
     * @return self
     */
    public function setIsIndexable($is_indexable)
    {
        if (is_null($is_indexable)) {
            throw new \InvalidArgumentException('non-nullable is_indexable cannot be null');
        }
        $this->container['is_indexable'] = $is_indexable;

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


