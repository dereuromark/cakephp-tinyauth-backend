# Full TinyAuthBackend Strategy

Use this mode if you want the backend to be the main source of truth for:

- controller/action access
- public actions
- roles and hierarchy
- resource abilities
- scoped entity permissions

## Typical stack

- TinyAuth for request-level `allow` and `acl`
- TinyAuthBackend for administration and storage
- CakePHP Authorization for entity/resource checks

## Recommended feature set

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

## Recommended flow

1. Migrate the plugin tables.
2. Configure TinyAuth DB adapters.
3. Sync controllers and resources (`bin/cake tiny_auth_backend sync`).
4. Wire `cakephp/authorization` with the plugin's `TinyAuthResolver` — see
   [Authorization Integration](/authorization/) for the full wiring guide.
5. Manage request and resource permissions from the same backend.

## Minimal Authorization wiring

```php
// Application::getAuthorizationService()
use Authorization\AuthorizationService;
use TinyAuthBackend\Policy\TinyAuthResolver;

$resolver = new TinyAuthResolver([
    \App\Model\Entity\Article::class,
    \App\Model\Entity\Project::class,
]);

return new AuthorizationService($resolver);
```

`TinyAuthResolver` transparently unwraps `SelectQuery` resources to their
repository, so the same resolver works for both
`$this->Authorization->authorize($entity, 'edit')` and
`$this->Authorization->applyScope($query)`.

## Good fit

Choose this mode if:

- you want one admin backend for both request and entity authorization
- you use role hierarchy
- you need scoped permissions like "own records only"
