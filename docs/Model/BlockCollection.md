# # BlockCollection

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**enabled** | **bool** | Whether the items of this block are a collection the frontend can page, sort and filter through &#x60;links&#x60;. When false, &#x60;items&#x60; holds every item and the counters describe them. |
**key** | **string** | The key of the collection, the last segment of its url (&#x60;/blocks/collection/{key}&#x60;): the uid of the placement, unless the editor named it. |
**per_page** | **int** | The items per page, set in the block definition. A request can not ask for another page size. |
**inline_first_page** | **bool** | Whether the page JSON carries the first page in &#x60;items&#x60;. When false, &#x60;items&#x60; is empty in the page JSON and the first page is loaded through &#x60;links.first&#x60;. |
**total** | **int** | The number of items, filtered when the request has a filter. Never more than the limit the editor set. |
**page_count** | **int** | The number of pages, at least 1. |
**current_page** | **int** | The page &#x60;items&#x60; holds, starting at 1. |
**sort** | **string** | The sort of this page: one or more of &#x60;sortable&#x60;, comma separated, &#x60;-&#x60; for descending. &#x60;position&#x60; is the order the editor set, and entries the requested fields do not tell apart keep that order. Null when the block is no collection. |
**sortable** | **string[]** | The fields &#x60;sort&#x60; accepts. &#x60;position&#x60; stands for the order the editor set. |
**filterable** | **string[]** | The fields &#x60;filter&#x60; accepts. A field of an object is addressed with &#x60;:&#x60; (&#x60;category:value&#x60;), a field of the entries of a list with &#x60;.&#x60; (&#x60;authors.name&#x60;). |
**links** | [**\Flyo\Model\BlockCollectionLinks**](BlockCollectionLinks.md) |  |

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
