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

The plugin ships a dedicated `TinyAuthResolver` that maps any known entity, table, or `SelectQuery` to `TinyAuthPolicy` — without you having to write a thin `App\Policy\FooPolicy` wrapper per resource:

```php
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use TinyAuthBackend\Policy\TinyAuthResolver;

class Application extends BaseApplication implements AuthorizationServiceProviderInterface
{
    public function getAuthorizationService(
        ServerRequestInterface $request
    ): AuthorizationServiceInterface {
        $resolver = new TinyAuthResolver([
            \App\Model\Entity\Article::class,
            \App\Model\Entity\Project::class,
        ]);

        return new AuthorizationService($resolver);
    }
}
```

The constructor takes an allowlist of entity/table classes. Leave it empty to put every resource under TinyAuth control (match-all mode):

```php
$resolver = new TinyAuthResolver(); // governs all resources
```

`TinyAuthResolver` transparently unwraps `SelectQuery` instances to their repository, so the same resolver works for both `$this->Authorization->authorize($article, 'edit')` and `$this->Authorization->applyScope($query)`. Cake's built-in `MapResolver` only handles the former, and `OrmResolver` requires convention-based `App\Policy\*` classes — `TinyAuthResolver` avoids both pitfalls.

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

### Identity Without `cakephp/authentication`

Most apps load `cakephp/authentication`, which hangs an `IdentityInterface` on the request automatically. If your app resolves users another way — a session payload, a JWT claim, an upstream SSO gateway — the plugin ships `EntityIdentity`, a small wrapper that turns any Cake entity into a valid `Authorization\IdentityInterface` without pulling in the authentication plugin:

```php
use TinyAuthBackend\Identity\EntityIdentity;

$user = $this->Users->get($userId);
$identity = new EntityIdentity($user, $authorizationService); // service is optional

$request = $request->withAttribute('identity', $identity);
```

`EntityIdentity` forwards array access and magic property reads to the underlying entity, so policies and templates can treat it interchangeably with the wrapped user entity. When constructed without an authorization service, `can()` returns `false` and `applyScope()` returns the resource unchanged — the right behavior for strategies that gate by role only and never call into the Authorization service (see the AdapterOnly usage pattern).

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
