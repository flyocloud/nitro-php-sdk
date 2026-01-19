# Flyo\EntitiesApi

All URIs are relative to https://api.flyo.cloud/nitro/v1, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**entityBySlug()**](EntitiesApi.md#entityBySlug) | **GET** /entities/slug/{slug} | Find entity by slug and optional Entity-Type-ID |
| [**entityByUniqueid()**](EntitiesApi.md#entityByUniqueid) | **GET** /entities/uniqueid/{uniqueid} | Find entity by uniqueid |


## `entityBySlug()`

```php
entityBySlug($slug, $type_id, $lang): \Flyo\Model\Entity
```

Find entity by slug and optional Entity-Type-ID

The endpoint allows for the retrieval of entities based on their slug, with an optional Entity-Type-ID for more accurate results. The endpoint requires a `slug` parameter to be passed in the path, which is necessary for identifying the entity. However, since slugs are not unique across different entities, it is highly recommended to also provide the `typeId` parameter through the query to avoid incorrect or unintended results. This `typeId` serves as an Entity-Definition-Schema ID, ensuring a more precise and targeted entity retrieval by distinguishing the entities more clearly. The `slug` parameter is mandatory and should be a string (e.g., 'hello-world'), while the `typeId` parameter is optional and should be an integer (e.g., 123), representing the Entity-Definition-Schema ID.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiToken
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');

$apiInstance = new Flyo\Api\EntitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$slug = hello-world; // string | When looking up an entity slug, it is advisable to provide the typeId parameter along with it, as slugs are not unique among other entities. Failing to include the typeId parameter may lead to unintended or incorrect results. By specifying the typeId, you can ensure more accurate and targeted retrieval of the desired entity.
$type_id = 123; // int | To ensure accurate lookup, it is considered a best practice to include the Type-ID of the entity associated with the slug. The Type-ID, alternatively referred to as the Entity-Definition-Schema ID, serves as a crucial identifier within the system. It uniquely distinguishes and categorizes the Entity-Definition-Schema.
$lang = de; // string | Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages.

try {
    $result = $apiInstance->entityBySlug($slug, $type_id, $lang);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EntitiesApi->entityBySlug: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **slug** | **string**| When looking up an entity slug, it is advisable to provide the typeId parameter along with it, as slugs are not unique among other entities. Failing to include the typeId parameter may lead to unintended or incorrect results. By specifying the typeId, you can ensure more accurate and targeted retrieval of the desired entity. | |
| **type_id** | **int**| To ensure accurate lookup, it is considered a best practice to include the Type-ID of the entity associated with the slug. The Type-ID, alternatively referred to as the Entity-Definition-Schema ID, serves as a crucial identifier within the system. It uniquely distinguishes and categorizes the Entity-Definition-Schema. | [optional] |
| **lang** | **string**| Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. | [optional] |

### Return type

[**\Flyo\Model\Entity**](../Model/Entity.md)

### Authorization

[ApiToken](../../README.md#ApiToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `entityByUniqueid()`

```php
entityByUniqueid($uniqueid, $lang): \Flyo\Model\Entity
```

Find entity by uniqueid

The endpoint provides comprehensive information about a specified entity. An entity represents a collection of information pertaining to a specific data type and is defined by a key-value pair. You can use various data types such as blogs, events, or any other relevant data. However, in order to access an entity, it must be properly configured within the nitro config.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiToken
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');

$apiInstance = new Flyo\Api\EntitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$uniqueid = 2348uc; // string | The unique identifier of the given entity is a string composed solely of lowercase alphabetic characters (a-z) and numbers. This identifier is meticulously generated for each data row, ensuring its uniqueness and facilitating efficient data management and retrieval across content pools.
$lang = de; // string | Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages.

try {
    $result = $apiInstance->entityByUniqueid($uniqueid, $lang);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EntitiesApi->entityByUniqueid: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **uniqueid** | **string**| The unique identifier of the given entity is a string composed solely of lowercase alphabetic characters (a-z) and numbers. This identifier is meticulously generated for each data row, ensuring its uniqueness and facilitating efficient data management and retrieval across content pools. | |
| **lang** | **string**| Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. | [optional] |

### Return type

[**\Flyo\Model\Entity**](../Model/Entity.md)

### Authorization

[ApiToken](../../README.md#ApiToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
