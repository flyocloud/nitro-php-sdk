# # PagesInner

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**type** | **string** | The &#x60;page&#x60; type is the default behavior for a webpage, but it can also be assigned different types to suit specific purposes. In addition to the default content type, there are several other types available, including &#x60;email&#x60;, &#x60;file&#x60;, &#x60;url&#x60; and &#x60;tel&#x60;. By specifying these types, you can customize the behavior and functionality of your page to better align with your requirements. | [optional]
**target** | **string** | can be either _self (which is default) or _blank | [optional]
**label** | **string** |  | [optional]
**href** | **string** |  | [optional]
**path** | **string** | A path always starts with a trailing slash | [optional]
**properties** | [**\Flyo\Model\ContainerPageProperty**](ContainerPageProperty.md) |  | [optional]
**children** | [**\Flyo\Model\PagesInner[]**](PagesInner.md) | Represents a comprehensive collection of pages within the specified container. These pages are organized in a nested tree structure, where each page can have child pages associated with it. These child pages are conveniently stored within the children property of their respective parent page. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
