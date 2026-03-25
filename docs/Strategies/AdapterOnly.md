## Adapter-Only Strategy

Use this mode if you want to keep the classic TinyAuth runtime behavior and only replace the old INI files with database-backed adapters.

This is the closest match to "what `allow` and `acl` already did before, just from DB".

### What You Keep

- TinyAuth remains your runtime authorization layer
- `DbAllowAdapter` replaces `allow.ini`
- `DbAclAdapter` replaces `acl.ini`
- the backend UI becomes the place where those rules are edited

### What You Do Not Need

- `TinyAuthPolicy`
- CakePHP Authorization integration
- resources/scopes if you do not want entity-level permissions

### Minimal Config

```php
'TinyAuth' => [
    'allowAdapter' => \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
    'aclAdapter' => \TinyAuthBackend\Auth\AclAdapter\DbAclAdapter::class,
],
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

### Recommended Flow

1. Run the plugin migrations.
2. Sync controllers/actions into the backend.
3. Import your old INI files once if you are migrating existing rules.
4. Point TinyAuth to the DB adapters.
5. Manage `allow` and `acl` from `/admin/auth`.

### Import Existing INI Files

```bash
bin/cake tiny_auth_backend import allow
bin/cake tiny_auth_backend import acl
```

Or initialize backend access for an admin role:

```bash
bin/cake tiny_auth_backend init admin
```

### What Tables Matter In This Mode

- `tinyauth_roles`
- `tinyauth_controllers`
- `tinyauth_actions`
- `tinyauth_acl_permissions`

You can ignore:

- `tinyauth_resources`
- `tinyauth_resource_abilities`
- `tinyauth_scopes`
- `tinyauth_resource_acl`
