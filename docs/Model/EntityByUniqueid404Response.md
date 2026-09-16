# # EntityByUniqueid404Response

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** |  | [optional]
**message** | **string** |  | [optional]
**status** | **int** |  | [optional]
**entity_unique_id** | **string** | The unique id of the entity the expired draft link pointed at. A draft token carries it, so a link which has stopped working still says what it was about: look the entity up through &#x60;/entities/uniqueid/{uniqueid}&#x60; to find out whether it is published by now. | [optional]
**href** | **string** | The path that entity is published under now, ready to redirect to, or &#x60;null&#x60; when it is still offline or no longer exists. Relative to the site, like the &#x60;href&#x60; of a sitemap or search entry, and read from the same indexed value as the &#x60;canonical&#x60; of a successful entity response. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
