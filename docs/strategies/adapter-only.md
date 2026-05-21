# Adapter-Only Strategy

Use this mode if you want to keep the classic TinyAuth runtime behavior and only
replace the old INI files with database-backed adapters.

This is the closest match to "what `allow` and `acl` already did before, just
from the database".

## What you keep

- TinyAuth remains your runtime authorization layer.
- `DbAllowAdapter` replaces `allow.ini`.
- `DbAclAdapter` replaces `acl.ini`.
- The backend UI becomes the place where those rules are edited.

## What you do not need

- `TinyAuthPolicy`
- CakePHP Authorization integration
- Resources / scopes, if you do not want entity-level permissions

## Minimal config

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

## Recommended flow

1. Run the plugin migrations.
2. Sync controllers/actions into the backend.
3. Import your old INI files once if you are migrating existing rules.
4. Point TinyAuth to the DB adapters.
5. Manage `allow` and `acl` from `/admin/auth`.

## Sync controllers and actions

Before the backend can edit rules, it needs to know which controllers and
actions exist. Run the sync command once after install (and again after adding
new controllers):

```sh
bin/cake tiny_auth_backend sync
```

This is the CLI equivalent of clicking **Sync** in `/admin/auth/sync`. It walks
your application (and plugins), writes discovered rows into
`tinyauth_controllers` / `tinyauth_actions`, and is idempotent — re-running it
never clobbers existing grants.

You can scope the sync to controllers or resources only:

```sh
bin/cake tiny_auth_backend sync controllers
bin/cake tiny_auth_backend sync resources
```

## Import existing INI files

```sh
bin/cake tiny_auth_backend import allow
bin/cake tiny_auth_backend import acl
```

Or initialize backend access for an admin role:

```sh
bin/cake tiny_auth_backend init admin
```

## What tables matter in this mode

You use:

- `tinyauth_roles`
- `tinyauth_controllers`
- `tinyauth_actions`
- `tinyauth_acl_permissions`

You can ignore:

- `tinyauth_resources`
- `tinyauth_resource_abilities`
- `tinyauth_scopes`
- `tinyauth_resource_acl`
