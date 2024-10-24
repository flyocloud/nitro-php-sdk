# # Entity

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**entity** | [**\Flyo\Model\EntityInterface**](EntityInterface.md) |  | [optional]
**model** | **object** | All values which are mappend from inside flyo trough the mapping system are stored inside this object. | [optional]
**language** | **string** | Current language context for entity model data | [optional]
**jsonld** | **object** | A Json LD based object with schema.org informations about the entity | [optional]
**translation** | [**\Flyo\Model\Translation[]**](Translation.md) | The translation contains information about further data in different languages. If the integration is not defined as multi lingual, the translations will be empty. | [optional]
**breadcrumb** | [**\Flyo\Model\Breadcrumb[]**](Breadcrumb.md) | The breadcrumb of the current site is represented by an array of pages, forming a navigational path. It provides a hierarchical representation of the user&#39;s current location within the website. The array is ordered from the innermost page, closest to the current page, to the outermost page, with the current page itself residing at the last position. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
