# # Page

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** |  | [optional]
**title** | **string** |  | [optional]
**slug** | **string** |  | [optional]
**json** | [**\Flyo\Model\Block[]**](Block.md) |  | [optional]
**depth** | **int** |  | [optional]
**is_home** | **int** |  | [optional]
**created_at** | **int** |  | [optional]
**updated_at** | **int** |  | [optional]
**is_visible** | **int** |  | [optional]
**meta_json** | [**\Flyo\Model\Meta**](Meta.md) |  | [optional]
**properties** | [**array<string,\Flyo\Model\PageProperty>**](PageProperty.md) |  | [optional]
**uid** | **string** | A unique identifier for the page | [optional]
**type** | **string** | Can be either a page with content (which is default behavior), email, file, url, tel | [optional]
**target** | **string** | can be either _self (which is default) or _blank | [optional]
**container** | **string** | The container this page belongs, by default all pages belong to the default container which is the main nav. | [optional]
**breadcrumb** | [**\Flyo\Model\PageBreadcrumbInner[]**](PageBreadcrumbInner.md) | The breadcrumb of the current site is represented by an array of pages, forming a navigational path. It provides a hierarchical representation of the user&#39;s current location within the website. The array is ordered from the innermost page, closest to the current page, to the outermost page, with the current page itself residing at the last position. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
