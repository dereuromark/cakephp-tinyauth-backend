## Services

The plugin provides several services for programmatic access to permissions.

### TinyAuthService

Central service for permission checking.

```php
use TinyAuthBackend\Service\TinyAuthService;

$service = new TinyAuthService();
```

#### Methods

```php
// Check if action is public (no auth required)
$isPublic = $service->isPublicAction(
    string $controller,
    string $action,
    array $options = []  // ['plugin' => '', 'prefix' => '']
): bool;

// Check if user has ACL access to an action
$hasAccess = $service->hasAccess(
    IdentityInterface $user,
    string $controller,
    string $action,
    array $options = []
): bool;

// Check if user can access a resource with an ability
$canAccess = $service->canAccessResource(
    IdentityInterface $user,
    EntityInterface $entity,
    string $ability
): bool;

// Get all abilities user has for a resource
$abilities = $service->getResourceAbilities(
    IdentityInterface $user,
    EntityInterface $entity
): array;
```

### HierarchyService

Manages role hierarchy traversal.

```php
use TinyAuthBackend\Service\HierarchyService;

$service = new HierarchyService();
```

#### Methods

```php
// Get all roles user can access (including inherited)
$roles = $service->getEffectiveRoles(int $roleId): array;

// Get complete role hierarchy
$hierarchy = $service->getHierarchy(): array;

// Check if roleA is above roleB in hierarchy
$isAbove = $service->isRoleAbove(int $roleA, int $roleB): bool;

// Get parent roles for a role
$parents = $service->getParentRoles(int $roleId): array;

// Get child roles for a role
$children = $service->getChildRoles(int $roleId): array;
```

### ControllerSyncService

Syncs controllers and actions from the application.

```php
use TinyAuthBackend\Service\ControllerSyncService;

$service = new ControllerSyncService();
```

#### Methods

```php
// Sync all controllers and actions
$result = $service->sync(): array;
// Returns: ['added' => 5, 'updated' => 2, 'removed' => 1]

// Get all discovered controllers
$controllers = $service->discoverControllers(): array;

// Get actions for a controller class
$actions = $service->discoverActions(string $controllerClass): array;
```

### ResourceSyncService

Syncs entity resources from the application.

```php
use TinyAuthBackend\Service\ResourceSyncService;

$service = new ResourceSyncService();
```

#### Methods

```php
// Sync all entity resources
$result = $service->sync(): array;

// Discover entity classes
$entities = $service->discoverEntities(): array;
```

### ImportExportService

Handles import/export of permissions.

```php
use TinyAuthBackend\Service\ImportExportService;

$service = new ImportExportService();
```

#### Methods

```php
// Export all permissions as JSON
$json = $service->exportAsJson(): string;

// Export ACL permissions as CSV
$csv = $service->exportAclAsCsv(): string;

// Export allow rules as CSV
$csv = $service->exportAllowAsCsv(): string;

// Import from JSON
$result = $service->importFromJson(string $json): array;
// Returns: ['imported' => 50, 'errors' => []]

// Import from legacy INI format
$result = $service->importFromIni(string $content, string $type): array;
// $type: 'allow' or 'acl'
```

### FeatureService

Manages feature flags for the plugin.

```php
use TinyAuthBackend\Service\FeatureService;

$service = new FeatureService();
```

#### Methods

```php
// Check if a feature is enabled
$enabled = $service->isEnabled(string $feature): bool;

// Available features:
// - 'resources' - Resource-based permissions
// - 'scopes' - Conditional permissions
// - 'hierarchy' - Role hierarchy
// - 'sync' - Auto-sync controllers
```

### RoleSourceService

Provides role data from configuration or database.

```php
use TinyAuthBackend\Service\RoleSourceService;

$service = new RoleSourceService();
```

#### Methods

```php
// Get all roles (merged from config and DB)
$roles = $service->getRoles(): array;

// Get role by ID
$role = $service->getRole(int $id): ?array;

// Get role by name
$role = $service->getRoleByName(string $name): ?array;
```

### Service Registration

Services can be registered in your application's container:

```php
// In src/Application.php
public function services(ContainerInterface $container): void
{
    $container->add(TinyAuthService::class);
    $container->add(HierarchyService::class);
    // etc.
}
```

Then inject via constructor:

```php
class ArticlePolicy
{
    public function __construct(
        protected TinyAuthService $tinyAuth
    ) {}
}
```

### Caching

Services automatically use caching. To clear:

```php
use Cake\Cache\Cache;

// Clear all TinyAuth caches
Cache::delete('TinyAuth.allow');
Cache::delete('TinyAuth.acl');
Cache::delete('TinyAuth.roles');
Cache::delete('TinyAuth.hierarchy');
```
