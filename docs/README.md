## TinyAuth Backend

TinyAuthBackend gives you a database-backed admin UI for permissions and roles.

The package supports three practical usage modes:

1. **Adapter-only TinyAuth**: keep classic TinyAuth `allow` + `acl` behavior, but store those rules in the database instead of INI files.
2. **Full TinyAuthBackend**: use the admin UI plus resources, scopes, role hierarchy, and `TinyAuthPolicy`/`TinyAuthService` for entity authorization.
3. **Backend UI + native CakePHP auth**: keep CakePHP Authentication/Authorization as your runtime layer and use this package mainly as a DB-backed permission management UI.

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

### Which Guide Should I Read?

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> af88ee6 (Finish role source support and replace placeholder tests)
- [Strategies Overview](Strategies/README.md)
- [Adapter-Only Strategy](Strategies/AdapterOnly.md)
- [Full TinyAuthBackend Strategy](Strategies/FullBackend.md)
- [Native CakePHP Auth Strategy](Strategies/NativeAuth.md)
- [External Role Source Strategy](Strategies/ExternalRoles.md)
<<<<<<< HEAD
=======
- [Adapter-Only Strategy](AdapterOnly.md)
>>>>>>> 11f8781 (Fix auth hierarchy semantics and document usage modes)
=======
>>>>>>> af88ee6 (Finish role source support and replace placeholder tests)
- [Authorization Integration](Authorization.md)
- [Resource Permissions](Resources.md)
- [Roles and Hierarchy](Roles.md)
- [Scopes](Scopes.md)
- [Services API](Services.md)
<<<<<<< HEAD
<<<<<<< HEAD
=======
- [Native CakePHP Auth Strategy](NativeAuth.md)
>>>>>>> 11f8781 (Fix auth hierarchy semantics and document usage modes)
=======
>>>>>>> af88ee6 (Finish role source support and replace placeholder tests)

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
