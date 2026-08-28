# # Entity

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**entity** | [**\Flyo\Model\EntityInterface**](EntityInterface.md) |  | [optional]
**model** | **object** | All values which are mappend from inside flyo trough the mapping system are stored inside this object. | [optional]
**language** | **string** | Current language context for entity model data | [optional]
**is_draft** | **bool** | Whether this response was served from a **draft link** rather than from published content. Always present, and &#x60;false&#x60; for every regular request. A draft link is a shareable, expiring snapshot of an entity which is still offline in Flyo: because offline content is never delivered to an integration, this is the only way such an entity can be looked at on the website at all. The snapshot is addressed by a cryptic token which takes the place of the entity&#39;s unique id and slug, so a draft is requested through the very same endpoints as any other entity — &#x60;/entities/uniqueid/{uniqueid}&#x60; and &#x60;/entities/slug/{slug}&#x60; — with the token as the parameter. Use this flag to render a visible hint that the visitor is not looking at the live page. Note that the token does not look like a normal slug or unique id, so a router which validates those parameters against a pattern has to let it through for drafts to work. | [optional]
**draft_expires_at** | **float** | The Unix timestamp at which the draft link stops working, or &#x60;null&#x60; when &#x60;is_draft&#x60; is &#x60;false&#x60;. Every draft link expires; after that moment the same URL answers with a 404 like any unknown entity. Useful to tell a reviewer how long the link they were sent remains valid. | [optional]
**jsonld** | **object** | A Json LD based object with schema.org informations about the entity | [optional]
**translation** | [**\Flyo\Model\Translation[]**](Translation.md) | The translation contains information about further data in different languages. If the integration is not defined as multi lingual, the translations will be empty. | [optional]
**breadcrumb** | [**\Flyo\Model\Breadcrumb[]**](Breadcrumb.md) | The breadcrumb of the current site is represented by an array of pages, forming a navigational path. It provides a hierarchical representation of the user&#39;s current location within the website. The array is ordered from the innermost page, closest to the current page, to the outermost page, with the current page itself residing at the last position. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
