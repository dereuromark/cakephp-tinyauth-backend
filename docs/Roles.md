## Role Management

The Roles page manages user roles with support for hierarchy and ordering.

### Overview

Roles define permission levels in your application. The hierarchy determines which permissions are inherited.

### Role Properties

| Field | Description |
|-------|-------------|
| `name` | Internal role identifier (e.g., `admin`) |
| `alias` | Display name (e.g., `Administrator`) |
| `sort_order` | Hierarchy level (higher = more privileges) |
| `parent_id` | Optional parent role for inheritance |

### Role Hierarchy

Roles can form a hierarchy where higher roles inherit permissions from lower roles:

```
admin (sort_order: 3)
  └── moderator (sort_order: 2)
        └── user (sort_order: 1)
```

In this example:
- Admin has all permissions of moderator and user
- Moderator has all permissions of user
- User has only their own permissions

### Managing Roles

#### Add a Role

1. Click "Add Role"
2. Enter name, alias, and sort order
3. Optionally select a parent role
4. Save

#### Edit a Role

1. Click the edit icon next to the role
2. Modify fields
3. Save

#### Reorder Roles

Drag and drop roles to change their hierarchy order. Higher positions = higher privileges.

#### Delete a Role

1. Click the delete icon
2. Confirm deletion

**Warning**: Deleting a role removes all associated permissions.

### Database Schema

```sql
CREATE TABLE tinyauth_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    alias VARCHAR(100),
    sort_order INT DEFAULT 0,
    parent_id INT NULL,
    created DATETIME,
    modified DATETIME,
    FOREIGN KEY (parent_id) REFERENCES tinyauth_roles(id)
);
```

### Configuration Integration

Roles should match your application's role configuration:

```php
// config/roles.php
return [
    'Roles' => [
        'user' => 1,       // Maps to tinyauth_roles where name='user'
        'moderator' => 2,  // Maps to tinyauth_roles where name='moderator'
        'admin' => 3,      // Maps to tinyauth_roles where name='admin'
    ],
];
```

The numeric values are role IDs that match the database.

### Programmatic Access

```php
use TinyAuthBackend\Service\HierarchyService;

$service = new HierarchyService();

// Get all roles a user can access (including inherited)
$effectiveRoles = $service->getEffectiveRoles($userRoleId);

// Get role hierarchy
$hierarchy = $service->getHierarchy();

// Check if role is above another
$isHigher = $service->isRoleAbove($roleA, $roleB);
```

### Syncing Roles

To sync roles from your configuration:

```php
$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
$configRoles = Configure::read('Roles');

foreach ($configRoles as $name => $id) {
    $existing = $rolesTable->find()
        ->where(['name' => $name])
        ->first();

    if (!$existing) {
        $role = $rolesTable->newEntity([
            'name' => $name,
            'alias' => ucfirst($name),
            'sort_order' => $id,
        ]);
        $rolesTable->save($role);
    }
}
```

### Best Practices

1. **Use descriptive names**: `content_editor` instead of `role2`
2. **Set meaningful aliases**: Display names for the UI
3. **Plan hierarchy carefully**: Higher roles inherit lower role permissions
4. **Document role purposes**: Keep track of what each role is for
5. **Limit role count**: Too many roles become hard to manage
