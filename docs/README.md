## TinyAuth Backend

A database-backed administration interface for TinyAuth with a modern, responsive UI.

### Features

- **Normalized Database Schema**: 8 tables for roles, controllers, actions, permissions, resources, and scopes
- **Tree+Matrix UI**: Controller tree navigation with permission matrix view
- **Role Hierarchy**: Drag-and-drop role ordering with parent/child relationships
- **HTMX+Alpine.js Frontend**: Reactive UI with minimal JavaScript
- **Standalone Layout**: Self-contained with Tailwind CSS (CDN), dark/light theme support
- **Authorization Integration**: TinyAuthPolicy for cakephp/authorization
- **DB Adapters**: DbAllowAdapter and DbAclAdapter for TinyAuth integration
- **Controller Sync**: Auto-discovery of controllers and actions from the application

### Enable the Plugin

```bash
bin/cake plugin load TinyAuthBackend
```

### Run Migrations

Create the required database tables:

```bash
bin/cake migrations migrate -p TinyAuthBackend
```

This creates the following tables:

| Table | Purpose |
|-------|---------|
| `tinyauth_roles` | User roles with hierarchy support |
| `tinyauth_controllers` | Discovered controllers (plugin/prefix/name) |
| `tinyauth_actions` | Controller actions with public flag |
| `tinyauth_acl_permissions` | Role-to-action permission mappings |
| `tinyauth_resources` | Entity resources for resource-based auth |
| `tinyauth_resource_abilities` | Resource abilities (view, edit, delete, etc.) |
| `tinyauth_scopes` | Reusable permission conditions |
| `tinyauth_resource_acl` | Resource-to-role permission mappings |

### Enable the Adapters

Configure TinyAuth to use the database adapters:

```php
// In config/app.php or config/app_local.php
'TinyAuth' => [
    'allowAdapter' => \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
    'aclAdapter' => \TinyAuthBackend\Auth\AclAdapter\DbAclAdapter::class,
],
```

### Initialize Roles

Add your application's roles to the database:

```bash
bin/cake tiny_auth_backend init {admin-role-name}
```

Or configure roles in `config/roles.php`:

```php
<?php
return [
    'Roles' => [
        'user' => 1,
        'moderator' => 2,
        'admin' => 3,
    ],
];
```

### Admin Panel

Navigate to the admin panel:

```
/admin/tinyauth/admin/
```

#### Available Sections

| URL | Purpose |
|-----|---------|
| `/admin/tinyauth/admin/acl` | ACL permission matrix (main view) |
| `/admin/tinyauth/admin/allow` | Public action management |
| `/admin/tinyauth/admin/roles` | Role management with hierarchy |
| `/admin/tinyauth/admin/resources` | Resource-based permissions |
| `/admin/tinyauth/admin/scopes` | Reusable permission scopes |
| `/admin/tinyauth/admin/sync` | Sync controllers from application |

### Import from INI Files

If migrating from file-based TinyAuth:

```bash
# Import allow rules
bin/cake tiny_auth_backend import allow

# Import ACL rules
bin/cake tiny_auth_backend import acl

# Import from specific file
bin/cake tiny_auth_backend import acl /path/to/file.ini
```

### Detailed Documentation

- [ACL Management](Acl.md) - Permission matrix and role-based access
- [Allow Management](Allow.md) - Public action configuration
- [Roles](Roles.md) - Role hierarchy and management
- [Resources](Resources.md) - Entity-level permissions
- [Scopes](Scopes.md) - Conditional permission rules
- [Services](Services.md) - Programmatic API
- [Authorization](Authorization.md) - cakephp/authorization integration

### Caching

Permissions are cached automatically. Clear the cache after manual database changes:

```php
use Cake\Cache\Cache;

Cache::delete('TinyAuth.allow');
Cache::delete('TinyAuth.acl');
```

### Custom Theme

You can override templates by creating them in your app:

```
templates/plugin/TinyAuthBackend/Admin/Acl/index.php
templates/plugin/TinyAuthBackend/layout/tinyauth.php
```
