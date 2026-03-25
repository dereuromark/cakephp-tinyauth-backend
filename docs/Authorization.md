## Authorization Integration

Use this mode if you want TinyAuthBackend to drive **entity/resource authorization** with CakePHP's `cakephp/authorization` plugin.

### What This Package Provides

- `TinyAuthPolicy` for entity-level checks
- `TinyAuthService` for direct permission lookups
- backend UIs for resources, scopes, roles, and rule management

### What It Does Not Provide Automatically

`TinyAuthPolicy` is **not** a request policy for controller/action routing.

Use one of these approaches:

- keep TinyAuth `allow`/`acl` for controller/action access and use Authorization for entities
- write your own request policy if you want request authorization fully inside CakePHP Authorization

### Setup

Install Authorization:

```bash
composer require cakephp/authorization
```

Load the plugin and middleware in your app as usual.

### Mapping `TinyAuthPolicy`

Map it as the default policy for entities you want to control from the backend:

```php
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Policy\OrmResolver;
use Psr\Http\Message\ServerRequestInterface;
use TinyAuthBackend\Policy\TinyAuthPolicy;

class Application extends BaseApplication implements AuthorizationServiceProviderInterface
{
    public function getAuthorizationService(
        ServerRequestInterface $request
    ): AuthorizationServiceInterface {
        $resolver = new OrmResolver(TinyAuthPolicy::class);

        return new AuthorizationService($resolver);
    }
}
```

### In Controllers

```php
$article = $this->Articles->get($id);
$this->Authorization->authorize($article, 'edit');
```

### In Views

```php
<?php if ($this->Identity->can('edit', $article)): ?>
    <?= $this->Html->link('Edit', ['action' => 'edit', $article->id]) ?>
<?php endif; ?>
```

### Super Admin Bypass

`TinyAuthPolicy` supports a configurable bypass role:

```php
'TinyAuthBackend' => [
    'superAdminRole' => 'root',
],
```

For backward compatibility it also reads `TinyAuth.superAdminRole`.

If no config is set, the built-in fallback aliases are:

- `admin`
- `superadmin`

### Using `TinyAuthService` Directly

```php
use TinyAuthBackend\Service\TinyAuthService;

$service = new TinyAuthService();

$canEdit = $service->canAccessResource($user, $article, 'edit');
$canCreate = $service->canPerformAbility($user, 'Article', 'create');
$scope = $service->getScopeCondition($service->getUserRoles($user), 'Article', 'view', $user);
```

### Recommended Split

A practical setup is:

1. TinyAuth `allow` and `acl` handle controller/action entry.
2. Authorization policies handle entity-level checks.
3. TinyAuthBackend stores both in one admin backend.
