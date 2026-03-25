## Resource-Based Permissions

Resources enable entity-level permissions (e.g., "user can edit their own posts").

### Overview

While ACL manages controller/action permissions, Resources manage entity-level access:

- **ACL**: "Can user access the Articles edit action?"
- **Resources**: "Can user edit THIS specific article?"

### Concepts

| Term | Description |
|------|-------------|
| Resource | An entity type (e.g., Article, Comment) |
| Ability | An action on a resource (e.g., view, edit, delete) |
| Scope | A condition that limits access (e.g., "own" items only) |

### Setting Up Resources

#### 1. Add a Resource

1. Go to `/admin/tinyauth/admin/resources`
2. Click "Add Resource"
3. Enter:
   - **Name**: Display name (e.g., "Article")
   - **Entity Class**: Full class name (e.g., `App\Model\Entity\Article`)
   - **Table Name**: Database table (e.g., `articles`)

#### 2. Add Abilities

For each resource, define what users can do:

1. Click "Manage Abilities" on the resource
2. Add abilities like:
   - `view` - Read access
   - `edit` - Modify access
   - `delete` - Remove access
   - `publish` - Custom ability

#### 3. Assign Permissions

On the Resources page, set permissions per role:

| Role | Ability | Scope |
|------|---------|-------|
| user | view | (none) - can view all |
| user | edit | own - can edit own only |
| moderator | edit | (none) - can edit all |
| admin | delete | (none) - can delete all |

### Database Schema

```sql
-- Resources
CREATE TABLE tinyauth_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    entity_class VARCHAR(200) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    created DATETIME,
    modified DATETIME
);

-- Abilities per resource
CREATE TABLE tinyauth_resource_abilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(200),
    created DATETIME,
    modified DATETIME,
    FOREIGN KEY (resource_id) REFERENCES tinyauth_resources(id)
);

-- Resource ACL permissions
CREATE TABLE tinyauth_resource_acl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ability_id INT NOT NULL,
    role_id INT NOT NULL,
    scope_id INT NULL,
    type ENUM('allow', 'deny') NOT NULL,
    created DATETIME,
    modified DATETIME,
    FOREIGN KEY (ability_id) REFERENCES tinyauth_resource_abilities(id),
    FOREIGN KEY (role_id) REFERENCES tinyauth_roles(id),
    FOREIGN KEY (scope_id) REFERENCES tinyauth_scopes(id)
);
```

### Using in Policies

```php
// src/Policy/ArticlePolicy.php
namespace App\Policy;

use App\Model\Entity\Article;
use Authorization\IdentityInterface;
use TinyAuthBackend\Service\TinyAuthService;

class ArticlePolicy
{
    protected TinyAuthService $tinyAuth;

    public function __construct()
    {
        $this->tinyAuth = new TinyAuthService();
    }

    public function canView(IdentityInterface $user, Article $article): bool
    {
        return $this->tinyAuth->canAccessResource($user, $article, 'view');
    }

    public function canEdit(IdentityInterface $user, Article $article): bool
    {
        return $this->tinyAuth->canAccessResource($user, $article, 'edit');
    }

    public function canDelete(IdentityInterface $user, Article $article): bool
    {
        return $this->tinyAuth->canAccessResource($user, $article, 'delete');
    }
}
```

### Programmatic API

```php
use TinyAuthBackend\Service\TinyAuthService;

$service = new TinyAuthService();

// Check resource access
$canEdit = $service->canAccessResource($user, $article, 'edit');

// Get all abilities user has for a resource
$abilities = $service->getResourceAbilities($user, $article);
// Returns: ['view', 'edit']

// Check ability without entity (type-level)
$canCreate = $service->canPerformAbility($user, 'Article', 'create');
```

### Common Patterns

#### Owner-Only Access

```php
// Scope: own
// entity_field: user_id
// user_field: id

// This allows users to edit only articles where:
// $article->user_id === $user->id
```

#### Team-Based Access

```php
// Scope: team
// entity_field: team_id
// user_field: team_id

// This allows users to edit articles in their team:
// $article->team_id === $user->team_id
```

#### Status-Based Access

For more complex conditions, use custom scopes or policy logic:

```php
public function canPublish(IdentityInterface $user, Article $article): bool
{
    // Must have ability AND article must be in draft status
    $hasAbility = $this->tinyAuth->canAccessResource($user, $article, 'publish');

    return $hasAbility && $article->status === 'draft';
}
```
