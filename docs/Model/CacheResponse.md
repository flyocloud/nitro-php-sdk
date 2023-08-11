# # CacheResponse

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**version** | **int** | Whenever Flyo generates new data, the version number will be appropriately incremented. If this value is employed for caching, it will undergo less frequent purging in comparison to the &#x60;updated_at&#x60; value, thus extending its longevity. (last longer) | [optional]
**updated_at** | **int** | A Unix timestamp indicating when the Nitro was last updated. This timestamp does not correlate with the version number. If this value is utilized for caching purposes, it will experience more frequent purges compared to the version number, leading to shorter caching intervals. (last shorter) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
