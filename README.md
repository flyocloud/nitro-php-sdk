# Flyo Nitro PHP SDK

🚀 **Supercharge your PHP applications with Flyo Nitro!** This SDK provides a clean, intuitive interface to interact with the Flyo Nitro API, enabling you to manage content, configurations, and entities with ease.

## 📦 Installation

Install the SDK via Composer:

```sh
composer require flyo/nitro-php
```

## 🚀 Quick Start

Get up and running in just a few lines of code:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure authentication
$config = new Flyo\Configuration();
$config->setApiKey('token', 'YOUR_FLYO_AUTH_TOKEN');

Flyo\Configuration::setDefaultConfiguration($config);

// Make your first API call
$api = new Flyo\Api\ConfigApi();
try {
    $result = $api->config();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConfigApi->config: ', $e->getMessage(), PHP_EOL;
}
```

## 📚 Available APIs

The SDK provides access to the following API endpoints:

| API | Description | Source |
|-----|-------------|--------|
| **ConfigApi** | Retrieve configuration and settings | [`lib/Api/ConfigApi.php`](lib/Api/ConfigApi.php) |
| **EntitiesApi** | Manage and query entities | [`lib/Api/EntitiesApi.php`](lib/Api/EntitiesApi.php) |
| **PagesApi** | Handle page content and structure | [`lib/Api/PagesApi.php`](lib/Api/PagesApi.php) |
| **SearchApi** | Perform content searches | [`lib/Api/SearchApi.php`](lib/Api/SearchApi.php) |
| **SitemapApi** | Generate and manage sitemaps | [`lib/Api/SitemapApi.php`](lib/Api/SitemapApi.php) |
| **VersionApi** | Check API version information | [`lib/Api/VersionApi.php`](lib/Api/VersionApi.php) |

## 📖 Documentation

For comprehensive guides, examples, and API reference:

- 📘 [Full Documentation](https://docs.flyo.cloud/doc/integrations-nitro-cms-sdks)
- 🔍 [API Reference](docs/)