## Authorization Integration

TinyAuthBackend integrates with `cakephp/authorization` for comprehensive permission management.

### Overview

The plugin provides `TinyAuthPolicy` which checks permissions against the database-backed ACL.

### Setup

#### 1. Install cakephp/authorization

```bash
composer require cakephp/authorization
```

#### 2. Load the Plugin

```php
// In src/Application.php bootstrap()
$this->addPlugin('Authorization');
```

#### 3. Add Middleware

```php
// In src/Application.php middleware()
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue
        // ... other middleware
        ->add(new AuthenticationMiddleware($this))
        ->add(new AuthorizationMiddleware($this));

    return $middlewareQueue;
}
```

#### 4. Configure Authorization Service

```php
// In src/Application.php
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Policy\MapResolver;
use Psr\Http\Message\ServerRequestInterface;
use TinyAuthBackend\Policy\TinyAuthPolicy;

class Application extends BaseApplication implements
    AuthorizationServiceProviderInterface
{
    public function getAuthorizationService(
        ServerRequestInterface $request
    ): AuthorizationServiceInterface {
        $resolver = new MapResolver();

        // Use TinyAuthPolicy for request authorization
        $resolver->map(ServerRequest::class, TinyAuthPolicy::class);

        return new AuthorizationService($resolver);
    }
}
```

### Using TinyAuthPolicy

The `TinyAuthPolicy` automatically checks ACL permissions:

```php
// In a controller
public function edit($id)
{
    // This checks if the current user has 'edit' permission
    // for this controller via the TinyAuthPolicy
    $this->Authorization->authorize($this->request);

    // ... rest of action
}
```

### Custom Policies

For entity-level authorization, create custom policies:

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

    /**
     * Check if user can view an article
     */
    public function canView(IdentityInterface $user, Article $article): bool
    {
        // Use resource-based permissions
        return $this->tinyAuth->canAccessResource($user, $article, 'view');
    }

    /**
     * Check if user can edit an article
     */
    public function canEdit(IdentityInterface $user, Article $article): bool
    {
        return $this->tinyAuth->canAccessResource($user, $article, 'edit');
    }

    /**
     * Check if user can delete an article
     */
    public function canDelete(IdentityInterface $user, Article $article): bool
    {
        return $this->tinyAuth->canAccessResource($user, $article, 'delete');
    }
}
```

Register the policy:

```php
// In Application::getAuthorizationService()
$resolver->map(Article::class, ArticlePolicy::class);
```

### Controller Authorization

Use authorization in controllers:

```php
// Authorize the request (checks TinyAuthPolicy)
$this->Authorization->authorize($this->request);

// Authorize an entity
$article = $this->Articles->get($id);
$this->Authorization->authorize($article, 'edit');

// Skip authorization for an action
$this->Authorization->skipAuthorization();
```

### Combining ACL and Resource Permissions

You can use both controller-level ACL and entity-level resource permissions:

```php
public function edit($id)
{
    // 1. Check if user can access this controller/action (ACL)
    $this->Authorization->authorize($this->request);

    // 2. Check if user can edit this specific article (Resource)
    $article = $this->Articles->get($id);
    $this->Authorization->authorize($article, 'edit');

    // ... edit logic
}
```

### Middleware Configuration

The authorization middleware can be configured:

```php
$middlewareQueue->add(new AuthorizationMiddleware($this, [
    // Redirect unauthorized users
    'unauthorizedHandler' => [
        'className' => 'Authorization.Redirect',
        'url' => '/users/login',
        'queryParam' => 'redirect',
    ],
]));
```

### Component Configuration

Load the Authorization component in your AppController:

```php
// In AppController::initialize()
public function initialize(): void
{
    parent::initialize();

    $this->loadComponent('Authorization.Authorization');
}
```

### Checking Permissions in Views

```php
// In a template
<?php if ($this->Identity->can('edit', $article)): ?>
    <?= $this->Html->link('Edit', ['action' => 'edit', $article->id]) ?>
<?php endif; ?>
```

### SuperAdmin Bypass

Configure a superadmin role that bypasses all checks:

```php
// In config/app.php
'TinyAuth' => [
    'superAdminRole' => 'superadmin',  // Role name
    // or
    'superAdminRole' => 99,  // Role ID
],
```

### Best Practices

1. **Use request authorization** for controller/action access
2. **Use entity authorization** for entity-level access
3. **Keep policies focused** - one policy per entity
4. **Use TinyAuthService** for complex permission checks
5. **Cache permissions** - they're cached automatically
6. **Test authorization** - write tests for your policies
