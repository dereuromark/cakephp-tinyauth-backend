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

Use the backend's resource tables and `TinyAuthService` inside your policies:

```php
use App\Model\Entity\Article;
use Cake\Datasource\EntityInterface;
use TinyAuthBackend\Policy\TinyAuthPolicy;

class ArticlePolicy extends TinyAuthPolicy
{
    public function canEdit(EntityInterface $user, Article $article): bool
    {
        return $this->can($user, 'edit', $article);
    }
}
```

Or call the service directly:

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

### Good Fit

Choose this mode if:

- your app already uses CakePHP Authorization policies
- you want a permission admin UI but not TinyAuth's runtime model
- you mainly care about entity/resource authorization
