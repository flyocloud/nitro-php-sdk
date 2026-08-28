# Flyo\SitemapApi



All URIs are relative to https://api.flyo.cloud/nitro/v1, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sitemap()**](SitemapApi.md#sitemap) | **GET** /sitemap | Get Sitemap |


## `sitemap()`

```php
sitemap($lang): \Flyo\Model\SitemapinterfaceInner[]
```

Get Sitemap

This endpoint provides the data needed to generate a sitemap. It encompasses pages from containers as well as all entities that have been mapped. Each item includes an `href` attribute containing the resolved URL path, an `updated_at` Unix timestamp which should be used as the `lastmod` value of the sitemap entry, and the `entity_unique_id` to correlate the entry with its entity. Entities and pages without a resolvable URL are omitted. The response is deliberately reduced to those three fields (see the Sitemap Item schema) and is streamed as a bare JSON array without pagination; presentation fields such as title, teaser and image, and the `routes` map, are only available on the entities and search endpoints. In multi-lingual setups, the sitemap returns all language variants of every entity and page, regardless of the `lang` parameter. This ensures complete SEO coverage across all configured languages.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiToken
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');


$apiInstance = new Flyo\Api\SitemapApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$lang = de; // string | Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages.

try {
    $result = $apiInstance->sitemap($lang);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SitemapApi->sitemap: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **lang** | **string**| Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. | [optional] |

### Return type

[**\Flyo\Model\SitemapinterfaceInner[]**](../Model/SitemapinterfaceInner.md)

### Authorization

[ApiToken](../../README.md#ApiToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
