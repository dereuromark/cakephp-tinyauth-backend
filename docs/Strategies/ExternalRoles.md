## External Role Source Strategy

Use this mode if role aliases and IDs already live outside TinyAuthBackend, for example in:

- app config
- a custom role service
- another table managed by your app

### Config

```php
'TinyAuthBackend' => [
    'roleSource' => 'Roles',
],
```

Or:

```php
'TinyAuthBackend' => [
    'roleSource' => [
        'user' => 1,
        'moderator' => 2,
        'admin' => 3,
    ],
],
```

### How It Works

- `RoleSourceService` reads aliases and IDs from the configured source
- the roles page becomes read-only
- external roles are mirrored into `tinyauth_roles` so ACL/resource permission rows can still use foreign keys safely
- the mirror is authoritative: rows not present in the current external role set are pruned on sync
- when a pruned role is still referenced by ACL/resource permission rows, those dependent rows are removed by the database foreign-key cascades too

### Good Fit

Choose this mode if:

- your app already owns role definitions elsewhere
- you still want to manage ACL/resource assignments in TinyAuthBackend
- you do not want admins changing role identity data from this plugin
