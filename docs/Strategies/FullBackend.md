## Full TinyAuthBackend Strategy

Use this mode if you want the backend to be the main source of truth for:

- controller/action access
- public actions
- roles and hierarchy
- resource abilities
- scoped entity permissions

### Typical Stack

- TinyAuth for request-level `allow` and `acl`
- TinyAuthBackend for administration and storage
- CakePHP Authorization for entity/resource checks

### Recommended Feature Set

```php
'TinyAuthBackend' => [
    'features' => [
        'allow' => true,
        'acl' => true,
        'roles' => true,
        'resources' => true,
        'scopes' => true,
    ],
],
```

### Recommended Flow

1. Migrate the plugin tables.
2. Configure TinyAuth DB adapters.
3. Sync controllers and resources.
4. Set up `TinyAuthPolicy` or custom policies for your entities.
5. Manage request and resource permissions from the same backend.

### Good Fit

Choose this mode if:

- you want one admin backend for both request and entity authorization
- you use role hierarchy
- you need scoped permissions like "own records only"
