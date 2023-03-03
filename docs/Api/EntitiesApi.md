# Flyo\EntitiesApi

All URIs are relative to https://api.flyo.cloud/nitro, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**entity()**](EntitiesApi.md#entity) | **GET** /entities/{uniqueid} | Get entity by uniqueid |


## `entity()`

```php
entity($uniqueid): \Flyo\Model\EntityResponse
```

Get entity by uniqueid

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiKeyAuth
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');


$apiInstance = new Flyo\Api\EntitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$uniqueid = 2348uc; // string | The unique id of the given entity

try {
    $result = $apiInstance->entity($uniqueid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EntitiesApi->entity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **uniqueid** | **string**| The unique id of the given entity | |

### Return type

[**\Flyo\Model\EntityResponse**](../Model/EntityResponse.md)

### Authorization

[ApiKeyAuth](../../README.md#ApiKeyAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
