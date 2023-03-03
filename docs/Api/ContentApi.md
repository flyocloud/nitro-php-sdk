# Flyo\ContentApi

All URIs are relative to https://api.flyo.cloud/nitro, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**putContent()**](ContentApi.md#putContent) | **PUT** /content/{pageId} | Put content |


## `putContent()`

```php
putContent($page_id, $content)
```

Put content

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiKeyAuth
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');


$apiInstance = new Flyo\Api\ContentApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$page_id = 123; // string | The id of the given page
$content = new \Flyo\Model\Content(); // \Flyo\Model\Content

try {
    $apiInstance->putContent($page_id, $content);
} catch (Exception $e) {
    echo 'Exception when calling ContentApi->putContent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_id** | **string**| The id of the given page | |
| **content** | [**\Flyo\Model\Content**](../Model/Content.md)|  | [optional] |

### Return type

void (empty response body)

### Authorization

[ApiKeyAuth](../../README.md#ApiKeyAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
