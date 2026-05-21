# Feature Flags

You can force-enable or disable parts of the backend with
`TinyAuthBackend.features`. This is useful when you only want the classic
TinyAuth adapter functionality exposed in the UI.

```php
'TinyAuthBackend' => [
    'features' => [
        'allow' => true,
        'acl' => true,
        'roles' => true,
        'resources' => false,
        'scopes' => false,
    ],
],
```

## Known feature keys

| Key | Section | Manages |
|-----|---------|---------|
| `allow` | [Allow (Public Actions)](/permissions/allow) | Actions reachable without authentication |
| `acl` | [ACL Matrix](/permissions/acl) | Controller/action role permissions |
| `roles` | [Roles](/permissions/roles) | Role aliases, ordering, and hierarchy |
| `resources` | [Resources](/permissions/resources) | Entity-level abilities |
| `scopes` | [Scopes](/permissions/scopes) | Reusable field-comparison conditions |

A disabled feature is removed from the navigation and its controllers reject
requests, so you can trim the backend down to exactly the surface you use.

::: tip Adapter-only setups
For a classic TinyAuth setup that only stores `allow` and `acl` in the database,
disable `resources` and `scopes` — see the
[Adapter-Only strategy](/strategies/adapter-only).
:::

## Inspecting flags at runtime

The [`FeatureService`](/reference/services#featureservice) resolves which sections
are enabled and builds the navigation:

```php
use TinyAuthBackend\Service\FeatureService;

$service = new FeatureService();
$enabled = $service->isEnabled('resources');
$items = $service->getNavigationItems();
```
