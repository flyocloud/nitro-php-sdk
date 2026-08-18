# Upgrade

## From 2.2 to 2.3

Generated against OpenAPI spec `2.30` (was `2.28`).

### `routes` is a plain array again

The `Routes` model class introduced in 2.2 has been removed. `EntityInterface::getRoutes()`
and `EntityinterfaceInner::getRoutes()` return `array<string,mixed>|null` again, so all
route keys are accessible and no longer swallowed by the model.

```php
// 2.2
$entity->getRoutes()->getEmpty();

// 2.3
$routes = $entity->getRoutes();
$routes['_empty']; // bool: true when no route could be resolved
$routes['detail']; // string: "/foo-bar"
```

If you type-hinted against `\Flyo\Model\Routes`, replace it with `array`.
