## ACL Management

The ACL (Access Control List) page provides a matrix view for managing role-based permissions.

### Overview

The ACL interface displays:
- **Left Panel**: Controller tree grouped by plugin and prefix
- **Main Panel**: Permission matrix for the selected controller

### Permission Matrix

The matrix shows:
- **Rows**: Actions for the selected controller
- **Columns**: Available roles (ordered by hierarchy)
- **Cells**: Permission state

### Permission States

| State | Display | Meaning |
|-------|---------|---------|
| None | Gray (empty) | No explicit permission - access denied |
| Allow | Green checkmark | Access granted for this role |
| Deny | Red X | Access explicitly denied (overrides inherited allow) |

### Setting Permissions

Click on any cell to cycle through permission states:
1. None → Allow
2. Allow → Deny
3. Deny → None

Changes are saved immediately via HTMX.

### Permission Logic

1. If a role has an explicit `deny` for an action, access is denied
2. If a role has an explicit `allow` for an action, access is granted
3. If no explicit permission exists, access is denied by default

### Role Hierarchy

Permissions can be inherited through role hierarchy:

```
admin (level 3)
  └── moderator (level 2)
        └── user (level 1)
```

When checking permissions:
- Admin inherits all permissions from moderator and user
- Moderator inherits all permissions from user
- Higher roles automatically have lower role permissions

### Search

Use the search box to quickly find:
- Controllers by name
- Actions by name
- Roles by name or alias

### Database Schema

```sql
-- Controllers table
CREATE TABLE tinyauth_controllers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plugin VARCHAR(100) NULL,
    prefix VARCHAR(100) NULL,
    name VARCHAR(100) NOT NULL,
    created DATETIME,
    modified DATETIME,
    UNIQUE KEY (plugin, prefix, name)
);

-- Actions table
CREATE TABLE tinyauth_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controller_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created DATETIME,
    modified DATETIME,
    UNIQUE KEY (controller_id, name),
    FOREIGN KEY (controller_id) REFERENCES tinyauth_controllers(id)
);

-- ACL Permissions table
CREATE TABLE tinyauth_acl_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_id INT NOT NULL,
    role_id INT NOT NULL,
    type ENUM('allow', 'deny') NOT NULL,
    created DATETIME,
    modified DATETIME,
    UNIQUE KEY (action_id, role_id),
    FOREIGN KEY (action_id) REFERENCES tinyauth_actions(id),
    FOREIGN KEY (role_id) REFERENCES tinyauth_roles(id)
);
```

### Programmatic Access

```php
use TinyAuthBackend\Service\TinyAuthService;

$service = new TinyAuthService();

// Check if user has access to an action
$hasAccess = $service->hasAccess($user, 'Articles', 'edit');

// Check with plugin/prefix
$hasAccess = $service->hasAccess($user, 'Articles', 'edit', [
    'plugin' => 'Blog',
    'prefix' => 'Admin',
]);
```

### Bulk Operations

To set permissions for all actions in a controller:

```php
$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');
$permissionsTable = $this->fetchTable('TinyAuthBackend.AclPermissions');

$actions = $actionsTable->find()
    ->where(['controller_id' => $controllerId])
    ->all();

foreach ($actions as $action) {
    $permission = $permissionsTable->newEntity([
        'action_id' => $action->id,
        'role_id' => $roleId,
        'type' => 'allow',
    ]);
    $permissionsTable->save($permission);
}

// Clear cache
Cache::delete('TinyAuth.acl');
```
