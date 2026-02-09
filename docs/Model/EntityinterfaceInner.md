# # EntityinterfaceInner

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**entity_unique_id** | **string** | Unique ID | [optional]
**entity_title** | **string** | The standard interface title resolved for the current entity | [optional]
**entity_teaser** | **string** | The standard interface teaser resolved for the current entity | [optional]
**entity_slug** | **string** | The slug for the given item, this can be either unique or not, depending on the configuration of the entity definition schema. | [optional]
**entity_time_start** | **float** | The shared entity interface time start attribute. If not defined, null or 0 is returned | [optional]
**entity_type** | **string** |  | [optional]
**entity_type_id** | **float** | The Type-ID, alternatively referred to as the Entity-Definition-Schema ID, serves as a crucial identifier within the system. It uniquely distinguishes and categorizes the Entity-Definition-Schema. | [optional]
**entity_image** | **string** | For image manipulation please see https://docs.flyo.cloud/doc/assets-images | [optional]
**href** | **string** | Returns the completed href tag. Internal links are appended with trailing slashes, such as &#x60;/about-me&#x60;, while email links are formatted with &#x60;mailto:hello@flyo.ch&#x60;. | [optional]
**routes** | **array<string,string>** |  | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
