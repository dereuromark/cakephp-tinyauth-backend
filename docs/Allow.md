## Allow Management (Public Actions)

The Allow page lets you mark actions as publicly accessible (no authentication required).

### Overview

Public actions bypass authentication entirely - anyone can access them without logging in.

Common examples:
- `Pages::display` - Static pages
- `Users::login` - Login page
- `Users::register` - Registration page
- API endpoints that don't require auth

### Interface

The Allow page displays all controllers with their actions:
- **Toggle Switch**: Enable/disable public access per action
- **Bulk Actions**: Make all actions in a controller public/protected

### Setting Public Actions

1. Find the controller in the list
2. Toggle the switch next to the action
3. Green = Public, Gray = Protected

### Bulk Operations

For each controller, you can:
- **Make All Public**: Set all actions to public
- **Make All Protected**: Remove public access from all actions

### Filter Options

Filter the view by:
- **All**: Show all actions
- **Public**: Show only public actions
- **Protected**: Show only protected actions

### Database Schema

Public actions are stored in the `tinyauth_actions` table:

```sql
CREATE TABLE tinyauth_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controller_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,  -- This field
    created DATETIME,
    modified DATETIME
);
```

### Programmatic Access

```php
use TinyAuthBackend\Service\TinyAuthService;

$service = new TinyAuthService();

// Check if action is public
$isPublic = $service->isPublicAction('Pages', 'display');

// Check with plugin/prefix
$isPublic = $service->isPublicAction('Articles', 'view', [
    'plugin' => 'Blog',
]);
```

### Making Actions Public Programmatically

```php
$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');

// Find the action
$action = $actionsTable->find()
    ->matching('TinyauthControllers', function ($q) {
        return $q->where([
            'TinyauthControllers.name' => 'Pages',
            'TinyauthControllers.plugin IS' => null,
            'TinyauthControllers.prefix IS' => null,
        ]);
    })
    ->where(['Actions.name' => 'display'])
    ->first();

// Make it public
$action->is_public = true;
$actionsTable->save($action);

// Clear cache
Cache::delete('TinyAuth.allow');
```

### Integration with TinyAuth

The `DbAllowAdapter` reads from the normalized tables:

```php
// In config/app.php
'TinyAuth' => [
    'allowAdapter' => \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
],
```

The adapter returns data in TinyAuth's expected format:

```php
// Returns array like:
[
    'Pages' => ['display', 'home'],
    'Users' => ['login', 'register'],
    'Blog.Articles' => ['index', 'view'],
]
```

### Security Considerations

- Be cautious when making actions public
- Review public actions regularly
- Use the filter to audit which actions are public
- Consider using role-based access instead of public access when possible
