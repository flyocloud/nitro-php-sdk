# # EntityInterface

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_version** | **float** | A sequential index serves as a version identifier for an item, allowing for improved organization and extended functionality. By assigning a unique numerical value to each version, it becomes easier to track and manage different iterations of an item over time. | [optional]
**entity_metric** | [**\Flyo\Model\EntityMetric**](EntityMetric.md) |  | [optional]
**entity_unique_id** | **string** | Unique ID | [optional]
**entity_id** | **float** | An unique ID across the flyo sytem | [optional]
**entity_image** | **string** | For image manipulation please see https://dev.flyo.cloud/dev/infos/images.html | [optional]
**entity_slug** | **string** | The slug for the given item, this can be either unique or not, depending on the configuration of the entity definition schema. | [optional]
**entity_teaser** | **string** | The standard interface teaser resolved for the current entity | [optional]
**entity_time_end** | **float** | The shared entity interface time end attribute. If not defined, null or 0 is returned | [optional]
**entity_time_start** | **float** | The shared entity interface time start attribute. If not defined, null or 0 is returned | [optional]
**entity_title** | **string** | The standard interface title resolved for the current entity | [optional]
**entity_type** | **string** |  | [optional]
**entity_type_id** | **float** | The Type-ID, alternatively referred to as the Entity-Definition-Schema ID, serves as a crucial identifier within the system. It uniquely distinguishes and categorizes the Entity-Definition-Schema. | [optional]
**updated_at** | **float** | A Unix timestamp indicating when the entity has been updated last time in Flyo. | [optional]
**routes** | **array<string,string>** |  | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
