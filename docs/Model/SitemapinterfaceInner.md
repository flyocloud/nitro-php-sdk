# # SitemapinterfaceInner

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**entity_unique_id** | **string** | Unique ID | [optional]
**updated_at** | **float** | A Unix timestamp indicating when the entity has been updated last time in Flyo. For entries which represent a Nitro page, this is the last time the content delivered for that page actually changed — a rebuild which produces identical output does not move it. Use this value as the &#x60;lastmod&#x60; information when generating a sitemap. | [optional]
**href** | **string** | Returns the completed href tag. Internal links are appended with trailing slashes, such as &#x60;/about-me&#x60;, while email links are formatted with &#x60;mailto:hello@flyo.ch&#x60;. | [optional]
**entity_type** | **string** | Deprecated — read &#x60;href&#x60; instead, which is already resolved for both pages (&#x60;nitro-page&#x60;) and mapped entities. Only kept for consumers that branch on the type to decide how to build the URL themselves. | [optional]
**entity_slug** | **string** | Deprecated — read &#x60;href&#x60; instead. Only kept for consumers that build a container page URL from its slug. | [optional]
**routes** | **array<string,mixed>** | Deprecated — read &#x60;href&#x60; instead, which holds the first resolved route. Only kept for consumers that pick a specific route identifier such as &#x60;routes.detail&#x60;. Includes the system key &#x60;_empty&#x60; (boolean): &#x60;false&#x60; means at least one route could be resolved. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
