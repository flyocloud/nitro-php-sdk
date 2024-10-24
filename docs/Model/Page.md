# # Page

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID for the page | [optional]
**title** | **string** |  | [optional]
**href** | **string** | Returns the completed href tag. Internal links are appended with trailing slashes, such as &#x60;/about-me&#x60;, while email links are formatted with &#x60;mailto:hello@flyo.ch&#x60;. | [optional]
**slug** | **string** | The slug, in its current form, contains the full path of nested slugs and serves as the identifier for querying the respective page. | [optional]
**json** | [**\Flyo\Model\Block[]**](Block.md) |  | [optional]
**depth** | **int** | The depth of the page in the tree structure | [optional]
**is_home** | **int** | Determining whether the page is the homepage or not. | [optional]
**created_at** | **int** | A Unix timestamp indicating when the page was created. | [optional]
**updated_at** | **int** | A Unix timestamp indicating when the page was last updated. | [optional]
**is_visible** | **int** | Determining whether the page is visible or not. | [optional]
**meta_json** | [**\Flyo\Model\Meta**](Meta.md) |  | [optional]
**properties** | [**array<string,\Flyo\Model\PageProperty>**](PageProperty.md) |  | [optional]
**uid** | **string** | A unique identifier for the page | [optional]
**type** | **string** | Can be either a page with content (which is default behavior), email, file, url, tel | [optional]
**target** | **string** | can be either _self (which is default) or _blank | [optional]
**container** | **string** | The container this page belongs, by default all pages belong to the default container which is the main nav. | [optional]
**breadcrumb** | [**\Flyo\Model\Breadcrumb[]**](Breadcrumb.md) | The breadcrumb of the current site is represented by an array of pages, forming a navigational path. It provides a hierarchical representation of the user&#39;s current location within the website. The array is ordered from the innermost page, closest to the current page, to the outermost page, with the current page itself residing at the last position. | [optional]
**translation** | [**\Flyo\Model\Translation[]**](Translation.md) | The translation contains information about further data in different languages. If the integration is not defined as multi lingual, the translations will be empty. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
