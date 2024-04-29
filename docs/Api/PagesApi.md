# Flyo\PagesApi

All URIs are relative to https://api.flyo.cloud/nitro/v1, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**home()**](PagesApi.md#home) | **GET** /pages/home | Get Home |
| [**page()**](PagesApi.md#page) | **GET** /pages | Get Page by slug |


## `home()`

```php
home($lang): \Flyo\Model\Page
```

Get Home

This endpoint allows you to retrieve the designated homepage of a website. Alternatively, you can utilize the pages endpoint by specifying an empty slug parameter to achieve the same result. By using either of these methods, you can effectively access the desired homepage of the website.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiToken
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');


$apiInstance = new Flyo\Api\PagesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$lang = de; // string | The language context for the current request. If not defined, the defed primary language will be used. If the nitro setup is not configured as multi lingual, the language parameter won't have any effect

try {
    $result = $apiInstance->home($lang);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PagesApi->home: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **lang** | **string**| The language context for the current request. If not defined, the defed primary language will be used. If the nitro setup is not configured as multi lingual, the language parameter won&#39;t have any effect | [optional] |

### Return type

[**\Flyo\Model\Page**](../Model/Page.md)

### Authorization

[ApiToken](../../README.md#ApiToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `page()`

```php
page($slug, $lang): \Flyo\Model\Page
```

Get Page by slug

This endpoint retrieves comprehensive information from a specified page using either a slug or a path. The slug refers to a unique identifier for the page, while the path is the slug with a leading slash. By providing either the slug or the path as input, the function will gather all the relevant details associated with the page.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiToken
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');


$apiInstance = new Flyo\Api\PagesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$slug = foo/bar; // string | The function retrieves a specific page by its slug. If no slug is provided, it automatically returns the homepage. Moreover, it seamlessly handles paths with subpages, allowing for nested URLs like \"testpage/subpage\". In this way, a forward slash (\"/\") within the path is recognized as a valid character and processed accordingly.
$lang = de; // string | The language context for the current request. If not defined, the defed primary language will be used. If the nitro setup is not configured as multi lingual, the language parameter won't have any effect

try {
    $result = $apiInstance->page($slug, $lang);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PagesApi->page: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **slug** | **string**| The function retrieves a specific page by its slug. If no slug is provided, it automatically returns the homepage. Moreover, it seamlessly handles paths with subpages, allowing for nested URLs like \&quot;testpage/subpage\&quot;. In this way, a forward slash (\&quot;/\&quot;) within the path is recognized as a valid character and processed accordingly. | [optional] |
| **lang** | **string**| The language context for the current request. If not defined, the defed primary language will be used. If the nitro setup is not configured as multi lingual, the language parameter won&#39;t have any effect | [optional] |

### Return type

[**\Flyo\Model\Page**](../Model/Page.md)

### Authorization

[ApiToken](../../README.md#ApiToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
