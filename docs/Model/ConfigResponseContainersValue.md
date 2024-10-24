# # ConfigResponseContainersValue

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**items** | [**\Flyo\Model\ContainerPage[]**](ContainerPage.md) | Represents a comprehensive collection of pages within the specified container. These pages are organized in a nested tree structure, where each page can have child pages associated with it. These child pages are conveniently stored within the children property of their respective parent page. | [optional]
**uid** | **string** | A unique identifier, known as a container ID, is assigned to each container during its creation and remains unchanged throughout its lifespan. This container ID serves as a permanent and unalterable reference for the container. | [optional]
**identifier** | **string** | The identifier is a unique string consisting solely of lowercase letters, and it is intended to remain constant throughout its usage. However, in the event that there are no associated pages linked to the identifier, it can be modified within the nitro configuration. | [optional]
**label** | **string** | You can customize and assign a unique label to the container of your choice. This label can be easily modified through the nitro configuration settings. This flexibility allows you to personalize and adapt the container&#39;s identification based on your specific needs. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
