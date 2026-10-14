# Flyo\BlocksApi



All URIs are relative to https://api.flyo.cloud/nitro/v1, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**blockCollection()**](BlocksApi.md#blockCollection) | **GET** /blocks/collection/{key} | Get a page of a block collection |


## `blockCollection()`

```php
blockCollection($key, $page, $sort, $filter, $lang): \Flyo\Model\Block
```

Get a page of a block collection

One page of the items of a block collection, sorted and filtered. A block whose `collection.enabled` is true carries only its first page in `items` (none, when `collection.inline_first_page` is false) and links to all of its pages in `collection.links`. This endpoint answers with the same block object: `uid`, `identifier`, `component`, `content`, `config` and `slots` as on the page, `items` holding the requested page, and `collection` describing that page (`total`, `page_count`, `current_page`, `sort` and `links`, for the requested sort and filter).  **Following the links.** Every link is absolute and carries the token of its collection in the `token` parameter. That token opens this one collection in one language and nothing else, so the links can be followed from a browser without exposing the token of the integration, and they need no `lang`. A collection on the live pages has a `pc-` token, cached and counted like the production token; on the preview pages a `dc-` token, which is never cached. The token stays the same for as long as the collection exists and is renewed together with the token of the integration. The tokens of the integration are accepted here as well, with `lang` on a site with several languages, and the links still carry the token of the collection. `links.base` is the collection without page, sort and filter, the start for requests of your own.  **Page size.** The block definition sets the number of items per page (`collection.per_page`), a request can not change it. A `page` past the last one answers the last page, so compare `current_page` with `page_count` when paging forward.  **Sort.** Without `sort` the items keep the order the editor set (`position`). `sort` takes one or more of the fields in `collection.sortable`, comma separated, `-` for descending: `sort=-date,title`. Entries the requested fields do not tell apart keep the order the editor set.  **Filter.** `filter` takes the fields of `collection.filterable`, in the syntax of the API SDK: `filter[title]=Kunsthaus` for equality, `filter[title][like]=Kunst`, the operators `eq`, `neq`, `lt`, `lte`, `gt`, `gte`, `in`, `nin` and `like`, and `and`, `or` and `not` to combine conditions. A field of an object is addressed with `:` (`filter[category:value]=kultur`), a field of the entries of a list with `.` (`filter[authors.name]=Anna Muster`). An unknown field or operator answers 400. The limit the editor set still applies: a filter searches the items of the collection, not the whole content pool.  **Caching.** Every url is a cache entry of its own. Follow the links as they are and avoid values that change with every request, such as the current time in a filter.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: ApiToken
$config = Flyo\Configuration::getDefaultConfiguration()->setApiKey('token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Flyo\Configuration::getDefaultConfiguration()->setApiKeyPrefix('token', 'Bearer');


$apiInstance = new Flyo\Api\BlocksApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$key = news-data; // string | The key of the collection, `collection.key` of the block: the uid of the placement unless the editor named it.
$page = 2; // int | The page to answer, starting at 1. A page past the last one answers the last page.
$sort = -date,title; // string | One or more of `collection.sortable`, comma separated, `-` for descending. `position` is the order the editor set and the default.
$filter = {"title":"Kunsthaus"}; // array<string,mixed> | Conditions on the fields of `collection.filterable`, see the description of this endpoint. A field of an object with `:`, a field of the entries of a list with `.`.
$lang = de; // string | Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages.

try {
    $result = $apiInstance->blockCollection($key, $page, $sort, $filter, $lang);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BlocksApi->blockCollection: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **key** | **string**| The key of the collection, &#x60;collection.key&#x60; of the block: the uid of the placement unless the editor named it. | |
| **page** | **int**| The page to answer, starting at 1. A page past the last one answers the last page. | [optional] [default to 1] |
| **sort** | **string**| One or more of &#x60;collection.sortable&#x60;, comma separated, &#x60;-&#x60; for descending. &#x60;position&#x60; is the order the editor set and the default. | [optional] |
| **filter** | [**array<string,mixed>**](../Model/mixed.md)| Conditions on the fields of &#x60;collection.filterable&#x60;, see the description of this endpoint. A field of an object with &#x60;:&#x60;, a field of the entries of a list with &#x60;.&#x60;. | [optional] |
| **lang** | **string**| Specifies the language context for the current request. If not provided, the default primary language will be used. This parameter has no effect if the Nitro setup is not configured for multiple languages. | [optional] |

### Return type

[**\Flyo\Model\Block**](../Model/Block.md)

### Authorization

[ApiToken](../../README.md#ApiToken)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
