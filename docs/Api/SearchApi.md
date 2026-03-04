# Flyo\SearchApi



All URIs are relative to https://api.flyo.cloud/nitro/v1, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**search()**](SearchApi.md#search) | **GET** /search | Get Search by query |


## `search()`

```php
search($query, $sort, $page, $lang): \Flyo\Model\EntityinterfaceInner[]
```

Get Search by query

This endpoint offers a powerful full-text search through the websites sitemap, encompassing both pages and entities. It performs word-based matching against entity titles and teasers. Each result includes a `score` field representing the relevance of the match. By default, results are sorted by relevance (highest score first). Each result also includes an `href` attribute containing the resolved URL path for the entity. In multi-lingual setups, search results are automatically filtered to match the current language context specified via the `lang` parameter, ensuring only results relevant to the requested language are returned.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiToken
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');


$apiInstance = new Flyo\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$query = foobar; // string | The query keyword that needs to be looked up. It is important to ensure that the query is properly URL encoded for accurate processing and retrieval.
$sort = score; // string | Optional sort field. If not provided, results are sorted by `score` (relevance) by default which is the recommended behavior for search. The `score` value reflects how well the result matches the query — higher scores indicate better matches. Use `-` as a prefix for descending order (for example `-title`). Ascending order uses the plain field name (for example `title`). A `+` prefix is not supported. The `time_start` field is derived from the shared entity time interface and is only meaningful for entities that implement it; entities without that interface return `time_start` as `0`, so use this sort carefully.
$page = 1; // int | The page number for paginated results. Defaults to 1 if not provided.
$lang = de; // string | Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages.

try {
    $result = $apiInstance->search($query, $sort, $page, $lang);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->search: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **query** | **string**| The query keyword that needs to be looked up. It is important to ensure that the query is properly URL encoded for accurate processing and retrieval. | |
| **sort** | **string**| Optional sort field. If not provided, results are sorted by &#x60;score&#x60; (relevance) by default which is the recommended behavior for search. The &#x60;score&#x60; value reflects how well the result matches the query — higher scores indicate better matches. Use &#x60;-&#x60; as a prefix for descending order (for example &#x60;-title&#x60;). Ascending order uses the plain field name (for example &#x60;title&#x60;). A &#x60;+&#x60; prefix is not supported. The &#x60;time_start&#x60; field is derived from the shared entity time interface and is only meaningful for entities that implement it; entities without that interface return &#x60;time_start&#x60; as &#x60;0&#x60;, so use this sort carefully. | [optional] |
| **page** | **int**| The page number for paginated results. Defaults to 1 if not provided. | [optional] [default to 1] |
| **lang** | **string**| Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. | [optional] |

### Return type

[**\Flyo\Model\EntityinterfaceInner[]**](../Model/EntityinterfaceInner.md)

### Authorization

[ApiToken](../../README.md#ApiToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
