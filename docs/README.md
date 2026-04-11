## TinyAuth Backend

TinyAuthBackend gives you a database-backed admin UI for permissions and roles.

The package supports four practical usage strategies, arranged as a ladder you can climb as your project's needs grow:

1. **Adapter-only TinyAuth**: keep classic TinyAuth `allow` + `acl` behavior, but store those rules in the database instead of INI files.
2. **Full TinyAuthBackend**: use the admin UI plus resources, scopes, role hierarchy, and `TinyAuthPolicy`/`TinyAuthService` for entity authorization.
3. **Backend UI + native CakePHP auth**: keep CakePHP Authentication/Authorization as your runtime layer and use this package mainly as a DB-backed permission management UI.
4. **External role source**: drive role aliases from a JWT claim, an LDAP group, an SSO gateway, or any other source outside the plugin, while keeping ACL/resource assignments in the backend.

### Admin URL

The plugin mounts under:

```text
/admin/auth
```

Common sections:

| URL | Purpose |
|-----|---------|
| `/admin/auth/allow` | Public action management |
| `/admin/auth/acl` | Controller/action ACL matrix |
| `/admin/auth/roles` | Roles and hierarchy |
| `/admin/auth/resources` | Resource abilities |
| `/admin/auth/scopes` | Field-based scopes |
| `/admin/auth/sync/controllers` | Scan controllers/actions into DB |
| `/admin/auth/sync/resources` | Scan entities/resources into DB |

### Admin Access

The plugin expects the host app to decide who may manage `/admin/auth`.

Default behavior:

- in `debug = true`, the admin UI is accessible by default for local development
- in `debug = false`, the default `TinyAuthBackend.editorCheck` denies access until you replace it with your own callable

Example:

```php
use Cake\Core\Configure;
use Psr\Http\Message\ServerRequestInterface;

Configure::write(
    'TinyAuthBackend.editorCheck',
    function (mixed $identity, ServerRequestInterface $request): bool {
        return $identity !== null
            && (int)($identity->get('role_id') ?? 0) === 3;
    },
);
```

Do not rely on the debug-mode default in production.

### Which Guide Should I Read?

- [Strategies Overview](Strategies/README.md)
- [Adapter-Only Strategy](Strategies/AdapterOnly.md)
- [Full TinyAuthBackend Strategy](Strategies/FullBackend.md)
- [Native CakePHP Auth Strategy](Strategies/NativeAuth.md)
- [External Role Source Strategy](Strategies/ExternalRoles.md)
- [Authorization Integration](Authorization.md)
- [Resource Permissions](Resources.md)
- [Roles and Hierarchy](Roles.md)
- [Scopes](Scopes.md)
- [Services API](Services.md)

### Feature Flags

You can force-enable or disable parts of the backend with `TinyAuthBackend.features`:

```php
'TinyAuthBackend' => [
    'features' => [
        'allow' => true,
        'acl' => true,
        'roles' => true,
        'resources' => false,
        'scopes' => false,
    ],
],
```

This is useful when you only want the classic TinyAuth adapter functionality exposed in the UI.
