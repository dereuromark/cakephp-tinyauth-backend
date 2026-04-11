## Native CakePHP Auth Strategy

Use this mode if you want CakePHP Authentication/Authorization to stay in charge at runtime and you do **not** want TinyAuth to handle request authorization.

### Important Constraint

This package still depends on `dereuromark/cakephp-tinyauth` at install time.

That does **not** mean you must use TinyAuth as your runtime auth layer.

You can instead use TinyAuthBackend mainly for:

- role management
- resource/ability editing
- scope management
- permission administration UI

and consume that data from your own CakePHP Authorization policies/services.

### Practical Split

- Authentication: `cakephp/authentication`
- Authorization: `cakephp/authorization`
- Permission storage/admin UI: `TinyAuthBackend`

### Recommended Usage

The simplest path is to let `TinyAuthPolicy` handle every entity you want the backend to govern, via the plugin-provided `TinyAuthResolver`:

```php
// Application::getAuthorizationService()
use Authorization\AuthorizationService;
use TinyAuthBackend\Policy\TinyAuthResolver;

$resolver = new TinyAuthResolver([
    \App\Model\Entity\Article::class,
    \App\Model\Entity\Project::class,
]);

return new AuthorizationService($resolver);
```

See [Authorization Integration](../Authorization.md) for the full wiring guide.

If you need custom logic on top of the base policy, extend `TinyAuthPolicy` and override the specific hook — the parent signature matches CakePHP Authorization's calling convention:

```php
use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;
use TinyAuthBackend\Policy\TinyAuthPolicy;

class ArticlePolicy extends TinyAuthPolicy
{
    public function canEdit(?IdentityInterface $identity, EntityInterface $entity): bool
    {
        // Custom gate goes here; fall through to the TinyAuth rules.
        return parent::canEdit($identity, $entity);
    }
}
```

Or call the service directly from your own policy:

```php
use TinyAuthBackend\Service\TinyAuthService;

$service = new TinyAuthService();
$allowed = $service->canAccessResource($user, $article, 'edit');
```

### What To Avoid In This Mode

If you are not using TinyAuth at runtime, do not wire up:

- `DbAllowAdapter`
- `DbAclAdapter`

Those adapters are specifically for TinyAuth's controller/action flow.

### Suggested Feature Set

```php
'TinyAuthBackend' => [
    'features' => [
        'allow' => false,
        'acl' => false,
        'roles' => true,
        'resources' => true,
        'scopes' => true,
    ],
],
```
