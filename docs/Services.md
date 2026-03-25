## Services

These services are the stable programmatic surface of the plugin.

### TinyAuthService

Use `TinyAuthService` for resource-level permission checks.

```php
use TinyAuthBackend\Service\TinyAuthService;

$service = new TinyAuthService();
```

Available methods:

```php
// Low-level role-based check
$allowed = $service->canAccess(
    ['admin', 'moderator'],
    'Article',
    'edit',
    $article,
    $user
);

// Convenience wrapper for entity checks
$allowed = $service->canAccessResource($user, $article, 'edit');

// Type-level check without an entity instance
$allowed = $service->canPerformAbility($user, 'Article', 'create');

// Resolve the exact DB rule for one role/resource/ability triple
$rule = $service->getResourcePermission('admin', 'Article', 'edit');

// Build ORM conditions from a scoped permission
$conditions = $service->getScopeCondition(['admin'], 'Article', 'view', $user);

// Resolve the caller's role aliases from config/DB
$roles = $service->getUserRoles($user);
```

`getScopeCondition()` returns:

- `null` for no access
- `[]` for unrestricted access
- an array of conditions for scoped access

### HierarchyService

Use `HierarchyService` when you need to inspect or apply role hierarchy.

```php
use TinyAuthBackend\Service\HierarchyService;

$service = new HierarchyService();
```

Available methods:

```php
// Parent chain, nearest parent first
$parents = $service->getParentRoles('user');

// All descendants as alias => id
$children = $service->getChildRoles('admin', $availableRoles);

// Descendant aliases ordered from nearest child to deepest node
$descendants = $service->getDescendantRoleAliases('admin');

// Expand TinyAuth ACL data so higher roles inherit lower-role allows
$acl = $service->applyInheritance($acl, $availableRoles);
```

Hierarchy semantics in this plugin are:

- `parent_id` points from a lower role to a higher role
- higher roles inherit lower-role permissions
- direct rules on the current role win over inherited rules

### ControllerSyncService

Scans controllers/actions and stores them in the backend tables.

```php
use TinyAuthBackend\Service\ControllerSyncService;

$service = new ControllerSyncService();
$result = $service->sync();
$controllers = $service->scan();
```

`sync()` returns:

```php
['added' => 0, 'updated' => 0, 'actions_added' => 0]
```

### ResourceSyncService

Scans entities and stores them as resources.

```php
use TinyAuthBackend\Service\ResourceSyncService;

$service = new ResourceSyncService();
$result = $service->sync();
$resources = $service->scan();
```

By default, synced resources get these abilities:

```php
['view', 'create', 'edit', 'delete']
```

You can override them:

```php
$service->setDefaultAbilities(['view', 'publish']);
```

### FeatureService

Determines which backend sections are enabled.

```php
use TinyAuthBackend\Service\FeatureService;

$service = new FeatureService();
$enabled = $service->isEnabled('resources');
$items = $service->getNavigationItems();
```

Known feature keys:

- `allow`
- `acl`
- `roles`
- `resources`
- `scopes`

### RoleSourceService

Resolves roles from the database or an external config/callback source.

```php
use TinyAuthBackend\Service\RoleSourceService;

$service = new RoleSourceService();
$roles = $service->getRoles();
$entities = $service->getRoleEntities();
$managed = $service->isManaged();
```
